<?php

namespace App\Livewire\Tools;

use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Bobin hesaplayıcıları (ayrı sayfa, sekmeli): solenoid, çok katmanlı, toroid (AL ve µ), düz spiral, hassas tek katman (Q),
 * dikdörtgen tek / çok katman ve folyo, PCB, ferrit çubuk, pot / E / U çekirdek.
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
        'onelayer' => ['Hassas tek katman', 'Adımlı sarım, Q ve öz rezonans', '🎯'],
        'rect' => ['Dikdörtgen tek katman', 'Kare / dikdörtgen gövde', '⬛'],
        'rectmulti' => ['Dikdörtgen çok katman / folyo', 'Makara ya da bakır folyo sarım', '📜'],
        'ferrite' => ['Ferrit toroid (µ)', 'Boyut ve geçirgenlikten, doyma akımı', '⭕'],
        'pcb' => ['PCB bobin', 'Kare, altıgen, sekizgen, dairesel', '🟩'],
        'rod' => ['Ferrit çubuk', 'AM / LW anten bobini', '📻'],
        'core' => ['Pot / E / U çekirdek', 'Hava boşluklu ferrit çekirdek', '🧲'],
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
