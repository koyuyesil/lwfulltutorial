<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Ticket;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class TicketsForm extends Component
{
    public $customers = [];
    public $selectedCustomer;
    public $devices = [];
    public $selectedDevice;
    public $tickets = [];
    public $selectedcustomerDevice;

    // Customer fields
    public $fname;
    public $lname;
    public $company;
    public $phone;
    public $email;
    public $address;

    // Device fields
    public $manufacturer;
    public $brand;
    public $model_name;
    public $model_number;
    public $description;

    //CustomerDevice fields
    public $serial;
    public $imei;
    public $color;

    //ticket fields
    public $problem;
    public $priority;

    public function mount()
    {
        $this->loadCustomers();
        $this->loadDevices();
    }

    public function loadCustomers()
    {
        $this->customers = Auth::user()->customers()->get();
    }

    public function loadDevices()
    {
        $this->devices = Auth::user()->devices()->get();
    }

    public function updatedSelectedCustomer()
    {
        if ($this->selectedCustomer) {
            $customer = Customer::find($this->selectedCustomer);

            if ($customer) {
                $this->fname = $customer->fname;
                $this->lname = $customer->lname;
                $this->company = $customer->company;
                $this->phone = $customer->phone;
                $this->email = $customer->email;
                $this->address = $customer->address;
                $this->loadDevices();
            } else {
                $this->reset();
            }
        } else {
            $this->reset();
        }
    }

    public function updatedSelectedDevice()
    {
        if ($this->selectedDevice) {
            $device = Device::find($this->selectedDevice);

            if ($device) {
                $this->manufacturer = $device->manufacturer;
                $this->brand = $device->brand;
                $this->model_name = $device->model_name;
                $this->model_number = $device->model_number;
                $this->description = $device->description;
            } else {
                $this->reset();
            }
        } else {
            $this->reset();
        }
    }

    public function createCustomerAndDevice()
    {
        $this->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $customer = Auth::user()->customers()->create([
            'fname' => $this->fname,
            'lname' => $this->lname,
            'company' => $this->company,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
        ]);

        $device = Auth::user()->devices()->create([
            'manufacturer' => $this->manufacturer,
            'brand' => $this->brand,
            'model_name' => $this->model_name,
            'model_number' => $this->model_number,
            'description' => $this->description,

        ]);

        //TODO EĞER HAZIR SEÇİLİRSE ONU EKLE TRANSACTİON KONTROLLERİNİ DE EKLE
        $customerDevice = CustomerDevice::create([
            'user_id' => Auth::id(),
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'color' => $this->color,
            'serial' => $this->serial,
            'imei' => $this->imei,
        ]);

        $this->selectedCustomer = $customer->id;
        $this->selectedDevice = $device->id;
        $this->selectedcustomerDevice = $customerDevice->id;


    }

    public function createTicket()
    {
        $this->validate([
            'selectedCustomer' => 'required|exists:customers,id',
            'selectedDevice' => 'required|exists:devices,id',
            'problem' => 'required|string|min:10',
            'priority' => 'required|string|in:low,medium,high',
        ]);

        Ticket::create([
            'customer_device_id' => $this->selectedcustomerDevice,
            'problem' => $this->problem,
            'priority' => $this->priority,
            'status' => 'pending',
        ]);

        $this->reset();
        $this->loadCustomers();
        $this->loadDevices();
    }

    public function render()
    {
        return view('livewire.tickets.tickets-form');
    }
}
