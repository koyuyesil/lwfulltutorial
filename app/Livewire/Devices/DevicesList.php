<?php

namespace App\Livewire\Devices;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class DevicesList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }

    public function changeStatus($id, $status)
    {
        // $task = Device::find($id);
        // $task->update(['status' => $status]);
    }

    public function delete(Device $device)
    {
        $device->delete();
    }

    #[On('device-created')]
    public function render()
    {
        // todo burada formları oluşturuken tüm tabloyu açsan iyi olur
        return view('livewire.devices.devices-list', [
            //'devices' => Device::query()->paginate(3), // Tüm tabloyu çeker.
            'devices' => Auth::user()->devices()->paginate(3), // User ilişkili tabloyu çeker
        ]);
    }
}
