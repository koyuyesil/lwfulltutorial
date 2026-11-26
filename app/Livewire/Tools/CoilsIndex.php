<?php

namespace App\Livewire\Tools;

use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Bobin hesaplayıcıları (ayrı sayfa, sekmeli): tek katmanlı solenoid, çok katmanlı, toroid (AL) ve düz spiral.
 * Hesaplama ve 3D çizim tarayıcıda: resources/js/coils.js
 */
#[Title('Bobin hesaplayıcı')]
class CoilsIndex extends Component
{
    /** anahtar => [başlık, kısa açıklama, simge] */
    public const TABS = [
        'solenoid' => ['Tek katmanlı', 'Hava çekirdekli silindirik bobin', '🌀'],
        'multilayer' => ['Çok katmanlı', 'Makara üzerine katlı sarım', '🧵'],
        'toroid' => ['Toroid / ferrit', 'AL değerinden endüktans', '🍩'],
        'spiral' => ['Düz spiral', 'Kablosuz şarj / NFC bobini', '📶'],
    ];

    public string $initial = 'solenoid';

    public function mount(): void
    {
        $key = (string) request()->query('bobin', 'solenoid');
        $this->initial = array_key_exists($key, self::TABS) ? $key : 'solenoid';
    }

    public function render()
    {
        return view('livewire.tools.coils-index', ['tabs' => self::TABS])->layout('layouts.app');
    }
}
