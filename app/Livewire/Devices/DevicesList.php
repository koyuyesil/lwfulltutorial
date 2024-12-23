<?php

namespace App\Livewire\Devices;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Device;

class DevicesList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }

    public function delete(Device $device)
    {
        $device->delete();
    }

    #[On('device-created')]
    public function render()
    {
        return view('livewire.devices.devices-list', [
            'devices' => Device::query()->paginate(3), // Tüm tabloyu çeker
        ]);
    }
}
