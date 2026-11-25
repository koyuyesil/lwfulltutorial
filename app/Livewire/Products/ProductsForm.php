<?php

namespace App\Livewire\Products;

use App\Livewire\Forms\ProductForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductsForm extends Component
{
    public ProductForm $form;

    #[On('create-product')]
    public function newProduct(): void
    {
        $this->form->reset();
        $this->resetValidation();
    }

    #[On('edit-product')]
    public function edit($id): void
    {
        $this->resetValidation();
        $this->form->setContent(Auth::user()->products()->findOrFail($id));
    }

    /** Daha önce girilen marka ve üreticiler (otomatik tamamlama). */
    #[Computed]
    public function suggestions(): array
    {
        $products = Auth::user()->products();

        return [
            'brands' => (clone $products)->distinct()->orderBy('brand')->pluck('brand')->filter()->values()->all(),
            'manufacturers' => (clone $products)->distinct()->orderBy('manufacturer')->pluck('manufacturer')->filter()->values()->all(),
        ];
    }

    /** Aynı marka + model zaten kayıtlıysa uyarı. */
    #[Computed]
    public function duplicate()
    {
        if (trim($this->form->brand) === '' || trim($this->form->model_name) === '') {
            return null;
        }

        return Auth::user()->products()
            ->whereRaw('LOWER(brand) = ?', [mb_strtolower(trim($this->form->brand))])
            ->whereRaw('LOWER(model_name) = ?', [mb_strtolower(trim($this->form->model_name))])
            ->when($this->form->product, fn ($q, $p) => $q->whereKeyNot($p->id))
            ->first();
    }

    public function save(): void
    {
        $editing = $this->form->isEditing();
        $product = $this->form->store();

        $this->dispatch('product-saved', id: $product->id);
        $this->dispatch('close-drawer', name: 'product-form');
        $this->dispatch('toast', message: $product->display_name.($editing ? ' güncellendi.' : ' eklendi.'));
    }

    public function render()
    {
        return view('livewire.products.products-form');
    }
}
