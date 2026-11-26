<?php

use App\Enums\CompatibilityKind;
use App\Enums\TicketStatus;
use App\Livewire\CrossReference\CrossReferenceIndex;
use App\Livewire\CrossReference\DeviceParts;
use App\Livewire\CrossReference\PartEditor;
use App\Livewire\Parts\PartRelations;
use App\Livewire\Parts\PartsIndex;
use App\Livewire\Settings\CrossReferenceImport;
use App\Livewire\Settings\PartImport;
use App\Livewire\Tickets\TicketQuote;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Part;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use App\Services\CrossReferenceCatalog;
use App\Support\SampleCrossReference;
use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(fn () => Settings::flush());

/** Çapraz referans parçası (stoksuz) */
function refPart(User $user, string $type, string $code, array $extra = []): Part
{
    return $user->parts()->create(['type' => $type, 'code' => $code, 'name' => $extra['name'] ?? $code, 'stock' => 0, 'min_stock' => 0] + $extra);
}

test('cross reference lives in the parts table with only pivot tables', function () {
    expect(Schema::hasTable('components'))->toBeFalse()
        ->and(Schema::hasTable('part_product'))->toBeTrue()
        ->and(Schema::hasTable('part_compatibilities'))->toBeTrue()
        ->and(Schema::hasColumns('parts', ['type', 'code', 'manufacturer', 'specs', 'notes']))->toBeTrue();
});

test('a part can be on many devices and compatible with other parts in both directions', function () {
    $user = User::factory()->create();
    $note = Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 13 Pro', 'codename' => 'garnet']);
    $poco = Product::factory()->for($user)->create(['brand' => 'POCO', 'model_name' => 'X6', 'codename' => 'garnetp']);

    Livewire::actingAs($user)->test(PartEditor::class)
        ->call('create', 'ram')
        ->set('code', 'K3LK7K70BM-BGCP')->set('name', '8 GB LPDDR5')->set('manufacturer', 'Samsung')
        ->set('specs', [['Bellek tipi', 'LPDDR5'], ['Kapasite', '8 GB'], ['Paket', '']])
        ->call('save')->assertHasNoErrors();

    $ram = $user->parts()->where('code', 'K3LK7K70BM-BGCP')->first();
    expect($ram->specs)->toBe(['Bellek tipi' => 'LPDDR5', 'Kapasite' => '8 GB'])
        ->and($ram->stock)->toBe(0)->and($ram->isLow())->toBeFalse()
        ->and($ram->category)->toBe('Anakart / entegre');

    $other = refPart($user, 'ram', 'H58G56AK6BX069', ['name' => '8 GB LPDDR5']);
    $other->products()->attach($poco->id);

    Livewire::actingAs($user)->test(PartRelations::class, ['partId' => $ram->id])
        ->set('deviceSearch', 'garnet')->set('variant', '8/256')->call('attachDevice', $note->id)
        ->set('kind', 'pin')->set('compatNote', 'Aynı paket')->call('link', $other->id)
        ->assertSee('Bulunduğu cihazlar')->assertSee('Redmi Note 13 Pro')
        ->assertSee('Muadil parça')->assertSee('H58G56AK6BX069')->assertSee('Pin uyumlu');

    expect($ram->fresh()->compatibles()->pluck('code')->all())->toBe(['H58G56AK6BX069'])
        ->and($other->fresh()->compatibles()->pluck('code')->all())->toBe(['K3LK7K70BM-BGCP'])
        ->and($other->fresh()->compatibles()->first()->pivot->kind)->toBe(CompatibilityKind::PIN->value)
        ->and($ram->products()->first()->pivot->variant)->toBe('8/256')
        ->and($ram->fresh()->reachableProductIds()->sort()->values()->all())->toBe(collect([$note->id, $poco->id])->sort()->values()->all());

    // Aynı türde aynı kod tekrar oluşturulamaz
    Livewire::actingAs($user)->test(PartEditor::class)->call('create', 'ram')->set('code', 'K3LK7K70BM-BGCP')->call('save')->assertHasErrors('code');

    Livewire::actingAs($user)->test(PartRelations::class, ['partId' => $ram->id])->call('unlink', $other->id);
    expect($ram->fresh()->compatibles())->toHaveCount(0);

    // Başka kullanıcı erişemez
    Livewire::actingAs(User::factory()->create())->test(PartRelations::class, ['partId' => $ram->id])->assertNotFound();
});

