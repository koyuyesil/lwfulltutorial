<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;
use App\Enums\PriorityType;
use App\Models\ClientProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
//TODO BURADA VAROLAN MÜŞTERİ VE CİHAZ YENİDEN OLUŞTURULMADAN KAYIT YAPILACAK AYRICA MEVCUT KAYIT SEÇİMİ DE YAPALIM.
//ÇOK İLİŞKİLİ UPDATE FORMU OLMASI GEREKLİ
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
    public function updatedSelectedClient()
    {
        $this->selectedClientProduct = null;

        // Sadece oturumdaki kullanıcının müşterileri seçilebilir
        $client = $this->selectedClient
            ? Auth::user()->clients()->find($this->selectedClient)
            : null;

        if (! $client) {
            $this->selectedClient = null;
            $this->reset(['first_name', 'last_name', 'company', 'phone', 'email', 'address']);

            return;
        }

        $this->first_name = $client->first_name;
        $this->last_name = $client->last_name;
        $this->company = $client->company;
        $this->phone = $client->phone;
        $this->email = $client->email;
        $this->address = $client->address;
    }

    // Seçilen ürün değiştiğinde çağrılan metot
    public function updatedSelectedProduct()
    {
        $this->selectedClientProduct = null;

        $product = $this->selectedProduct
            ? Auth::user()->products()->find($this->selectedProduct)
            : null;

        if (! $product) {
            $this->selectedProduct = null;
            $this->reset(['manufacturer', 'brand', 'model_name', 'model_number', 'description']);

            return;
        }

        $this->manufacturer = $product->manufacturer;
        $this->brand = $product->brand;
        $this->model_name = $product->model_name;
        $this->model_number = $product->model_number;
        $this->description = $product->description;
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
            'company' => (string) $this->company, // sütun NOT NULL, form alanı opsiyonel
            'phone' => $this->phone,
            'email' => (string) $this->email,
            'address' => (string) $this->address,
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
            'color' => (string) $this->color,
            'serial' => (string) $this->serial,
            'imei' => (string) $this->imei,
        ]);

        // Seçilen müşteri ve ürün bilgilerini güncelle
        $this->selectedClient = $client->id;
        $this->selectedProduct = $product->id;
        $this->selectedClientProduct = $clientProduct->id;

        // Yeni kayıtlar seçim listelerinde de görünsün
        $this->loadClients();
        $this->loadProducts();
    }

    // Arıza kaydını oluşturma işlemi
    public function create()
    {
        $userId = Auth::id();

        // Arıza kaydı için doğrulama (yalnızca kullanıcının kendi kayıtları)
        $this->validate([
            'selectedClient' => ['required', Rule::exists('clients', 'id')->where('user_id', $userId)],
            'selectedProduct' => ['required', Rule::exists('products', 'id')->where('user_id', $userId)],
            'problem' => 'required|string|min:5',
            'priority' => ['required', Rule::enum(PriorityType::class)],
        ]);

        // Mevcut müşteri + ürün seçildiyse aradaki cihaz kaydını bul ya da oluştur
        $clientProductId = $this->selectedClientProduct
            ?? ClientProduct::firstOrCreate(
                ['client_id' => $this->selectedClient, 'product_id' => $this->selectedProduct, 'user_id' => $userId],
                ['serial' => (string) $this->serial, 'imei' => (string) $this->imei, 'color' => (string) $this->color],
            )->id;

        Ticket::create([
            'user_id' => $userId,
            'client_product_id' => $clientProductId,
            'problem' => $this->problem,
            'priority' => $this->priority,
        ]);

        // Listeyi yenile, formu sıfırla (seçim listeleri korunur)
        $this->dispatch('ticket-created');
        $this->reset(array_diff(array_keys($this->all()), ['clients', 'products']));
        $this->resetValidation();
    }

    // Bileşenin render edilmesini sağlar (view dosyasını yükler)
    public function render()
    {
        return view('livewire.tickets.tickets-form'); // Form görünümünü döndür
    }
}
