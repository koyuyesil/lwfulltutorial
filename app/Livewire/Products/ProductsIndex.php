<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Products')]
class ProductsIndex extends Component
{
    public function render()
    {
        return view('livewire.products.products-index')->layout('layouts.app');
    }
}