test('device drawer lists the phone parts and the quick buttons open an inline form', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['model_name' => 'Redmi Note 13 Pro']);
    $soc = refPart($user, 'soc', 'SM8550', ['name' => 'Snapdragon 8 Gen 2']);

    $drawer = Livewire::actingAs($user)->test(DeviceParts::class)->call('show', $product->id)
        ->assertDispatched('open-drawer', name: 'device-parts')
        ->set('search', 'SM85')->assertSee('SM8550')->call('attach', $soc->id)
        ->assertSee('Telefonun parçaları')->assertSee('Snapdragon 8 Gen 2');
    expect($product->parts()->pluck('code')->all())->toBe(['SM8550']);

    // Hızlı düğme: başka çekmece açmaz, aynı panelde form açılır
    $drawer->call('openNew', 'pmic')->assertSet('newType', 'pmic')->assertNotDispatched('open-drawer')
        ->set('newCode', 'PM8550')->set('newName', 'Güç yönetimi')->call('createPart')->assertHasNoErrors()
        ->assertSet('newType', '');
    expect($product->parts()->orderBy('code')->pluck('code')->all())->toBe(['PM8550', 'SM8550']);

    // Var olan kodla yenisi açılmaz, mevcut parça bağlanır
    $other = Product::factory()->for($user)->create();
    Livewire::actingAs($user)->test(DeviceParts::class)->call('show', $other->id)
        ->call('openNew', 'soc')->set('newCode', 'sm8550')->call('createPart');
    expect($user->parts()->where('code', 'SM8550')->count())->toBe(1)
        ->and($soc->products()->count())->toBe(2);

    // Aynı türe tekrar basmak formu kapatır
    Livewire::actingAs($user)->test(DeviceParts::class)->call('show', $product->id)->call('openNew', 'ram')->call('openNew', 'ram')->assertSet('newType', '');

    Livewire::actingAs(User::factory()->create())->test(DeviceParts::class)->call('show', $product->id)->assertNotFound();
});

test('cross reference defaults to the device view', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['brand' => 'Samsung', 'model_name' => 'Galaxy S24 Ultra']);
    refPart($user, 'soc', 'SM8650')->products()->attach($product->id);

    Livewire::actingAs($user)->test(CrossReferenceIndex::class)->assertSet('view', 'devices')->assertSee('Galaxy S24 Ultra')->assertSee('SM8650');
    $this->actingAs($user)->get(route('cross-reference.index', ['gorunum' => 'parts', 'q' => 'SM8650']))->assertOk()->assertSee('SM8650')->assertSee('Galaxy S24 Ultra');
});

test('the json sample opens a preview first and imports devices and parts without duplicates', function () {
    $user = User::factory()->create();
    Product::factory()->for($user)->create(['brand' => 'Samsung', 'model_name' => 'Galaxy S24 Ultra']);

    $import = Livewire::actingAs($user)->test(CrossReferenceImport::class)->call('loadSample')
        ->assertSet('summary.parts_new', count(SampleCrossReference::SOCS))
        ->assertSee('Galaxy S24 Ultra')
        ->set('previewTab', 'devices')->assertSee('Katalogda var');
    expect($user->parts()->count())->toBe(0); // onaydan önce kayıt yok

    $import->call('import')->assertSet('token', null);
    $sampleDevices = count(SampleCrossReference::data()['devices']);
    expect($user->parts()->count())->toBe(count(SampleCrossReference::SOCS))
        ->and($user->products()->count())->toBe($sampleDevices) // mevcut S24 Ultra yeniden eklenmedi
        ->and($user->parts()->where('code', 'SM8650')->first()->products()->pluck('model_name')->all())->toContain('Galaxy S24 Ultra')
        ->and($user->parts()->where('code', 'SM7325')->first()->compatibles()->pluck('code')->all())->toBe(['SM7325-AE']);

    // Aynı dosya ikinci kez: her şey mevcut
    Livewire::actingAs($user)->test(CrossReferenceImport::class)->call('loadSample')
        ->assertSet('summary.parts_new', 0)->assertSet('summary.devices_new', 0)->call('import');
    expect($user->parts()->count())->toBe(count(SampleCrossReference::SOCS))->and($user->products()->count())->toBe($sampleDevices);
});

