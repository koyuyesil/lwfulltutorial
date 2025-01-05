<?php

namespace App\Livewire\Devices;

use App\Models\Device;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\DeviceForm;

class DevicesForm extends Component
{
    public DeviceForm $form;
    public function save()
    {
        $this->form->validate();
        $this->form->store();
        //$this->redirect('tasks',navigate: false); //false wiewde navlar ayarlı ise istekler azaltılır
        $this->dispatch('device-created');// bunun yerine parent save yada redirect yapılabilir
    }

    #[On('edit-device')] // bu event sadece formu doldurur
    public function edit($id)
    {
        $device = Device::findOrFail($id);
        $this->form->setContent($device);
    }
    public function render()
    {
        return view('livewire.devices.devices-form');
    }
}
