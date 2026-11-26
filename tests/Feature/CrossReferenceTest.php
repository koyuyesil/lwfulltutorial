<?php

use App\Enums\CompatibilityKind;
use App\Enums\TicketStatus;
use App\Livewire\CrossReference\ComponentEditor;
use App\Livewire\CrossReference\CrossReferenceIndex;
use App\Livewire\CrossReference\DeviceComponents;
use App\Livewire\Tickets\TicketQuote;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use App\Services\CrossReferenceCatalog;
use App\Services\DeviceCatalog;
use App\Support\SampleCrossReference;
use App\Support\Settings;
use Livewire\Livewire;

beforeEach(fn () => Settings::flush());

test('a component can be on many devices and compatible with other components in both directions', function () {
    $user = User::factory()->create();
    $note = Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 13 Pro', 'codename' => 'garnet']);
    $poco = Product::factory()->for($user)->create(['brand' => 'POCO', 'model_name' => 'X6', 'codename' => 'garnetp']);

    $editor = Livewire::actingAs($user)->test(ComponentEditor::class)
        ->call('create', 'ram')
        ->set('code', 'K3LK7K70BM-BGCP')->set('name', '8 GB LPDDR5')->set('manufacturer', 'Samsung')
        ->set('specs', [['Bellek tipi', 'LPDDR5'], ['Kapasite', '8 GB'], ['Paket', '']])
        ->call('save')->assertHasNoErrors();

    $ram = $user->components()->where('code', 'K3LK7K70BM-BGCP')->first();
    expect($ram->specs)->toBe(['Bellek tipi' => 'LPDDR5', 'Kapasite' => '8 GB']);

    $editor->set('deviceSearch', 'garnet')->set('variant', '8/256')->call('attachDevice', $note->id);
    $other = $user->components()->create(['type' => 'ram', 'code' => 'H58G56AK6BX069', 'name' => '8 GB LPDDR5']);
    $other->products()->attach($poco->id);

    $editor->set('kind', 'pin')->set('compatNote', 'Aynı paket')->call('link', $other->id);

    // İki yönlü: karşı bileşende de görünür
    expect($ram->fresh()->compatibles()->pluck('code')->all())->toBe(['H58G56AK6BX069'])
        ->and($other->fresh()->compatibles()->pluck('code')->all())->toBe(['K3LK7K70BM-BGCP'])
        ->and($other->fresh()->compatibles()->first()->pivot->kind)->toBe(CompatibilityKind::PIN->value)
        ->and($ram->products()->first()->pivot->variant)->toBe('8/256')
        // Uyumlu bileşen sayesinde ulaşılabilen cihazlar
        ->and($ram->fresh()->reachableProductIds()->sort()->values()->all())->toBe(collect([$note->id, $poco->id])->sort()->values()->all());

    // Aynı türde aynı kod tekrar oluşturulamaz
    Livewire::actingAs($user)->test(ComponentEditor::class)->call('create', 'ram')->set('code', 'K3LK7K70BM-BGCP')->call('save')->assertHasErrors('code');

    $editor->call('unlink', $other->id);
    expect($ram->fresh()->compatibles())->toHaveCount(0);
});

test('device drawer attaches existing components and other users cannot touch them', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();
    $soc = $user->components()->create(['type' => 'soc', 'code' => 'SM8550', 'name' => 'Snapdragon 8 Gen 2']);

    Livewire::actingAs($user)->test(DeviceComponents::class)->call('show', $product->id)
        ->set('search', 'SM85')->assertSee('SM8550')->call('attach', $soc->id)->assertSee('Snapdragon 8 Gen 2');
    expect($product->components()->pluck('code')->all())->toBe(['SM8550']);

    $intruder = User::factory()->create();
    Livewire::actingAs($intruder)->test(ComponentEditor::class)->call('edit', $soc->id)->assertNotFound();
    Livewire::actingAs($intruder)->test(DeviceComponents::class)->call('show', $product->id)->assertNotFound();
});

