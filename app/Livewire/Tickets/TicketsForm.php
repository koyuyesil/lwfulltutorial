<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Ticket;
use App\Models\Device;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class TicketsForm extends Component
{
    public $customers;
    public $devices = [];
    public $tickets = [];
    public $selectedCustomer;
    public $selectedDevice;
    public $problem;

    public $newCustomerFirstName;
    public $newCustomerLastName;
    public $newCustomerCompany;
    public $newCustomerPhone;
    public $newCustomerEmail;
    public $newCustomerAdress;

    public $newDeviceModel;
    public $newDeviceSerial;

    public function mount()
    {
        $this->loadCustomers();
        //$this->loadTickets();
    }

    public function loadCustomers()
    {
        //$this->customers = Customer::all();
        $this->customers = Auth::user()->customers;

    }

    public function loadDevices()
    {
        if ($this->selectedCustomer) {
            $this->devices = CustomerDevice::where('customer_id', $this->selectedCustomer)
                ->with('device')
                ->get()
                ->pluck('device');
        } else {
            $this->devices = [];
        }
    }

    public function loadTickets()
    {
        $this->tickets = Ticket::whereHas('customer', function ($query) {
            $query->where('user_id', auth()->id());
        })->get();
    }


    public function updatedSelectedCustomer()
    {
        // Seçilen müşteri bilgilerini forma yükle
        $customer = Customer::find(id: $this->selectedCustomer);
        if ($customer) {
            $this->newCustomerFirstName = $customer->fname;
            $this->newCustomerLastName = $customer->lname;
            $this->newCustomerCompany = $customer->company;
            $this->newCustomerPhone = $customer->phone;
            $this->newCustomerEmail = $customer->email;
            $this->newCustomerAdress = $customer->address;
            $this->loadDevices(); // Müşteriye ait cihazları getir
        } else {
            $this->reset();
        }
    }

    public function updatedSelectedDevice()
    {
        // Seçilen cihaz bilgilerini forma yükle
        // $device = Device::find($this->selectedDevice);
        // if ($device) {
        //     $this->deviceModel = $device->model;
        //     $this->deviceSerial = $device->serial_number;
        // } else {
        //     $this->reset(['deviceModel', 'deviceSerial']);
        // }
    }

    public function createCustomerAndDevice()
    {
        $this->validate([
            'newCustomerFirstName' => 'required|string|max:255',
            'newCustomerLastName' => 'required|string|max:255',
            'newCustomerCompany' => 'required|string|max:255',
            'newCustomerPhone' => 'required|string|max:255',
            'newCustomerEmail' => 'required|string|max:255',
            'newCustomerAdress' => 'required|string|max:255',
        ]);

        // Yeni müşteri oluştur
        $customer = auth()->user()->customers()->create([
            'fname' => $this->newCustomerFirstName,
            'lname' => $this->newCustomerLastName,
            'company' => $this->newCustomerCompany,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
            'address' => $this->newCustomerAdress,
        ]);


        // Müşteriye cihaz ekle
        $device = Device::create([
            'model' => $this->newDeviceModel,
            'serial_number' => $this->newDeviceSerial,
            'user_id' => auth()->id(),
        ]);

        CustomerDevice::create([
            'customer_id' => $customer->id,
            'device_id' => $device->id,
        ]);

        // Yeni müşteri ve cihazı seçili yap
        $this->selectedCustomer = $customer->id;
        $this->selectedDevice = $device->id;

        // Formu sıfırla
        $this->reset();
        $this->loadCustomers();
        $this->loadDevices();
    }

    public function createTicket()
    {
        $this->validate([
            'selectedCustomer' => 'required|exists:customers,id',
            'selectedDevice' => 'required|exists:devices,id',
            'problem' => 'required|string|min:10',
        ]);

        Ticket::create([
            'customer_id' => $this->selectedCustomer,
            'device_id' => $this->selectedDevice,
            'problem' => $this->problem,
            'status' => 'pending',
        ]);

        $this->reset(['problem']);
        $this->loadTickets();
    }
    public function render()
    {
        return view('livewire.tickets.tickets-form');
    }
}



