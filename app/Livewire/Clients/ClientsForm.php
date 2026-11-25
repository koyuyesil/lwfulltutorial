<?php

namespace App\Livewire\Clients;

use App\Livewire\Forms\ClientForm;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ClientsForm extends Component
{
    public ClientForm $form;

    /** Aynı telefonla kayıtlı başka müşteri (mükerrer kayıt uyarısı). */
    public ?array $duplicate = null;

    #[On('create-client')]
    public function newClient(): void
    {
        $this->form->reset();
        $this->duplicate = null;
        $this->resetValidation();
    }

    #[On('edit-client')]
    public function edit(int $id): void
    {
        $this->resetValidation();
        $this->duplicate = null;
        $this->form->set(Auth::user()->clients()->findOrFail($id));
    }

    public function updatedFormPhone(): void
    {
        $digits = preg_replace('/\D/', '', $this->form->phone);
        $this->duplicate = null;

        if (strlen($digits) < 10) {
            return;
        }

        $match = Auth::user()->clients()
            ->whereRaw(Client::phoneDigitsSql().' like ?', ['%'.substr($digits, -10)])
            ->when($this->form->client, fn ($q, $c) => $q->whereKeyNot($c->id))
            ->first();

        $this->duplicate = $match ? ['id' => $match->id, 'name' => $match->full_name] : null;
    }

    public function save(): void
    {
        $editing = $this->form->isEditing();
        $client = $this->form->save();
        $this->duplicate = null;

        $this->dispatch('client-saved', id: $client->id);
        $this->dispatch('close-drawer', name: 'client-form');
        $this->dispatch('toast', message: $editing ? "{$client->full_name} güncellendi." : "{$client->full_name} eklendi.");
    }

    public function render()
    {
        return view('livewire.clients.clients-form');
    }
}
