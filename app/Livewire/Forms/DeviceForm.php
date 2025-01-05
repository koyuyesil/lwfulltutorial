<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Device;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class DeviceForm extends Form
{
    public $editMode = false;
    public ?Device $device;

    #[Validate('required|min:5')]
    public ?string $manufacturer;

    #[Validate('required|min:5')]
    public ?string $brand;

    #[Validate('required')]
    public ?string $model_name;

    #[Validate('required')]
    public ?string $model_number;

    #[Validate('required')]
    public ?string $description;


    public function setContent(Device $device)
    {
        $this->editMode = true;
        $this->device = $device;
        $this->brand = $device->brand;
        $this->model_name = $device->model_name;
        $this->model_number = $device->model_number;
        $this->description = $device->description;
        $this->manufacturer = $device->manufacturer;


    }
    public function store()
    {
        if ($this->editMode) {
            $this->device->update($this->all());
            $this->reset();
            session()->flash('message', 'Form successfully updated.');
        } else {
            Auth::user()->devices()->create($this->all());
            session()->flash('message', 'Form successfully created.');
            //request()->session()->flash('succes',__('Task created successfully'));
            $this->reset();
        }
    }
}
