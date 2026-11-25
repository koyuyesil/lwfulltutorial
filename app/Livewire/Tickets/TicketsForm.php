<?php

namespace App\Livewire\Tickets;

use App\Enums\PriorityType;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Tek ekranda servis kabulü: müşteri (mevcut / yeni) → cihaz (kayıtlı / yeni) → arıza.
 */
class TicketsForm extends Component
{
    public const PROBLEM_TAGS = [
        'Ekran kırık', 'Dokunmatik çalışmıyor', 'Şarj olmuyor', 'Açılmıyor', 'Sıvı teması',
        'Batarya şişmiş', 'Kamera arızası', 'Ses gelmiyor', 'Mikrofon arızası', 'Yazılım / güncelleme', 'Isınma', 'Şebeke yok',
    ];

    /* Müşteri */
    public string $clientMode = 'existing'; // existing | new

    public string $clientSearch = '';

    public ?int $selectedClient = null;

    public array $newClient = ['first_name' => '', 'last_name' => '', 'phone' => '', 'email' => '', 'company' => ''];

    /* Cihaz */
    public ?int $selectedClientProduct = null;

    public string $productMode = 'existing'; // existing | new

    public string $productSearch = '';

    public ?int $selectedProduct = null;

    public array $newProduct = ['brand' => '', 'model_name' => ''];

    public string $imei = '';

    public string $serial = '';

    public string $color = '';

    /* Arıza */
    public string $problem = '';

    public PriorityType $priority = PriorityType::NORMAL;

    /** Başarılı kayıttan sonra gösterilen özet. */
    public ?int $createdTicketId = null;

    public function mount(): void
    {
        $clientId = (int) request()->query('musteri');

        if ($clientId && Auth::user()->clients()->whereKey($clientId)->exists()) {
            $this->selectedClient = $clientId;
        }
    }

    #[On('create-ticket')]
    public function resetForm(): void
    {
        $this->reset();
        $this->resetValidation();
    }

    /* ------------------------------------------------------------ Müşteri */

    public function updatedClientSearch(): void
    {
        unset($this->clientResults);
    }

    public function selectClient(int $id): void
    {
        $this->selectedClient = Auth::user()->clients()->findOrFail($id)->id;
        $this->clientSearch = '';
        $this->selectedClientProduct = null;
        $this->resetValidation('selectedClient');
    }

    public function updatedSelectedClient(): void
    {
        $this->selectedClientProduct = null;
    }

    public function clearClient(): void
    {
        $this->reset('selectedClient', 'selectedClientProduct', 'clientSearch');
    }

    public function setClientMode(string $mode): void
    {
        $this->clientMode = $mode === 'new' ? 'new' : 'existing';
        $this->selectedClientProduct = null;
        $this->resetValidation();

        // Aramada yazılan ifadeyi yeni müşteri formuna taşı (telefon ya da ad)
        if ($this->clientMode === 'new' && $this->clientSearch !== '') {
            $digits = preg_replace('/\D/', '', $this->clientSearch);
            if (strlen($digits) >= 7) {
                $this->newClient['phone'] = $this->clientSearch;
            } else {
                [$first, $last] = array_pad(explode(' ', trim($this->clientSearch), 2), 2, '');
                $this->newClient['first_name'] = $first;
                $this->newClient['last_name'] = $last;
            }
        }
    }

    #[Computed]
    public function clientResults()
    {
        $term = trim($this->clientSearch);
        if (mb_strlen($term) < 2) {
            return collect();
        }
        $digits = preg_replace('/\D/', '', $term);

        return Auth::user()->clients()
            ->where(function (Builder $q) use ($term, $digits) {
                $q->where('first_name', 'like', "%$term%")
                    ->orWhere('last_name', 'like', "%$term%")
                    ->orWhere('company', 'like', '%'.Client::trUpper($term).'%');
                if (strlen($digits) >= 3) {
                    $q->orWhereRaw(Client::phoneDigitsSql().' like ?', ["%$digits%"]);
                }
            })
            ->withCount('clientsProducts')
            ->limit(6)->get();
    }

