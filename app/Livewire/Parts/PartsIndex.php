<?php

namespace App\Livewire\Parts;

use App\Models\Part;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/** Yedek parça kataloğu ve stok. */
#[Title('Parça ve stok')]
class PartsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'kategori', except: '')]
    public string $category = '';

    #[Url(as: 'stok', except: '')]
    public string $stockFilter = ''; // '' | low | out

    public ?int $editingId = null;

    public array $form = [];

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->form = ['component_id' => '', 'name' => '', 'sku' => '', 'category' => '', 'compatible' => '', 'cost' => '', 'price' => '', 'stock' => '0', 'min_stock' => '1', 'supplier' => ''];
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

    public function edit(int $id): void
    {
        $part = Auth::user()->parts()->findOrFail($id);
        $this->resetForm();
        $this->editingId = $part->id;
        $this->form = [
            'component_id' => (string) $part->component_id, 'name' => $part->name, 'sku' => (string) $part->sku, 'category' => (string) $part->category, 'compatible' => (string) $part->compatible,
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
            'form.component_id' => ['nullable', \Illuminate\Validation\Rule::exists('components', 'id')->where('user_id', Auth::id())],
            'form.name' => ['required', 'string', 'min:2', 'max:120'],
            'form.sku' => ['nullable', 'string', 'max:60'],
            'form.category' => ['nullable', 'string', 'max:60'],
            'form.compatible' => ['nullable', 'string', 'max:255'],
            'form.cost' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'form.price' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'form.stock' => ['required', 'integer', 'min:-9999', 'max:999999'],
            'form.min_stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'form.supplier' => ['nullable', 'string', 'max:120'],
        ], [
            'form.name.required' => 'Parça adı zorunludur.',
        ])['form'];

        $data = array_map(fn ($v) => is_string($v) ? (trim($v) === '' ? null : trim($v)) : $v, $data);
        $data['cost'] ??= 0;
        $data['price'] ??= 0;
        $data['component_id'] = $data['component_id'] ? (int) $data['component_id'] : null;

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

    /** Hızlı stok düzeltme (+ / −) */
    public function adjust(int $id, int $delta): void
    {
        $part = Auth::user()->parts()->findOrFail($id);
        $part->increment('stock', max(-1000, min(1000, $delta)));
    }

    public function delete(int $id): void
    {
        Auth::user()->parts()->findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Parça silindi.');
    }

    public function render()
    {
        $user = Auth::user();
        $base = $user->parts();

        $parts = $user->parts()
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('sku', 'like', $term)->orWhere('compatible', 'like', $term)->orWhere('supplier', 'like', $term));
            })
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->when($this->stockFilter === 'low', fn ($q) => $q->low())
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('stock', '<=', 0))
            ->with('component:id,type,code,name')->withCount('items')
            ->orderByRaw('CASE WHEN stock <= min_stock THEN 0 ELSE 1 END')
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
            'components' => $user->components()->orderBy('type')->orderBy('code')->get(['id', 'type', 'code', 'name']),
            'categories' => $user->parts()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ])->layout('layouts.app');
    }
}
