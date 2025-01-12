<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

    #[On('created')]
    public function render()
    {
        // Müşterileri ve her müşterinin cihazlarını alıyoruz.
        $clients = Auth::user()->clients()->with('clientsProducts.product')->paginate(3); // Pagination ekleniyor
        return view('livewire.clients.clients-list', [
            'clients' => $clients,  // Eager loading kullanarak ilişkili cihazları alıyoruz.
            'clientsByCompany' => Auth::user()->clients()->select('company', DB::raw('COUNT(*) as count'))
                ->groupBy('company')
                ->orderBy('company', 'desc')
                ->get()
        ]);
    }
}