test('json export round trips to another account creating missing devices', function () {
    $user = User::factory()->create();
    $device = Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 13 Pro', 'codename' => 'garnet', 'model_number' => '2312DRA50G', 'release_year' => 2024]);
    $soc = refPart($user, 'soc', 'SM7435', ['name' => 'Snapdragon 7s Gen 2', 'manufacturer' => 'Qualcomm', 'specs' => ['Üretim süreci' => '4 nm']]);
    $alt = refPart($user, 'soc', 'SM7435-AB');
    $stockOnly = $user->parts()->create(['name' => 'Yedek ekran', 'stock' => 3, 'min_stock' => 1]); // bağımsız parça
    $soc->products()->attach($device->id, ['variant' => '8/256']);
    $soc->linkCompatible($alt, CompatibilityKind::PIN, 'Aynı paket');

    $json = $this->actingAs($user)->get(route('cross-reference.export'))->assertOk()->assertHeader('content-type', 'application/json')->getContent();
    $data = json_decode($json, true);
    expect($data['format'])->toBe(CrossReferenceCatalog::FORMAT)
        ->and($data['devices'][0])->toMatchArray(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 13 Pro', 'codename' => 'garnet', 'model_number' => '2312DRA50G', 'release_year' => 2024])
        ->and(collect($data['parts'])->pluck('code')->all())->toContain('SM7435', 'SM7435-AB')->not->toContain(null)
        ->and($json)->not->toContain('Yedek ekran');

    $other = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('capraz.json', $json);
    Livewire::actingAs($other)->test(CrossReferenceImport::class)->set('file', $file)
        ->assertSet('summary.devices_new', 1)->assertSet('summary.parts_new', 2)->assertSee('Redmi Note 13 Pro')
        ->call('import');

    $imported = $other->parts()->where('code', 'SM7435')->first();
    expect($other->products()->first()->codename)->toBe('garnet')
        ->and($imported->specs)->toBe(['Üretim süreci' => '4 nm'])
        ->and($imported->products()->first()->pivot->variant)->toBe('8/256')
        ->and($imported->compatibles()->first()->code)->toBe('SM7435-AB')
        ->and($imported->compatibles()->first()->pivot->note)->toBe('Aynı paket');

    // CSV ya da başka JSON reddedilir
    Livewire::actingAs($other)->test(CrossReferenceImport::class)->set('file', UploadedFile::fake()->createWithContent('x.json', '{"a":1}'))->assertHasErrors('file');
    Livewire::actingAs($other)->test(CrossReferenceImport::class)->set('file', UploadedFile::fake()->createWithContent('x.csv', "type,code\nsoc,X"))->assertHasErrors('file');
});

test('parts linked to a compatible part are suggested on the ticket quote', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 13 Pro', 'codename' => 'garnet']);
    $onDevice = refPart($user, 'ram', 'RAM-A');
    $compatible = $user->parts()->create(['type' => 'ram', 'code' => 'RAM-B', 'name' => 'Yedek bellek', 'stock' => 3, 'price' => 500]);
    $onDevice->products()->attach($product->id);
    $onDevice->linkCompatible($compatible, CompatibilityKind::DIRECT);

    $client = Client::factory()->for($user)->create();
    $cp = ClientProduct::factory()->create(['user_id' => $user->id, 'client_id' => $client->id, 'product_id' => $product->id]);
    $ticket = Ticket::factory()->for($user)->status(TicketStatus::DIAGNOSING)->create(['client_product_id' => $cp->id]);

    Livewire::actingAs($user)->test(TicketQuote::class, ['ticketId' => $ticket->id])->assertSee('Yedek bellek');
});

test('parts page separates devices from compatible parts and edits cross reference fields', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['model_name' => 'Galaxy S23']);
    $soc = refPart($user, 'soc', 'SM8550', ['name' => 'Snapdragon 8 Gen 2']);
    $soc->products()->attach($product->id);
    $soc->linkCompatible(refPart($user, 'soc', 'SM8550-AC'));

    Livewire::actingAs($user)->test(PartsIndex::class)
        ->assertSee('1 cihaz')->assertSee('1 muadil')
        ->set('stockFilter', 'ref')->assertSee('Snapdragon 8 Gen 2')
        ->set('stockFilter', 'out')->assertDontSee('Snapdragon 8 Gen 2') // stok takibi olmayan referans kaydı tükenen sayılmaz
        ->call('edit', $soc->id)->assertSet('form.type', 'soc')->assertSet('form.code', 'SM8550')
        ->set('form.type', 'ram')->set('form.code', '')->call('save')->assertHasErrors('form.code');
});

test('parts csv export and import with a preview', function () {
    $user = User::factory()->create();
    $user->parts()->create(['name' => 'Redmi Note 13 Pro ekran', 'sku' => 'EKR-001', 'category' => 'Ekran', 'cost' => 1200.5, 'price' => 2000, 'stock' => 3, 'min_stock' => 1]);

    $csv = $this->actingAs($user)->get(route('parts.export'))->assertOk()->streamedContent();
    expect($csv)->toContain('EKR-001')->toContain('1200.50');

    $upload = "Ad;Stok kodu;Kategori;Satış;Stok;Tür;Kod\nRedmi Note 13 Pro ekran;EKR-001;Ekran;2.100,00;5;;\nBatarya BN5J;BAT-77;Batarya;450,50;2;battery;BN5J\nX;;;;;;\n";
    $import = Livewire::actingAs($user)->test(PartImport::class)->set('file', UploadedFile::fake()->createWithContent('parcalar.csv', $upload))
        ->assertSet('summary.new', 1)->assertSet('summary.duplicate', 1)->assertSet('summary.invalid', 1)
        ->assertSee('Batarya BN5J');
    expect($user->parts()->count())->toBe(1); // onaydan önce kayıt yok

    $import->set('strategy', 'update')->call('import');
    expect($user->parts()->count())->toBe(2)
        ->and((float) $user->parts()->where('sku', 'EKR-001')->value('price'))->toBe(2100.0)
        ->and($user->parts()->where('sku', 'BAT-77')->first()->type?->value)->toBe('battery');

    $this->actingAs($user)->get(route('settings', 'parcalar'))->assertOk()->assertSee('CSV içe aktar');
});

