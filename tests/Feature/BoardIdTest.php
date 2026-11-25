<?php

use App\Enums\ChipType;
use App\Enums\DividerType;
use App\Livewire\BoardIds\BoardIdsDetail;
use App\Livewire\BoardIds\BoardIdsForm;
use App\Livewire\BoardIds\BoardIdsList;
use App\Models\BoardId;
use App\Models\Product;
use App\Models\User;
use App\Support\Si;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('si helper parses service notation and builds smd codes', function () {
    expect(Si::parse('4k7'))->toBe(4700.0)
        ->and(Si::parse('100k'))->toBe(100000.0)
        ->and(Si::parse('22k1'))->toBe(22100.0)
        ->and(Si::parse('2,2M'))->toBe(2200000.0)
        ->and(Si::parse('abc'))->toBeNull();

    expect(Si::smdCode(10000))->toBe('103')
        ->and(Si::smdCode(4700))->toBe('472')
        ->and(Si::smdCode(100000))->toBe('104')
        ->and(Si::smdCode(10200))->toBe('1022')
        ->and(Si::smdCode(4.7))->toBe('4R7')
        ->and(Si::smdCode(0.47))->toBe('R47')
        ->and(Si::smdCode(0))->toBe('000');

    expect(Si::format(4700))->toBe('4,7 kΩ')
        ->and(Si::format(0.9, 'V'))->toBe('900 mV');
});

test('selecting a model fills the build name from its codename without overwriting custom names', function () {
    $user = User::factory()->create();
    $vili = Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => '11T Pro', 'codename' => 'vili']);
    $garnet = Product::factory()->for($user)->create(['brand' => 'Redmi', 'model_name' => 'Note 13 Pro 5G', 'codename' => 'garnet']);

    Livewire::actingAs($user)->test(BoardIdsForm::class)
        ->set('form.product_id', $vili->id)
        ->assertSet('form.build_name', 'vili')
        ->set('form.product_id', $garnet->id)
        ->assertSet('form.build_name', 'garnet') // otomatik ad değişir
        ->set('form.build_name', 'garnet_PVT')
        ->set('form.product_id', $vili->id)
        ->assertSet('form.build_name', 'garnet_PVT') // elle yazılan korunur
        ->assertSee('örn. vili, garnet', false);
});

test('board id is saved with type, chip, vref and parsed resistors', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();

    Livewire::actingAs($user)->test(BoardIdsForm::class)
        ->set('form.product_id', $product->id)
        ->set('form.build_name', 'vili')
        ->call('setType', 'adc')
        ->call('setChip', 'soc')
        ->set('form.r2', '4k7')
        ->set('form.repair_methods', ['flash', 'resistor'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('board-id-saved');

    $board = $user->boardIds()->first();
    expect($board->divider_type)->toBe(DividerType::ADC)
        ->and($board->chip)->toBe(ChipType::SOC)
        ->and($board->r1())->toBe(100000.0)
        ->and($board->r2())->toBe(4700.0)
        ->and(round($board->vout(), 4))->toBe(round(1.8 * 4700 / 104700, 4));
});

test('choosing a type fills sensible resistor defaults and chip works for every type', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(BoardIdsForm::class)
        ->assertSet('form.r1', '100k')
        ->assertSet('form.r2', '10k')
        ->call('setType', 'logic')
        ->assertSet('form.r2', '')
        ->set('form.r1', '')
        ->call('setType', 'adc')
        ->assertSet('form.r1', '100k')
        ->assertSet('form.r2', '10k')
        ->call('setType', 'pullup')
        ->call('setChip', 'soc')
        ->assertSet('form.chip', 'soc')
        ->call('setType', 'logic')
        ->assertSet('form.chip', 'soc') // entegre tipten bağımsız
        ->assertDontSee('PMIC içinde');

    expect(DividerType::cases())->toHaveCount(3)
        ->and(ChipType::SOC->label())->toBe('CPU/SoC');
});

