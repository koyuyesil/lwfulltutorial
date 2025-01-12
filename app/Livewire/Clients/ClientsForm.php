<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\ClientForm;

class ClientsForm extends Component
{
    public ClientForm $form;

    public function save()
    {
        $this->form->validate();
        $this->form->create();

        $this->dispatch('created');
    }
    #[On('edit')]//bu event sadece formu doldurur
    public function edit($id)
    {
        $client = Client::findOrFail($id);
        $this->form->set($client);
    }
    public function render()
    {
        return view('livewire.clients.clients-form');
    }
}
