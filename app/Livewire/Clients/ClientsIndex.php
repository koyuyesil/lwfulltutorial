<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Clients')]
class ClientsIndex extends Component
{
    public function render()
    {
        return view('livewire.clients.clients-index')->layout('layouts.app');
    }
}