    #[Computed]
    public function client(): ?Client
    {
        return $this->selectedClient
            ? Auth::user()->clients()->with('clientsProducts.product')->find($this->selectedClient)
            : null;
    }

    /* ------------------------------------------------------------ Cihaz */

    public function selectDevice(?int $id): void
    {
        $this->selectedClientProduct = $id && $this->client?->clientsProducts->contains('id', $id) ? $id : null;
    }

    public function selectProduct(int $id): void
    {
        $this->selectedProduct = Auth::user()->products()->findOrFail($id)->id;
        $this->productSearch = '';
        $this->resetValidation('selectedProduct');
    }

    public function clearProduct(): void
    {
        $this->reset('selectedProduct', 'productSearch');
    }

    public function setProductMode(string $mode): void
    {
        $this->productMode = $mode === 'new' ? 'new' : 'existing';
        $this->resetValidation();

        if ($this->productMode === 'new' && $this->productSearch !== '') {
            [$brand, $model] = array_pad(explode(' ', trim($this->productSearch), 2), 2, '');
            $this->newProduct = ['brand' => $brand, 'model_name' => $model];
        }
    }

    #[Computed]
    public function productResults()
    {
        $term = trim($this->productSearch);
        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Auth::user()->products()
            ->where(fn (Builder $q) => $q->where('brand', 'like', "%$term%")
                ->orWhere('model_name', 'like', "%$term%")
                ->orWhere('model_number', 'like', "%$term%")
                ->orWhereRaw(
                    (DB::getDriverName() === 'mysql' ? "CONCAT(brand, ' ', model_name)" : "(brand || ' ' || model_name)").' like ?',
                    ["%$term%"],
                ))
            ->limit(6)->get();
    }

    #[Computed]
    public function product(): ?Product
    {
        return $this->selectedProduct ? Auth::user()->products()->find($this->selectedProduct) : null;
    }

    /** Girilen IMEI/seri daha önce kayıtlı mı? (tekrar gelen cihaz tespiti) */
    #[Computed]
    public function knownDevice(): ?ClientProduct
    {
        $imei = trim($this->imei);
        $serial = trim($this->serial);

        if (strlen($imei) < 8 && strlen($serial) < 5) {
            return null;
        }

        return Auth::user()->clientsProducts()
            ->where(fn ($q) => $q
                ->when(strlen($imei) >= 8, fn ($q) => $q->orWhere('imei', $imei))
                ->when(strlen($serial) >= 5, fn ($q) => $q->orWhere('serial', $serial)))
            ->with(['client', 'product', 'tickets' => fn ($q) => $q->latest('id')])
            ->first();
    }

    public function useKnownDevice(): void
    {
        $device = $this->knownDevice;
        if (! $device) {
            return;
        }

        $this->clientMode = 'existing';
        $this->selectedClient = $device->client_id;
        $this->selectedClientProduct = $device->id;
        $this->reset('imei', 'serial', 'color', 'clientSearch');
        unset($this->client, $this->knownDevice);
    }

    /* ------------------------------------------------------------ Arıza */

    public function addTag(string $tag): void
    {
        if (! in_array($tag, self::PROBLEM_TAGS, true) || str_contains($this->problem, $tag)) {
            return;
        }

        $this->problem = trim($this->problem) === '' ? $tag : rtrim($this->problem, " ,.").', '.mb_strtolower($tag);
    }

    /* ------------------------------------------------------------ Kayıt */

