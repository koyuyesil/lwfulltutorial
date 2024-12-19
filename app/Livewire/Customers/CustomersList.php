<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
class CustomersList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }

    public function render()
    {
        return view('livewire.customers.customers-list', [
            'customers' => Customer::query()->paginate(3), // Tüm tabloyu çeker
        ]);
    }
}
