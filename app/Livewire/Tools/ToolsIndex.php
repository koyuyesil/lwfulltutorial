<?php

namespace App\Livewire\Tools;

use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Elektronik hesaplayıcılar ve birim dönüştürücüler.
 * Hesaplamalar tarayıcıda (Alpine, resources/js/calculators.js) anlık yapılır.
 */
#[Title('Hesaplayıcılar')]
class ToolsIndex extends Component
{
    public string $initial = 'ohm';

    public function mount(): void
    {
        $this->initial = preg_replace('/[^a-z0-9]/', '', (string) request()->query('arac', 'ohm')) ?: 'ohm';
    }

    public function render()
    {
        return view('livewire.tools.tools-index')->layout('layouts.app');
    }
}
