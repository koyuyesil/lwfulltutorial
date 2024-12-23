<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Customer;

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
        //$customers = Customer::query()->paginate(3)
        // Müşterileri ve her müşterinin cihazlarını alıyoruz.
        $customers = Customer::with('customerDevices.device')->paginate(3); // Pagination ekleniyor

        return view('livewire.customers.customers-list', [
            'customers' => $customers,  // Eager loading kullanarak ilişkili cihazları alıyoruz
        ]);
    }
}
