<?php

namespace App\Livewire\Devices;

use Livewire\Component;
use Livewire\Attributes\Title;

class DevicesIndex extends Component
{
    #[Title('Devices')]
    public function render()
    {
        return view('livewire.devices.devices-index')->layout('layouts.app');
    }
}
