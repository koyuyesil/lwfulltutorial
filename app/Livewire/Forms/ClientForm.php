<?php

namespace App\Livewire\Forms;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class ClientForm extends Form
{
    public ?Client $client = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $phone = '';

    public string $email = '';

    public string $company = '';

    public string $address = '';

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:60'],
            'phone' => ['required', 'string', 'max:25', 'regex:/^\+?[0-9()\s-]{10,}$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Ad zorunludur.',
            'first_name.min' => 'Ad en az 2 karakter olmalıdır.',
            'last_name.required' => 'Soyad zorunludur.',
            'last_name.min' => 'Soyad en az 2 karakter olmalıdır.',
            'phone.required' => 'Telefon zorunludur; arıza takibi bu numarayla doğrulanır.',
            'phone.regex' => 'Geçerli bir telefon numarası girin (en az 10 hane).',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
        ];
    }

    public function isEditing(): bool
    {
        return $this->client !== null;
    }

    public function set(Client $client): void
    {
        $this->client = $client;
        $this->first_name = (string) $client->first_name;
        $this->last_name = (string) $client->last_name;
        $this->phone = (string) $client->phone;
        $this->email = (string) $client->email;
        $this->company = (string) $client->company;
        $this->address = (string) $client->address;
    }

    public function save(): Client
    {
        $data = $this->validate();

        if ($this->client) {
            $this->client->update($data);
            $client = $this->client;
        } else {
            $client = Auth::user()->clients()->create($data);
        }

        $this->reset();

        return $client;
    }
}
