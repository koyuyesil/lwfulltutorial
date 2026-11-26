<?php

use App\Models\User;

test('calculators page renders every tool and deep links to one', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('tools.index', ['arac' => 'led']))
        ->assertOk()
        ->assertSee('toolsApp(', false)
        ->assertSee("'led'", false);

    foreach (['ohmCalc', 'dividerCalc', 'networkCalc', 'ledCalc', 'colorCalc', 'smdCalc', 'capCodeCalc', 'rcCalc',
        'reactanceCalc', 'timer555Calc', 'dbCalc', 'batteryCalc', 'powerCalc', 'traceCalc', 'awgCalc'] as $component) {
        $response->assertSee('x-data="'.$component.'"', false);
    }
});

test('related tools are grouped and listed one under another', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('tools.index'))
        ->assertOk()
        ->assertSeeInOrder(['Temel devre', 'id="arac-ohm"', 'id="arac-network"', 'id="arac-divider"', 'id="arac-led"',
            'Bileşen tanıma', 'id="arac-colors"', 'Zaman ve frekans', 'id="arac-rc"', 'id="arac-timer555"',
            'Güç ve batarya', 'PCB ve kablo', 'id="arac-trace"'], false)
        ->assertSee('scene3d(', false) // 3D sahneler
        ->assertDontSee('x-data="unitConverter"', false);
});

test('unknown tool keys fall back to the first tool', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('tools.index', ['arac' => 'yok']))
        ->assertOk()
        ->assertSee("toolsApp('ohm'", false);
});

test('unknown tool keys are sanitized', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('tools.index', ['arac' => '<script>']))
        ->assertOk()
        ->assertDontSee('<script>\'', false);
});

test('unit converter and coil calculators have their own pages listed under calculators in the menu', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('tools.units'))->assertOk()->assertSee('x-data="unitConverter"', false)->assertSee('Birim dönüştürücü');
    // Eski derin bağlantı yeni sayfaya yönlenir
    $this->actingAs($user)->get(route('tools.index', ['arac' => 'convert']))->assertRedirect(route('tools.units'));

    $coils = $this->actingAs($user)->get(route('tools.coils', ['bobin' => 'toroid']))->assertOk()->assertSee("coilsApp('toroid')", false);
    foreach (['solenoidCalc', 'multilayerCalc', 'toroidCalc', 'spiralCalc'] as $component) {
        $coils->assertSee('x-data="'.$component.'"', false);
    }
    $this->actingAs($user)->get(route('tools.coils', ['bobin' => 'yok']))->assertSee("coilsApp('solenoid')", false);

    // Menüde Hesaplayıcılar → Birim dönüştürücü → Bobin hesaplayıcı sırası
    $this->actingAs($user)->get(route('dashboard'))->assertSeeInOrder(['Hesaplayıcılar', 'Birim dönüştürücü', 'Bobin hesaplayıcı', 'role="separator"', 'Board ID'], false);
});

test('calculator inputs have up and down buttons with press and hold', function () {
    $this->actingAs(User::factory()->create())->get(route('tools.index'))->assertOk()
        ->assertSee('aria-label="Arttır"', false)->assertSee('$hold(', false)->assertSee('@keydown.arrow-up.prevent', false);
});