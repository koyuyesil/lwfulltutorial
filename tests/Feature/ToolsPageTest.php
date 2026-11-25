<?php

use App\Models\User;

test('calculators page renders every tool and deep links to one', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('tools.index', ['arac' => 'led']))
        ->assertOk()
        ->assertSee('toolsApp(', false)
        ->assertSee("'led'", false);

    foreach (['ohmCalc', 'dividerCalc', 'networkCalc', 'ledCalc', 'colorCalc', 'smdCalc', 'capCodeCalc', 'rcCalc',
        'reactanceCalc', 'timer555Calc', 'dbCalc', 'batteryCalc', 'powerCalc', 'traceCalc', 'awgCalc', 'unitConverter'] as $component) {
        $response->assertSee('x-data="'.$component.'"', false);
    }
});

test('related tools are grouped and listed one under another', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('tools.index'))
        ->assertOk()
        ->assertSeeInOrder(['Temel devre', 'id="arac-ohm"', 'id="arac-network"', 'id="arac-divider"', 'id="arac-led"',
            'Bileşen tanıma', 'id="arac-colors"', 'Zaman ve frekans', 'id="arac-rc"', 'id="arac-timer555"',
            'Güç ve batarya', 'PCB ve kablo', 'id="arac-trace"', 'Birim dönüştürücü'], false)
        ->assertSee('scene3d(', false); // 3D sahneler
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
