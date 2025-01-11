<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
class ClientsList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }

    public function delete(Client $client)
    {
        $client->delete();
    }

    #[On('client-created')]
    public function render()
    {
        //$clients = Client::query()->paginate(3)
        // Müşterileri ve her müşterinin cihazlarını alıyoruz.
        $clients = Client::with('clientsProducts.product')->paginate(3); // Pagination ekleniyor
        return view('livewire.clients.clients-list', [
            'clients' => $clients,  // Eager loading kullanarak ilişkili cihazları alıyoruz
        ]);
    }
}