test('new repair methods are stored and shown', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();

    Livewire::actingAs($user)->test(BoardIdsForm::class)
        ->set('form.product_id', $product->id)
        ->set('form.build_name', 'EDL_TEST')
        ->set('form.repair_methods', ['tmp_bl_unlock', 'tmp_root', 'edl_auth_bypass', 'brom_bypass', 'qc_diag'])
        ->call('save')
        ->assertHasNoErrors();

    expect(array_map(fn ($m) => $m->label(), $user->boardIds()->first()->methods()))
        ->toBe(['Geçici BL unlock', 'Geçici root', 'EDL auth bypass', 'BROM bypass', 'QC diag modu']);
});

test('logic type uses a single resistor and reports the level', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();

    Livewire::actingAs($user)->test(BoardIdsForm::class)
        ->set('form.product_id', $product->id)
        ->set('form.build_name', 'LOGIC_A')
        ->call('setType', 'logic')
        ->call('setLogicLevel', 0)
        ->assertSet('form.r1', '')
        ->assertSet('form.r2', '10k')
        ->call('save')
        ->assertHasNoErrors();

    $board = $user->boardIds()->first();
    expect($board->r1())->toBeNull()
        ->and($board->vout())->toBe(0.0)
        ->and($board->logicLevel())->toBe(0);
});

test('internal pull-up keeps r1 as the ic internal value', function () {
    $board = BoardId::factory()->type(DividerType::PULLUP, ChipType::SOC)->create(['resistances' => ['R1' => 51000, 'R2' => 51000], 'vref' => 1.8]);

    expect($board->divider_type->internalR1())->toBeTrue()
        ->and($board->chip)->toBe(ChipType::SOC)
        ->and($board->vout())->toBe(0.9);
});

test('legacy pull-up types are migrated to type + chip', function () {
    $migration = require database_path('migrations/2026_11_26_090000_split_chip_from_divider_type.php');
    $board = BoardId::factory()->create();

    $migration->down();
    DB::table('board_ids')->where('id', $board->id)->update(['divider_type' => 'pullup_cpu']);
    $migration->up();

    $fresh = $board->fresh();
    expect($fresh->divider_type)->toBe(DividerType::PULLUP)
        ->and($fresh->chip)->toBe(ChipType::SOC);
});

test('invalid resistor text is rejected and other users products cannot be used', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(BoardIdsForm::class)
        ->set('form.product_id', Product::factory()->create()->id)
        ->set('form.build_name', 'X1')
        ->set('form.r2', 'onbin')
        ->call('save')
        ->assertHasErrors(['form.product_id', 'form.r2']);

    Livewire::actingAs($user)->test(BoardIdsForm::class)->call('edit', BoardId::factory()->create()->id)->assertNotFound();
});

test('list filters by type and search, detail shows the pcb view', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['brand' => 'Xiaomi', 'model_name' => 'Redmi Note 11', 'codename' => 'spes']);
    BoardId::factory()->for($user)->for($product)->create(['build_name' => 'spes']);
    $pmic = BoardId::factory()->for($user)->for($product)->type(DividerType::PULLUP, ChipType::PMIC)->create(['build_name' => 'spes_pvt']);
    $soc = BoardId::factory()->for($user)->for($product)->type(DividerType::PULLUP, ChipType::SOC)->create(['build_name' => 'spes_soc']);

    Livewire::actingAs($user)->test(BoardIdsList::class)
        ->set('type', 'pullup')
        ->assertViewHas('boards', fn ($b) => $b->pluck('id')->sort()->values()->all() === [$pmic->id, $soc->id])
        ->assertViewHas('typeCounts', fn ($c) => $c['pullup'] === 2 && $c['adc'] === 1)
        ->set('type', '')
        ->set('search', 'spes')
        ->assertViewHas('boards', fn ($b) => $b->count() === 3);

    Livewire::actingAs($user)->test(BoardIdsDetail::class)
        ->call('load', $soc->id)
        ->assertSee('spes_soc')
        ->assertSee('boardPcb(', false)
        ->assertSee('&quot;chip&quot;:&quot;soc&quot;', false)
        ->assertSee('Dahili pull-up');

    $this->actingAs($user)->get(route('board-ids.index'))->assertOk()->assertSee('Board ID kütüphanesi');
});
