<?php

namespace App\Livewire\Tools;

use Livewire\Attributes\Title;
use Livewire\Component;

/** Birim dönüştürücü (ana menüde Hesaplayıcılar'ın altında, ayrı sayfa). Hesaplama tarayıcıda: unitConverter. */
#[Title('Birim dönüştürücü')]
class UnitsIndex extends Component
{
    public function render()
    {
        return view('livewire.tools.units-index')->layout('layouts.app');
    }
}
