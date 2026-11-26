<?php

namespace App\Livewire\CrossReference;

use App\Enums\ComponentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/** Cihazın bileşenleri (çekmece): türe göre gruplu liste, mevcut bileşen bağlama, yeni bileşen. */
class DeviceComponents extends Component
{
    #[Locked]
    public ?int $productId = null;

    public string $search = '';

    public string $variant = '';

    #[On('show-device-components')]
    public function show(int $id): void
    {
        $this->productId = Auth::user()->products()->findOrFail($id)->id;
        $this->reset('search', 'variant');
        $this->dispatch('open-drawer', name: 'device-components');
    }

    #[On('component-saved')]
    public function refresh(): void
    {
    }

    #[Computed]
    public function results()
    {
        $term = trim($this->search);
        if (! $this->productId || mb_strlen($term) < 1) {
            return collect();
        }

        return Auth::user()->components()
            ->where(fn ($q) => $q->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%"))
            ->orderBy('type')->orderBy('code')->limit(8)->get();
    }

    public function attach(int $componentId): void
    {
        $c = Auth::user()->components()->findOrFail($componentId);
        $variant = trim($this->variant) ?: null;
        if (! $c->products()->wherePivot('product_id', $this->productId)->wherePivot('variant', $variant)->exists()) {
            $c->products()->attach($this->productId, ['variant' => $variant]);
        }
        $this->reset('search', 'variant');
        $this->dispatch('component-saved');
    }

    public function detach(int $pivotId): void
    {
        DB::table('component_product')->where('id', $pivotId)->where('product_id', $this->productId)->delete();
        $this->dispatch('component-saved');
    }

    public function render()
    {
        $product = $this->productId
            ? Auth::user()->products()->with(['components' => fn ($q) => $q->with(['compatibleTo', 'compatibleFrom'])->orderBy('code')])->find($this->productId)
            : null;

        return view('livewire.cross-reference.device-components', [
            'product' => $product,
            'groups' => $product ? $product->components->groupBy(fn ($c) => $c->type->value) : collect(),
            'types' => ComponentType::cases(),
        ]);
    }
}
