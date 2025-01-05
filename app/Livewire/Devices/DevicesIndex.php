<?php

namespace App\Livewire\Devices;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Devices')]
class DevicesIndex extends Component
{
    public function render()
    {
        return view('livewire.devices.devices-index')->layout('layouts.app');
    }
}