    protected function rules(): array
    {
        $userId = Auth::id();
        $rules = [
            'problem' => ['required', 'string', 'min:5', 'max:2000'],
            'priority' => ['required', Rule::enum(PriorityType::class)],
        ];

        if ($this->clientMode === 'existing') {
            $rules['selectedClient'] = ['required', Rule::exists('clients', 'id')->where('user_id', $userId)];
        } else {
            $rules += [
                'newClient.first_name' => ['required', 'string', 'min:2', 'max:60'],
                'newClient.last_name' => ['required', 'string', 'min:2', 'max:60'],
                'newClient.phone' => ['required', 'string', 'max:25', 'regex:/^\+?[0-9()\s-]{10,}$/'],
                'newClient.email' => ['nullable', 'email', 'max:120'],
                'newClient.company' => ['nullable', 'string', 'max:120'],
            ];
        }

        if ($this->selectedClientProduct && $this->clientMode === 'existing') {
            $rules['selectedClientProduct'] = [Rule::exists('client_products', 'id')
                ->where('user_id', $userId)->where('client_id', $this->selectedClient)];
        } else {
            if ($this->productMode === 'existing') {
                $rules['selectedProduct'] = ['required', Rule::exists('products', 'id')->where('user_id', $userId)];
            } else {
                $rules['newProduct.brand'] = ['required', 'string', 'min:2', 'max:60'];
                $rules['newProduct.model_name'] = ['required', 'string', 'max:80'];
            }
            $rules += [
                'imei' => ['nullable', 'regex:/^[0-9]{14,16}$/'],
                'serial' => ['nullable', 'string', 'max:50'],
                'color' => ['nullable', 'string', 'max:30'],
            ];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'selectedClient.required' => 'Bir müşteri seçin ya da yeni müşteri ekleyin.',
            'selectedClient.exists' => 'Seçilen müşteri bulunamadı.',
            'selectedProduct.required' => 'Cihaz modelini seçin ya da yeni model ekleyin.',
            'newClient.first_name.required' => 'Ad zorunludur.',
            'newClient.last_name.required' => 'Soyad zorunludur.',
            'newClient.phone.required' => 'Telefon zorunludur.',
            'newClient.phone.regex' => 'Geçerli bir telefon girin (en az 10 hane).',
            'newProduct.brand.required' => 'Marka zorunludur.',
            'newProduct.model_name.required' => 'Model adı zorunludur.',
            'imei.regex' => 'IMEI 15 haneli bir sayı olmalıdır.',
            'problem.required' => 'Arıza açıklaması zorunludur.',
            'problem.min' => 'Arızayı biraz daha detaylı yazın (en az 5 karakter).',
        ];
    }

    public function create(): void
    {
        $this->validate();
        $user = Auth::user();

        $ticket = DB::transaction(function () use ($user) {
            $clientId = $this->clientMode === 'existing'
                ? $this->selectedClient
                : $user->clients()->create($this->newClient)->id;

            if ($this->selectedClientProduct && $this->clientMode === 'existing') {
                $deviceId = $this->selectedClientProduct;
            } else {
                $productId = $this->productMode === 'existing'
                    ? $this->selectedProduct
                    : $user->products()->firstOrCreate(
                        ['brand' => trim($this->newProduct['brand']), 'model_name' => trim($this->newProduct['model_name'])],
                        ['manufacturer' => trim($this->newProduct['brand'])],
                    )->id;

                $deviceId = ClientProduct::create([
                    'user_id' => $user->id,
                    'client_id' => $clientId,
                    'product_id' => $productId,
                    'imei' => trim($this->imei) ?: null,
                    'serial' => trim($this->serial) ?: null,
                    'color' => trim($this->color) ?: null,
                ])->id;
            }

            return Ticket::create([
                'user_id' => $user->id,
                'client_product_id' => $deviceId,
                'problem' => trim($this->problem),
                'priority' => $this->priority,
            ]);
        });

        $this->reset();
        $this->resetValidation();
        $this->createdTicketId = $ticket->id;

        $this->dispatch('ticket-created');
        $this->dispatch('toast', message: "Servis kaydı açıldı: {$ticket->tracking_code}");
    }

    public function render()
    {
        $created = $this->createdTicketId
            ? Auth::user()->tickets()->with('clientProduct.client', 'clientProduct.product')->find($this->createdTicketId)
            : null;

        return view('livewire.tickets.tickets-form', ['created' => $created]);
    }
}
