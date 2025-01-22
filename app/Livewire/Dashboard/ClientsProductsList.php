<?php

namespace App\Livewire\Dashboard;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;

class ClientsProductsList extends Component
{
    use WithPagination;

    public function placeholder()
    {
        return view('skeleton'); //lazzy loading
    }

    public function render()
    {
        // Müşterileri ve her müşterinin cihazlarını alıyoruz.
        //$clientsProducts = Client::with('clientsProducts.product')->paginate(3); // Pagination ekleniyor


        // Müşterileri ve her müşterinin cihazlarını alıyoruz.
        $clientsProducts = Client::with([
            'clientsProducts.product' => function ($query) {
                $query->orderBy('updated_at', 'desc') // Önce updated_at ile sıralama
                      ->orderBy('created_at', 'desc'); // Sonra created_at ile sıralama
            }
        ])
        ->orderBy('updated_at', 'desc') // Client sıralaması
        ->orderBy('created_at', 'desc') // Daha sonra created_at
        ->paginate(3); // Pagination ekleniyor

        return view('livewire.dashboard.clients-products-list', [
            'clientsProducts' => $clientsProducts,  // Eager loading kullanarak ilişkili cihazları alıyoruz
        ]);
    }
}
