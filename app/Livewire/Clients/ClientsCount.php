<?php

namespace App\Livewire\Clients;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class ClientsCount extends Component
{
    #[Reactive]
    public $clientsByCompany;
    public function render()
    {
        return view('livewire.clients.clients-count');
    }
}
