<?php

use App\Enums\DividerType;
use App\Livewire\Forms\BoardIdForm;
use App\Livewire\Settings\BoardIdImport;
use App\Livewire\Settings\SettingsIndex;
use App\Models\BoardId;
use App\Models\User;
use App\Services\BoardIdCatalog;
use App\Support\Settings;
use App\Support\TurkishTextRepair;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

beforeEach(fn () => Settings::flush());

function boardCsv(string $content, string $name = 'board.csv'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $content);
}

test('the sample csv imports as internal pull-up records and is idempotent', function () {
    $user = User::factory()->create();
    $csv = file_get_contents(BoardIdCatalog::samplePath());
    $total = count(app(BoardIdCatalog::class)->parse($csv)['rows']);

    expect($total)->toBeGreaterThan(50);

    $component = Livewire::actingAs($user)->test(BoardIdImport::class)
        ->set('file', boardCsv($csv))
        ->assertSet('summary.total', $total)
        ->assertSet('summary.invalid', 0);

    $new = $component->get('summary')['new'];
    $component->call('import')->assertDispatched('board-id-saved');

    expect($user->boardIds()->count())->toBe($new)
        ->and($user->boardIds()->where('divider_type', '!=', DividerType::PULLUP->value)->count())->toBe(0)
        ->and($user->products()->where('codename', 'garnet')->exists())->toBeTrue();

    // RSA → R2, ORG → R2 orijinal
    $garnet = $user->boardIds()->whereRelation('product', 'codename', 'garnet')->where('mass_production_hwid', 'CN-MP-17.9.0')->get()
        ->first(fn (BoardId $b) => $b->r2() === 43000.0);
    expect($garnet)->not->toBeNull()
        ->and($garnet->r1())->toBeNull()
        ->and($garnet->r2Original())->toBe(10000.0)
        ->and($garnet->pre_production_hwid)->toBe('GL-P1-17.11.0')
        ->and($garnet->methods())->not->toBeEmpty()
        ->and($garnet->description)->toContain('geçici unlock');

    // İkinci kez: hepsi mevcut
    Livewire::actingAs($user)->test(BoardIdImport::class)
        ->set('file', boardCsv($csv))
        ->assertSet('summary.new', 0);
});

test('export round-trips through import', function () {
    $user = User::factory()->create();
    $product = $user->products()->create(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 13', 'manufacturer' => 'Xiaomi', 'codename' => 'emerald']);
    $user->boardIds()->create([
        'product_id' => $product->id, 'build_name' => 'emerald 256', 'divider_type' => 'pullup', 'chip' => 'pmic', 'vref' => 1.8,
        'resistances' => ['R1' => null, 'R2' => 18000, 'R2_ORG' => 31800], 'mass_production_hwid' => 'glb-MP-8221.19.0',
        'pre_production_hwid' => 'glb-P1.1-8221.11.1', 'repair_methods' => ['resistor'], 'description' => 'sökmeden paralel 43k',
    ]);

    $response = $this->actingAs($user)->get(route('board-ids.export'))->assertOk();
    $csv = $response->streamedContent();
    expect($csv)->toContain('emerald,Xiaomi,"Redmi Note 13","emerald 256",pullup,pmic,1.8,,18000,,31800');

    $other = User::factory()->create();
    Livewire::actingAs($other)->test(BoardIdImport::class)
        ->set('file', boardCsv($csv))
        ->assertSet('summary.new', 1)
        ->call('import');

    $board = $other->boardIds()->with('product')->first();
    expect($board->product->model_name)->toBe('Redmi Note 13')
        ->and($board->r2Original())->toBe(31800.0)
        ->and($board->description)->toBe('sökmeden paralel 43k');

    // Aynı kullanıcıda tekrar yükleme: mükerrer, güncelle seçilirse açıklama yazılır
    Livewire::actingAs($user)->test(BoardIdImport::class)
        ->set('file', boardCsv(str_replace('sökmeden paralel 43k', 'yeni not', $csv)))
        ->assertSet('summary.duplicate', 1)
        ->set('strategy', 'update')
        ->call('import');
    expect($user->boardIds()->first()->description)->toBe('yeni not');
});

test('resistor notation from service tables is parsed', function () {
    expect(BoardIdCatalog::resistor('open'))->toBeNull()
        ->and(BoardIdCatalog::resistor('11,4k'))->toBe(11400.0)
        ->and(BoardIdCatalog::resistor('22-27k'))->toBe(22000.0)
        ->and(BoardIdCatalog::resistor('3.3?k'))->toBe(3300.0)
        ->and(BoardIdCatalog::resistor('?k'))->toBeNull()
        ->and(BoardIdCatalog::resistor('4m'))->toBe(4000000.0);
});

test('broken turkish characters are repaired', function () {
    expect(TurkishTextRepair::fix("s\u{FFFD}kmeden ge\u{FFFD}ici"))->toBe('sökmeden geçici');
});

test('non csv files and unknown layouts are rejected', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(BoardIdImport::class)
        ->set('file', boardCsv('x', 'a.txt'))
        ->assertHasErrors('file');

    Livewire::actingAs($user)->test(BoardIdImport::class)
        ->set('file', boardCsv("foo,bar\n1,2"))
        ->assertSet('token', null)
        ->assertSet('parseErrors', fn ($e) => count($e) === 1);
});

test('editing in the form keeps original values', function () {
    $user = User::factory()->create();
    $product = $user->products()->create(['brand' => 'Xiaomi', 'model_name' => 'X', 'manufacturer' => 'Xiaomi', 'codename' => 'x']);
    $board = $user->boardIds()->create([
        'product_id' => $product->id, 'build_name' => 'xx', 'divider_type' => 'pullup', 'chip' => 'pmic', 'vref' => 1.8,
        'resistances' => ['R1' => null, 'R2' => 10000, 'R2_ORG' => 33000], 'repair_methods' => [],
    ]);

    Livewire::actingAs($user)->test(\App\Livewire\BoardIds\BoardIdsForm::class)
        ->call('edit', $board->id)
        ->set('form.r2', '12k')
        ->call('save')
        ->assertHasNoErrors();

    expect($board->fresh()->r2())->toBe(12000.0)->and($board->fresh()->r2Original())->toBe(33000.0);
});

test('settings has a board id tab and sample download', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings', 'board-id'))->assertOk()->assertSee('Board ID kütüphanesi');
    $this->actingAs($user)->get(route('board-ids.sample'))->assertOk();
});

test('guests cannot export the board id library', function () {
    $this->get(route('board-ids.export'))->assertRedirect();
});
