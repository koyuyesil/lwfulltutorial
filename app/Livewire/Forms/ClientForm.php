<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use app\Models\Client;
use Livewire\Form;

class ClientForm extends Form
{

    public $editMode = false;
    public ?Client $client;
    // Client (Müşteri) alanları
    #[Validate('required|min:5')]
    public $first_name; // Müşteri adı
    #[Validate('required|min:5')]
    public $last_name; // Müşteri soyadı
    #[Validate('required|min:5')]
    public $company; // Şirket adı
    #[Validate('required|min:5')]
    public $phone; // Telefon numarası
    #[Validate('required|min:5')]
    public $email; // E-posta adresi
    #[Validate('required|min:5')]
    public $address; // Adres
    public function set(Client $client)
    {
        $this->editMode = true;
        $this->client = $client;
        $this->first_name = $client->first_name;
        $this->last_name = $client->last_name;
        $this->company = $client->company;
        $this->phone = $client->phone;
        $this->email = $client->email;
        $this->address = $client->address;
    }

    public function create()
    {
        if ($this->editMode) {
            $this->client->update($this->all());
            $this->reset();
            session()->flash('message', 'Form successfully updated.');
        } else {
            auth()->user()->clients()->create($this->all());
            session()->flash('message', 'Form successfully created.');
            $this->reset();
        }
    }
}