test('the sample cross reference imports socs linked to catalog devices and round trips through csv', function () {
    $user = User::factory()->create();
    Product::factory()->for($user)->create(['brand' => 'Samsung', 'model_name' => 'Galaxy S24 Ultra']);

    Livewire::actingAs($user)->test(\App\Livewire\Settings\CrossReferenceImport::class)->call('loadSample')->assertSet('report.components_created', count(SampleCrossReference::SOCS));

    $soc = $user->components()->where('code', 'SM8650')->first();
    expect($soc->products()->pluck('model_name')->all())->toBe(['Galaxy S24 Ultra'])
        ->and($user->components()->where('code', 'SM7325')->first()->compatibles()->pluck('code')->all())->toBe(['SM7325-AE']);

    // Dışa aktar → başka kullanıcıya içe aktar (cihazları oluşturarak)
    $csv = $this->actingAs($user)->get(route('cross-reference.export'))->assertOk()->streamedContent();
    expect($csv)->toContain('SM8650')->toContain('Samsung > Galaxy S24 Ultra');

    $other = User::factory()->create();
    $catalog = app(CrossReferenceCatalog::class);
    $result = $catalog->import($other, $catalog->parse($csv)['rows'], createDevices: true);
    expect($result['components_created'])->toBe(count(SampleCrossReference::SOCS))
        ->and($result['products_created'])->toBe(1)
        ->and($other->components()->where('code', 'SM7325-AE')->first()->compatibles()->pluck('code')->all())->toBe(['SM7325']);

    $this->actingAs($user)->get(route('cross-reference.index', ['q' => 'SM8650']))->assertOk()->assertSee('SM8650')->assertSee('Galaxy S24 Ultra');
    $this->actingAs($user)->get(route('cross-reference.index', ['gorunum' => 'devices']))->assertOk()->assertSee('Galaxy S24 Ultra');
    $this->actingAs($user)->get(route('cross-reference.sample'))->assertOk();
});

test('parts linked to a compatible component are suggested on the ticket quote', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 13 Pro', 'codename' => 'garnet']);
    $onDevice = $user->components()->create(['type' => 'ram', 'code' => 'RAM-A']);
    $compatible = $user->components()->create(['type' => 'ram', 'code' => 'RAM-B']);
    $onDevice->products()->attach($product->id);
    $onDevice->linkCompatible($compatible, CompatibilityKind::DIRECT);
    $user->parts()->create(['name' => 'Yedek bellek', 'component_id' => $compatible->id, 'stock' => 3, 'price' => 500]);

    $client = Client::factory()->for($user)->create();
    $cp = ClientProduct::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'product_id' => $product->id]);
    $ticket = Ticket::factory()->for($user)->status(TicketStatus::DIAGNOSING)->create(['client_product_id' => $cp->id]);

    Livewire::actingAs($user)->test(TicketQuote::class, ['ticketId' => $ticket->id])->assertSee('Yedek bellek');
});

test('csv import reports invalid rows and unmatched devices', function () {
    $user = User::factory()->create();
    $catalog = app(CrossReferenceCatalog::class);
    $csv = "type,code,name,devices,compatible\nsoc,SM8550,Snapdragon 8 Gen 2,\"Yok > Cihaz; bilinmeyen\",\nxyz,,bad,,\nram,R1,,,ram:YOK:pin\n";
    $result = $catalog->import($user, $catalog->parse($csv)['rows']);

    expect($result['components_created'])->toBe(2)
        ->and($result['invalid'])->toHaveCount(1)
        ->and($result['unmatched'])->toContain('Yok > Cihaz')->toContain('bilinmeyen')->toContain('ram:YOK (uyumlu bileşen bulunamadı)');
});

