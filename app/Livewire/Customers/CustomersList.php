<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use Livewire\Attributes\On;

class CustomersList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }

    public function delete(Customer $customer)
    {
        $customer->delete();
    }

    #[On('customer-created')]
    public function render()
    {
        return view('livewire.customers.customers-list', [
            'customers' => Customer::query()->paginate(3), // Tüm tabloyu çeker
        ]);
    }
}
