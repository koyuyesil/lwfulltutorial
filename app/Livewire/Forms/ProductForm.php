<?php

namespace App\Livewire\Forms;

use App\Models\Product;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Support\Facades\Auth;

class ProductForm extends Form
{
    public $editMode = false;
    public ?Product $product;

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

    public function setContent(Product $product)
    {
        $this->editMode = true;
        $this->product = $product;
        $this->brand = $product->brand;
        $this->model_name = $product->model_name;
        $this->model_number = $product->model_number;
        $this->description = $product->description;
        $this->manufacturer = $product->manufacturer;
    }

    public function store()
    {
        if ($this->editMode) {
            $this->product->update($this->all());
            $this->reset();
            session()->flash('message', 'Form successfully updated.');
        } else {
            Auth::user()->products()->create($this->all());
            session()->flash('message', 'Form successfully created.');
            //request()->session()->flash('succes',__('Task created successfully'));
            $this->reset();
        }
    }
}
