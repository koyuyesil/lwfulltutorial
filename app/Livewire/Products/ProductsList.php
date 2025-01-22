<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ProductsList extends Component
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

    public function delete(Product $product)
    {
        $product->delete();
    }

    #[On('product-created')]
    public function render()
    {
         //TODO burada PRODUCTS oluşturuken tüm tabloyu erişime açsan iyi olur. ortak database
        return view('livewire.products.products-list', [
            //'devices' => Device::query()->paginate(3), // Tüm tabloyu çeker.
            'products' => Auth::user()->products()->paginate(5), // User ilişkili tabloyu çeker
        ]);
    }
}
