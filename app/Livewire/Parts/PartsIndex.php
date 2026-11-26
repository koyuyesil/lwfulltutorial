<?php

namespace App\Livewire\Parts;

use App\Enums\ComponentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Yedek parça kataloğu ve stok. Çapraz referans parçaları da (SoC, RAM, PMIC…) bu listededir;
 * bir parça bağımsız olabilir ya da cihazlara ve muadillerine bağlanabilir (Parts\PartRelations).
 */
#[Title('Parça ve stok')]
class PartsIndex extends Component
{
    use \App\Livewire\Concerns\UndoableDelete;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'kategori', except: '')]
    public string $category = '';

    #[Url(as: 'stok', except: '')]
    public string $stockFilter = ''; // '' | low | out | ref

    public ?int $editingId = null;

    public array $form = [];

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->form = ['name' => '', 'sku' => '', 'category' => '', 'type' => '', 'code' => '', 'manufacturer' => '', 'compatible' => '', 'cost' => '', 'price' => '', 'stock' => '0', 'min_stock' => '1', 'supplier' => ''];
        $this->editingId = null;
        $this->resetValidation();
    }

    public function updating($name): void
    {
        if (in_array($name, ['search', 'category', 'stockFilter'], true)) {
            $this->resetPage();
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-drawer', name: 'part-form');
    }

    #[On('open-part')]
    public function edit(int $id): void
    {
        $part = Auth::user()->parts()->findOrFail($id);
        $this->resetForm();
        $this->editingId = $part->id;
        $this->form = [
            'name' => $part->name, 'sku' => (string) $part->sku, 'category' => (string) $part->category,
            'type' => (string) $part->type?->value, 'code' => (string) $part->code, 'manufacturer' => (string) $part->manufacturer,
            'compatible' => (string) $part->compatible,
            'cost' => (string) (float) $part->cost, 'price' => (string) (float) $part->price, 'stock' => (string) $part->stock,
            'min_stock' => (string) $part->min_stock, 'supplier' => (string) $part->supplier,
        ];
        $this->dispatch('open-drawer', name: 'part-form');
    }

    public function save(): void
    {
        foreach (['cost', 'price'] as $k) {
            $this->form[$k] = str_replace(',', '.', (string) $this->form[$k]);
        }

        $data = $this->validate([
            'form.name' => ['required', 'string', 'min:2', 'max:120'],
            'form.sku' => ['nullable', 'string', 'max:60'],
            'form.category' => ['nullable', 'string', 'max:60'],
            'form.type' => ['nullable', Rule::enum(ComponentType::class)],
            'form.code' => ['nullable', 'required_with:form.type', 'string', 'max:120'],
            'form.manufacturer' => ['nullable', 'string', 'max:120'],
            'form.compatible' => ['nullable', 'string', 'max:255'],
            'form.cost' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'form.price' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'form.stock' => ['required', 'integer', 'min:-9999', 'max:999999'],
            'form.min_stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'form.supplier' => ['nullable', 'string', 'max:120'],
        ], [
            'form.name.required' => 'Parça adı zorunludur.',
            'form.code.required_with' => 'Çapraz referans türü seçildiyse parça kodu da girilmelidir.',
        ], ['form.type' => 'tür', 'form.code' => 'parça kodu', 'form.manufacturer' => 'üretici'])['form'];

        $data = array_map(fn ($v) => is_string($v) ? (trim($v) === '' ? null : trim($v)) : $v, $data);
        $data['cost'] ??= 0;
        $data['price'] ??= 0;

        if ($this->editingId) {
            Auth::user()->parts()->findOrFail($this->editingId)->update($data);
            $message = 'Parça güncellendi.';
        } else {
            Auth::user()->parts()->create($data);
            $message = 'Parça eklendi.';
        }

        $this->dispatch('close-drawer', name: 'part-form');
        $this->dispatch('toast', message: $message);
        $this->resetForm();
    }

    /** Hızlı stok düzeltme (+ / -) */
    public function adjust(int $id, int $delta): void
    {
        $part = Auth::user()->parts()->findOrFail($id);
        $part->increment('stock', max(-1000, min(1000, $delta)));
    }

    public function delete(int $id): void
    {
        $this->trashWithUndo(Auth::user()->parts()->findOrFail($id));
    }

    #[On('cross-ref-saved')]
    public function refreshList(): void {}

    public function render()
    {
        $user = Auth::user();
        $base = $user->parts();

        $parts = $user->parts()
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('sku', 'like', $term)->orWhere('code', 'like', $term)
                    ->orWhere('compatible', 'like', $term)->orWhere('supplier', 'like', $term)->orWhere('manufacturer', 'like', $term)
                    ->orWhereHas('products', fn ($p) => $p->where('model_name', 'like', $term)->orWhere('codename', 'like', $term)));
            })
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->when($this->stockFilter === 'low', fn ($q) => $q->low())
            // Stok takibi olmayan çapraz referans kayıtları "tükenen" sayılmaz
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('stock', '<=', 0)->where(fn ($q) => $q->where('min_stock', '>', 0)->orWhereNull('type')))
            ->when($this->stockFilter === 'ref', fn ($q) => $q->reference())
            ->withCount(['items', 'products', 'compatibleTo', 'compatibleFrom'])
            ->orderByRaw('CASE WHEN min_stock > 0 AND stock <= min_stock THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->paginate(24);

        $stats = [
            'count' => (clone $base)->count(),
            'low' => (clone $base)->low()->count(),
            'value' => (float) (clone $base)->where('stock', '>', 0)->selectRaw('COALESCE(SUM(stock * cost), 0) as v')->value('v'),
        ];

        return view('livewire.parts.parts-index', [
            'parts' => $parts,
            'stats' => $stats,
            'categories' => $user->parts()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ])->layout('layouts.app');
    }
}
