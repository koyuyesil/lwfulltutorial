<?php

namespace App\Livewire\Products;

use App\Livewire\Forms\ProductForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductsForm extends Component
{
    use WithFileUploads;

    public ProductForm $form;

    /** Yüklenen görsel; base64 data URI'ye çevrilip forma yazılır. */
    public $photo;

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

    public function updatedPhoto(\App\Services\DeviceCatalog $catalog): void
    {
        $this->validate(['photo' => ['image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048']], [
            'photo.image' => 'Lütfen bir görsel seçin.',
            'photo.max' => 'Görsel en fazla 2 MB olabilir.',
        ]);

        [$dataUri, $error] = $catalog->normalizeImage(base64_encode(file_get_contents($this->photo->getRealPath())));
        $this->photo = null;

        if ($error) {
            $this->addError('photo', $error);

            return;
        }

        $this->form->image = $dataUri;
    }

    public function removeImage(): void
    {
        $this->form->image = null;
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