test('selected rows export as a fixed width text table and as an excel workbook', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['brand' => 'Samsung', 'model_name' => 'Galaxy S23 Ultra', 'codename' => 'dm3q']);
    $a = $user->components()->create(['type' => 'soc', 'code' => 'SM8550', 'name' => 'Snapdragon 8 Gen 2', 'manufacturer' => 'Qualcomm']);
    $b = $user->components()->create(['type' => 'ram', 'code' => 'K3LK7K70BM-BGCP', 'name' => '8 GB LPDDR5']);
    $c = $user->components()->create(['type' => 'ram', 'code' => 'NOT-SELECTED']);
    $a->products()->attach($product->id);
    $b->products()->attach($product->id, ['variant' => '8/256']);
    $b->linkCompatible($c, CompatibilityKind::PIN);

    Livewire::actingAs($user)->test(CrossReferenceIndex::class)
        ->call('togglePage', [$a->id, $b->id])->assertSet('selected', [$a->id, $b->id])
        ->assertSee('2 bileşen seçildi')
        ->assertSee(route('cross-reference.selection', ['format' => 'xlsx', 'view' => 'components', 'ids' => $a->id.','.$b->id]))
        ->call('togglePage', [$a->id, $b->id])->assertSet('selected', []);

    $txt = $this->actingAs($user)->get(route('cross-reference.selection', ['format' => 'txt', 'view' => 'components', 'ids' => "$a->id,$b->id"]))
        ->assertOk()->assertHeader('content-type', 'text/plain; charset=UTF-8')->getContent();
    $lines = explode("\r\n", trim(ltrim($txt, "\u{FEFF}")));
    expect($lines)->toHaveCount(4) // başlık, çizgi, 2 satır
        ->and($lines[0])->toMatch('/^Tür +\tKod +\tAd/u')
        ->and($lines[1])->toMatch('/^-+\t-+/')
        ->and($txt)->toContain('SM8550')->toContain('Galaxy S23 Ultra (8/256)')->toContain('NOT-SELECTED [Pin uyumlu]')
        // sabit aralık: ikinci sütun her satırda aynı konumda başlar
        ->and(mb_strpos($lines[2], "\t"))->toBe(mb_strpos($lines[0], "\t"));

    $xlsx = $this->actingAs($user)->get(route('cross-reference.selection', ['format' => 'xlsx', 'view' => 'devices', 'ids' => (string) $product->id]))
        ->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')->getContent();
    $tmp = tempnam(sys_get_temp_dir(), 'x');
    file_put_contents($tmp, $xlsx);
    $zip = new ZipArchive;
    expect($zip->open($tmp))->toBeTrue();
    $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    @unlink($tmp);
    expect($sheet)->toContain('Galaxy S23 Ultra')->toContain('SM8550')->toContain('K3LK7K70BM-BGCP')->toContain('autoFilter');

    // Başka kullanıcının kayıtları dışa aktarılamaz
    $this->actingAs(User::factory()->create())->get(route('cross-reference.selection', ['format' => 'txt', 'view' => 'components', 'ids' => (string) $a->id]))->assertNotFound();
    $this->actingAs($user)->get(route('cross-reference.selection', ['format' => 'txt', 'view' => 'components']))->assertRedirect();
});

test('settings sample buttons open an import preview instead of downloading', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(\App\Livewire\Settings\ProductImport::class)
        ->call('loadSample')->assertSet('fileName', 'Örnek cihaz kataloğu')->assertSet('summary.new', count(\App\Support\SampleDeviceCatalog::rows()));
    expect($user->products()->count())->toBe(0); // onaydan önce kayıt yok

    Livewire::actingAs($user)->test(\App\Livewire\Settings\BoardIdImport::class)
        ->call('loadSample')->assertSet('fileName', 'Örnek Board ID verisi')->assertSet('summary.invalid', 0);

    $this->actingAs($user)->get(route('settings', 'capraz-referans'))->assertOk()
        ->assertSee('Örnek veriyi yükle')->assertSee('CSV içe aktar')->assertDontSee('Örnek CSV indir');
    $this->actingAs($user)->get(route('settings', 'cihaz-modelleri'))->assertOk()->assertSee('Örnek veriyi yükle')->assertDontSee('Örnek CSV indir');
});