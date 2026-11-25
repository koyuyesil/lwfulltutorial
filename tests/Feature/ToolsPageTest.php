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

test('unknown tool keys are sanitized', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('tools.index', ['arac' => '<script>']))
        ->assertOk()
        ->assertDontSee('<script>\'', false);
});
