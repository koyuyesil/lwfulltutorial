<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\ProductForm;

class ProductsForm extends Component
{
    public ProductForm $form;
    public function save()
    {
        $this->form->validate();
        $this->form->store();
        //$this->redirect('tasks',navigate: false); //false wiewde navlar ayarlı ise istekler azaltılır
        $this->dispatch('product-created');// bunun yerine parent save yada redirect yapılabilir
    }

    #[On('edit-product')] // bu event sadece formu doldurur
    public function edit($id)
    {
        $product = auth()->user()->products()->findOrFail($id);
        $this->form->setContent( $product);
    }
    public function render()
    {
        return view('livewire.products.products-form');
    }
}
