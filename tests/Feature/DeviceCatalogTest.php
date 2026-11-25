<?php

use App\Livewire\Products\ProductsForm;
use App\Livewire\Settings\ProductImport;
use App\Livewire\Settings\SettingsIndex;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use App\Services\DeviceCatalog;
use App\Support\SampleDeviceCatalog;
use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

beforeEach(fn () => Settings::flush());

function csvFile(string $content, string $name = 'katalog.csv'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $content);
}

function sampleCsv(): string
{
    $handle = fopen('php://temp', 'r+');
    DeviceCatalog::writeCsv(SampleDeviceCatalog::csvRows(), $handle);
    rewind($handle);

    return stream_get_contents($handle);
}

function tinyPng(): string
{
    $img = imagecreatetruecolor(800, 400);
    imagefill($img, 0, 0, imagecolorallocate($img, 20, 40, 160));
    ob_start();
    imagepng($img);

    return base64_encode(ob_get_clean());
}

test('sample catalog contains real models from popular brands with images', function () {
    $rows = SampleDeviceCatalog::rows();
    $brands = collect($rows)->pluck('brand')->unique();

    expect(count($rows))->toBeGreaterThan(50)
        ->and($brands)->toContain('Samsung', 'Apple', 'Xiaomi', 'Redmi', 'POCO', 'Google', 'OnePlus')
        ->and(collect($rows)->firstWhere('model_name', 'Galaxy S24 Ultra')['codename'])->toBe('e3q');

    $this->actingAs(User::factory()->create())->get(route('products.sample'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('the sample csv imports cleanly with images and is idempotent', function () {
    $user = User::factory()->create();
    $csv = sampleCsv();

    $component = Livewire::actingAs($user)->test(ProductImport::class)
        ->set('file', csvFile($csv))
        ->assertSet('summary.new', count(SampleDeviceCatalog::rows()))
        ->assertSet('summary.invalid', 0)
        ->call('import')
        ->assertDispatched('product-saved');

    expect($user->products()->count())->toBe(count(SampleDeviceCatalog::rows()))
        ->and($user->products()->whereNotNull('image')->count())->toBe(count(SampleDeviceCatalog::rows()));

    $s24 = $user->products()->where('model_name', 'Galaxy S24 Ultra')->first();
    expect($s24->codename)->toBe('e3q')
        ->and($s24->chipset)->toBe('Snapdragon 8 Gen 3')
        ->and($s24->release_year)->toBe(2024)
        ->and($s24->image)->toStartWith('data:image/svg+xml;base64,');

    // Aynı dosya ikinci kez: hepsi mükerrer, hiçbir şey eklenmez
    Livewire::actingAs($user)->test(ProductImport::class)
        ->set('file', csvFile($csv))
        ->assertSet('summary.new', 0)
        ->assertSet('summary.duplicate', count(SampleDeviceCatalog::rows()))
        ->call('import');

    expect($user->products()->count())->toBe(count(SampleDeviceCatalog::rows()));
});

test('import detects duplicates by brand+model and model number, repeats and invalid rows', function () {
    $user = User::factory()->create();
    $existing = Product::factory()->for($user)->create(['brand' => 'Samsung', 'model_name' => 'Galaxy A54 5G', 'model_number' => 'SM-A546B']);
    Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => 'Mi 11', 'model_number' => 'M2011K2G']);

    $csv = "marka;model;model_no;kod_adi;yil\n"
        ."samsung;galaxy a54 5g;;a54x;2023\n"          // marka+model (büyük/küçük harf duyarsız) → mevcut
        ."Xiaomi;Xiaomi 11;M2011K2G;venus;2021\n"       // model no eşleşmesi → mevcut
        ."Google;Pixel 8;GKWS6;shiba;2023\n"            // yeni
        ."Google;Pixel 8;GKWS6;shiba;2023\n"            // dosya içinde tekrar
        .";Model Yok;;;\n"                              // hatalı: marka yok
        ."Apple;iPhone 15;A3090;D37;1800\n";            // geçersiz yıl: uyarı, yine de yeni

    $component = Livewire::actingAs($user)->test(ProductImport::class)->set('file', csvFile($csv));
    $component->assertSet('summary.duplicate', 2)
        ->assertSet('summary.new', 2)
        ->assertSet('summary.repeat', 1)
        ->assertSet('summary.invalid', 1);

    $component->set('strategy', 'update')->call('import');

    expect($user->products()->count())->toBe(4) // 2 mevcut + Pixel 8 + iPhone 15
        ->and($existing->fresh()->codename)->toBe('a54x')
        ->and($existing->fresh()->model_number)->toBe('SM-A546B') // boş hücre mevcut bilgiyi silmez
        ->and($user->products()->where('model_name', 'iPhone 15')->first()->release_year)->toBeNull();
});

test('import rejects unsafe svg and resizes raster images to webp', function () {
    $catalog = app(DeviceCatalog::class);

    [$uri, $error] = $catalog->normalizeImage('data:image/png;base64,'.tinyPng());
    expect($error)->toBeNull()->and($uri)->toStartWith('data:image/webp;base64,');
    $size = getimagesizefromstring(base64_decode(substr($uri, strpos($uri, ',') + 1)));
    expect(max($size[0], $size[1]))->toBe(DeviceCatalog::IMAGE_MAX_SIZE);

    [, $svgError] = $catalog->normalizeImage(base64_encode('<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>'));
    expect($svgError)->toContain('güvenli değil');

    [, $garbage] = $catalog->normalizeImage('bu-base64-degil!!');
    expect($garbage)->not->toBeNull();
});

test('import requires brand and model columns', function () {
    Livewire::actingAs(User::factory()->create())->test(ProductImport::class)
        ->set('file', csvFile("foo,bar\n1,2\n"))
        ->assertSet('token', null)
        ->assertSee('brand');
});

test('export contains every field and image and round-trips through import', function () {
    $owner = User::factory()->create();
    Livewire::actingAs($owner)->test(ProductImport::class)->set('file', csvFile(sampleCsv()))->call('import');
    Product::factory()->create(['brand' => 'Başka', 'model_name' => 'Firma']); // başka kullanıcı

    $response = $this->actingAs($owner)->get(route('products.export'))->assertOk();
    $csv = $response->streamedContent();

    expect($csv)->toStartWith("\xEF\xBB\xBF".'brand,model_name,model_number,codename')
        ->and($csv)->toContain('Galaxy S24 Ultra')
        ->and($csv)->toContain('data:image/svg+xml;base64,')
        ->and($csv)->not->toContain('Başka');

    $other = User::factory()->create();
    Livewire::actingAs($other)->test(ProductImport::class)->set('file', csvFile($csv))->call('import');
    expect($other->products()->count())->toBe($owner->products()->count())
        ->and($other->products()->whereNotNull('image')->count())->toBe($owner->products()->count());
});

test('product form stores technical fields and uploaded image as base64', function () {
    $user = User::factory()->create();
    $photo = UploadedFile::fake()->createWithContent('foto.png', base64_decode(tinyPng()));

    Livewire::actingAs($user)->test(ProductsForm::class)
        ->set('form.brand', 'Samsung')
        ->set('form.model_name', 'Galaxy S23')
        ->set('form.codename', 'dm1q')
        ->set('form.chipset', 'Snapdragon 8 Gen 2')
        ->set('form.release_year', '2023')
        ->set('photo', $photo)
        ->assertSet('form.image', fn ($v) => str_starts_with((string) $v, 'data:image/webp;base64,'))
        ->call('save')
        ->assertHasNoErrors();

    $product = $user->products()->first();
    expect($product->codename)->toBe('dm1q')
        ->and($product->release_year)->toBe(2023)
        ->and($product->image)->toStartWith('data:image/webp;base64,');
});

test('settings are saved per user and drive sla and product view', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(SettingsIndex::class, ['tab' => 'servis'])
        ->set('sla.high', 4)
        ->call('saveTickets')
        ->assertHasNoErrors();

    Livewire::actingAs($user)->test(SettingsIndex::class, ['tab' => 'genel'])
        ->set('companyName', 'Hızlı Tamir Ltd.')
        ->call('saveGeneral');

    Settings::flush();
    $this->freezeSecond();
    $ticket = Ticket::factory()->for($user)->create(['status' => null, 'priority' => 'high']);
    expect($ticket->due_at->equalTo(now()->addHours(4)))->toBeTrue()
        ->and($user->fresh()->companyName())->toBe('Hızlı Tamir Ltd.');

    // Başka kullanıcının ayarı etkilenmez
    expect(Ticket::slaHoursFor('high', User::factory()->create()))->toBe(24);

    Livewire::actingAs($user)->test(SettingsIndex::class, ['tab' => 'servis'])
        ->set('sla.high', 0)->call('saveTickets')->assertHasErrors('sla.high');

    foreach (SettingsIndex::TABS as $tab) {
        $this->actingAs($user)->get(route('settings', $tab))->assertOk();
    }
});
