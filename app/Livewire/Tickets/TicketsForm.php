<?php

namespace App\Livewire\Tickets;

use App\Models\Client;
use App\Models\Ticket;
use App\Models\Product;
use Livewire\Component;
use App\Enums\StatusType;
use App\Enums\PriorityType;
use App\Models\ClientProduct;
use Illuminate\Support\Facades\Auth;

class TicketsForm extends Component
{
    public $clients = []; // Kullanıcının sahip olduğu tüm müşteriler
    public $selectedClient; // Seçilen müşteri (ID)
    public $products = []; // Kullanıcının sahip olduğu tüm ürünler
    public $selectedProduct; // Seçilen ürün (ID)
    public $tickets = []; // Kullanıcının arıza kayıtları
    public $selectedClientProduct; // Seçilen müşteri-ürün ilişkisi (ID)

    // Client (Müşteri) alanları
    public $first_name; // Müşteri adı
    public $last_name; // Müşteri soyadı
    public $company; // Şirket adı
    public $phone; // Telefon numarası
    public $email; // E-posta adresi
    public $address; // Adres

    // Product (Ürün) alanları
    public $manufacturer; // Üretici
    public $brand; // Marka
    public $model_name; // Model ismi
    public $model_number; // Model numarası
    public $description; // Ürün açıklaması

    // ClientProduct (Müşteri-Ürün) alanları
    public $serial; // Seri numarası
    public $imei; // IMEI numarası
    public $color; // Renk

    // Ticket (Arıza Kaydı) alanları
    public $problem; // Arıza açıklaması

    public StatusType $status = StatusType::OPEN;

    public PriorityType $priority = PriorityType::LOW;

    // Bileşen yüklendiğinde ilk olarak müşterileri ve ürünleri yükle
    public function mount()
    {
        $this->loadClients();
        $this->loadProducts();
    }

    // Kullanıcının müşterilerini yükler
    public function loadClients()
    {
        $this->clients = Auth::user()->clients()->get(); // Kullanıcıya ait müşterileri al
    }

    // Kullanıcının ürünlerini yükler
    public function loadProducts()
    {
        $this->products = Auth::user()->products()->get(); // Kullanıcıya ait ürünleri al
    }

    // Seçilen müşteri değiştiğinde çağrılan metot
    public function updatedselectedClient()
    {
        if ($this->selectedClient) {
            $client = Client::find($this->selectedClient); // Seçilen müşteri ID'siyle müşteri bilgilerini al

            if ($client) {
                // Müşteri bilgilerini form alanlarına at
                $this->first_name = $client->first_name;
                $this->last_name = $client->last_name;
                $this->company = $client->company;
                $this->phone = $client->phone;
                $this->email = $client->email;
                $this->address = $client->address;
                $this->loadProducts(); // Müşteri seçildiğinde ürünleri de yükle
            } else {
                $this->reset(); // Müşteri bulunamazsa formu sıfırla
            }
        } else {
            $this->reset(); // Müşteri seçilmezse formu sıfırla
        }
    }

    // Seçilen ürün değiştiğinde çağrılan metot
    public function updatedselectedProduct()
    {
        if ($this->selectedProduct) {
            $product = Product::find($this->selectedProduct); // Seçilen ürün ID'siyle ürün bilgilerini al

            if ($product) {
                // Ürün bilgilerini form alanlarına at
                $this->manufacturer = $product->manufacturer;
                $this->brand = $product->brand;
                $this->model_name = $product->model_name;
                $this->model_number = $product->model_number;
                $this->description = $product->description;
            } else {
                $this->reset(); // Ürün bulunamazsa formu sıfırla
            }
        } else {
            $this->reset(); // Ürün seçilmezse formu sıfırla
        }
    }

    // Yeni müşteri ve ürün oluşturma işlemi
    public function createClientAndProduct()
    {
        // Müşteri verisi için doğrulama
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        // Yeni müşteri oluştur
        $client = Auth::user()->clients()->create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
        ]);

        // Yeni ürün oluştur
        $product = Auth::user()->products()->create([
            'manufacturer' => $this->manufacturer,
            'brand' => $this->brand,
            'model_name' => $this->model_name,
            'model_number' => $this->model_number,
            'description' => $this->description,
        ]);

        // Müşteri ve ürün arasında ilişki oluştur
        $clientProduct = ClientProduct::create([
            'user_id' => Auth::id(),
            'client_id' => $client->id,
            'product_id' => $product->id,
            'color' => $this->color,
            'serial' => $this->serial,
            'imei' => $this->imei,
        ]);

        // Seçilen müşteri ve ürün bilgilerini güncelle
        $this->selectedClient = $client->id;
        $this->selectedProduct = $product->id;
        $this->selectedClientProduct = $clientProduct->id;
    }

    // Arıza kaydını oluşturma işlemi
    public function create()
    {
        // Arıza kaydı için doğrulama
        $this->validate([
            'selectedClient' => 'required|exists:clients,id',
            'selectedProduct' => 'required|exists:products,id',
            'problem' => 'required|string|min:5',
            'priority' => 'required',
            'status' => 'required',
        ]);

        // Yeni arıza kaydı oluştur
        Ticket::create([
            'user_id' => Auth::id(),
            'client_product_id' => $this->selectedClientProduct,
            'problem' => $this->problem,
            'priority' => $this->priority,
            'status' => $this->status,
        ]);

        // Formu sıfırla ve tekrar yükle
        $this->reset();
        $this->loadClients();
        $this->loadProducts();
    }

    // Bileşenin render edilmesini sağlar (view dosyasını yükler)
    public function render()
    {
        return view('livewire.tickets.tickets-form'); // Form görünümünü döndür
    }
}
