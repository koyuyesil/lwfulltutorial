<?php

namespace App\Livewire\Parts;

use App\Enums\CompatibilityKind;
use App\Models\Part;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Parçanın ilişkileri, iki ayrı eşleştirme tablosu:
 *  1. Bulunduğu cihazlar (part_product)
 *  2. Muadiller: Bu parça · Uyum · Muadil parça (part_compatibilities, iki yönlü)
 * Hem Parça ve stok sayfasında hem çapraz referans parça düzenleyicisinde kullanılır.
 */
class PartRelations extends Component
{
    #[Locked]
    public int $partId;

    public string $deviceSearch = '';

    public string $variant = '';

    public string $compatSearch = '';

    public bool $sameType = true;

    public string $kind = 'direct';

    public string $compatNote = '';

    public function mount(int $partId): void
    {
        $this->partId = Auth::user()->parts()->findOrFail($partId)->id;
    }

    protected function part(): Part
    {
        return Auth::user()->parts()->findOrFail($this->partId);
    }

    #[On('cross-ref-saved')]
    public function refresh(): void {}

    /* ---------------------------------------------------------------- cihazlar */

    #[Computed]
    public function deviceResults()
    {
        $term = trim($this->deviceSearch);
        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Auth::user()->products()
            ->where(fn ($q) => $q->where('model_name', 'like', "%$term%")->orWhere('brand', 'like', "%$term%")
                ->orWhere('codename', 'like', "%$term%")->orWhere('model_number', 'like', "%$term%"))
            ->orderBy('brand')->orderBy('model_name')->limit(8)->get();
    }

    public function attachDevice(int $productId): void
    {
        $part = $this->part();
        $product = Auth::user()->products()->findOrFail($productId);
        $variant = trim($this->variant) ?: null;
        if (! $part->products()->wherePivot('product_id', $product->id)->wherePivot('variant', $variant)->exists()) {
            $part->products()->attach($product->id, ['variant' => $variant]);
        }
        $this->reset('deviceSearch', 'variant');
        $this->dispatch('cross-ref-saved');
    }

    public function detachDevice(int $pivotId): void
    {
        DB::table('part_product')->where('id', $pivotId)->where('part_id', $this->part()->id)->delete();
        $this->dispatch('cross-ref-saved');
    }

    /* ---------------------------------------------------------------- muadiller */

    #[Computed]
    public function compatResults()
    {
        $term = trim($this->compatSearch);
        if (mb_strlen($term) < 1) {
            return collect();
        }
        $part = $this->part();

        return Auth::user()->parts()
            ->whereKeyNot($part->id)
            ->when($this->sameType && $part->type, fn ($q) => $q->where('type', $part->type->value))
            ->where(fn ($q) => $q->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%")->orWhere('sku', 'like', "%$term%"))
            ->orderBy('code')->orderBy('name')->limit(8)->get();
    }

    public function link(int $otherId): void
    {
        $this->validate(['kind' => [Rule::enum(CompatibilityKind::class)], 'compatNote' => ['nullable', 'string', 'max:255']]);
        $this->part()->linkCompatible(Auth::user()->parts()->findOrFail($otherId), CompatibilityKind::from($this->kind), trim($this->compatNote) ?: null);
        $this->reset('compatSearch', 'compatNote');
        $this->dispatch('cross-ref-saved');
    }

    public function unlink(int $otherId): void
    {
        $this->part()->unlinkCompatible(Auth::user()->parts()->findOrFail($otherId));
        $this->dispatch('cross-ref-saved');
    }

    public function render()
    {
        $part = $this->part()->load(['products' => fn ($q) => $q->orderBy('brand')->orderBy('model_name')]);

        return view('livewire.parts.part-relations', [
            'part' => $part,
            'compatibles' => $part->compatibles(),
            // Muadiller sayesinde bu parçanın kullanılabileceği ek cihazlar
            'reachable' => Auth::user()->products()->whereIn('id', $part->reachableProductIds())->whereNotIn('id', $part->products->pluck('id'))
                ->orderBy('brand')->get(['id', 'brand', 'model_name']),
        ]);
    }
}