test('selected rows export as text, excel and json', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['brand' => 'Samsung', 'model_name' => 'Galaxy S23 Ultra', 'codename' => 'dm3q']);
    $a = refPart($user, 'soc', 'SM8550', ['name' => 'Snapdragon 8 Gen 2', 'manufacturer' => 'Qualcomm']);
    $b = refPart($user, 'ram', 'K3LK7K70BM-BGCP', ['name' => '8 GB LPDDR5']);
    $c = refPart($user, 'ram', 'NOT-SELECTED');
    $a->products()->attach($product->id);
    $b->products()->attach($product->id, ['variant' => '8/256']);
    $b->linkCompatible($c, CompatibilityKind::PIN);

    Livewire::actingAs($user)->test(CrossReferenceIndex::class)->set('view', 'parts')
        ->call('togglePage', [$a->id, $b->id])->assertSet('selected', [$a->id, $b->id])
        ->assertSee('2 parça seçildi')
        ->assertSee(route('cross-reference.selection', ['format' => 'xlsx', 'view' => 'parts', 'ids' => $a->id.','.$b->id]))
        ->call('togglePage', [$a->id, $b->id])->assertSet('selected', []);

    $txt = $this->actingAs($user)->get(route('cross-reference.selection', ['format' => 'txt', 'view' => 'parts', 'ids' => "$a->id,$b->id"]))
        ->assertOk()->assertHeader('content-type', 'text/plain; charset=UTF-8')->getContent();
    $lines = explode("\r\n", trim(ltrim($txt, "\u{FEFF}")));
    expect($lines)->toHaveCount(4)
        ->and($lines[0])->toMatch('/^Tür +\tKod +\tAd/u')
        ->and($txt)->toContain('SM8550')->toContain('Galaxy S23 Ultra (8/256)')->toContain('NOT-SELECTED [Pin uyumlu]')
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
    expect($sheet)->toContain('Galaxy S23 Ultra')->toContain('SM8550')->toContain('K3LK7K70BM-BGCP');

    $json = json_decode($this->actingAs($user)->get(route('cross-reference.selection', ['format' => 'json', 'view' => 'devices', 'ids' => (string) $product->id]))->assertOk()->getContent(), true);
    expect(collect($json['parts'])->pluck('code')->all())->toContain('SM8550', 'K3LK7K70BM-BGCP', 'NOT-SELECTED'); // muadil hedefi de dosyada

    $this->actingAs(User::factory()->create())->get(route('cross-reference.selection', ['format' => 'txt', 'view' => 'parts', 'ids' => (string) $a->id]))->assertNotFound();
    $this->actingAs($user)->get(route('cross-reference.selection', ['format' => 'txt', 'view' => 'parts']))->assertRedirect();
});

test('settings sample buttons open an import preview instead of downloading', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(\App\Livewire\Settings\ProductImport::class)
        ->call('loadSample')->assertSet('fileName', 'Örnek cihaz kataloğu')->assertSet('summary.new', count(\App\Support\SampleDeviceCatalog::rows()));
    expect($user->products()->count())->toBe(0);

    Livewire::actingAs($user)->test(\App\Livewire\Settings\BoardIdImport::class)
        ->call('loadSample')->assertSet('fileName', 'Örnek Board ID verisi')->assertSet('summary.invalid', 0);

    $this->actingAs($user)->get(route('settings', 'capraz-referans'))->assertOk()
        ->assertSee('Örnek veriyi yükle')->assertSee('JSON içe aktar')->assertDontSee('CSV içe aktar');
    $this->actingAs($user)->get(route('settings', 'cihaz-modelleri'))->assertOk()->assertSee('Örnek veriyi yükle')->assertDontSee('Örnek CSV indir');
});

test('sidebar shows tools with a divider instead of a premium heading', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->getContent();
    expect($html)->toContain('role="separator"')->toContain('Çapraz referans')->not->toMatch('/>\s*Premium\s*</');
});
