<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Customers')]
class CustomersIndex extends Component
{
    public function render()
    {
        return view('livewire.customers.customers-index')->layout('layouts.app');
    }
}
