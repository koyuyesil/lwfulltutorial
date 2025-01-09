<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;

class ClientsProductsList extends Component
{
    use WithPagination;

    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }

    public function render()
    {
        // Müşterileri ve her müşterinin cihazlarını alıyoruz.
        $clientsProducts = Customer::with('customerDevices.device')->paginate(3); // Pagination ekleniyor

        return view('livewire.dashboard.clients-products-list', [
            'clientsProducts' => $clientsProducts,  // Eager loading kullanarak ilişkili cihazları alıyoruz
        ]);
    }
}
