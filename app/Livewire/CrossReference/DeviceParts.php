<?php

namespace App\Livewire\CrossReference;

use App\Enums\ComponentType;
use App\Services\CrossReferenceCatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Telefonun parçaları (çekmece): Tür · Parça · Varyant · Muadiller eşleştirme tablosu.
 * Kütüphaneden parça bağlama ve hızlı tür düğmeleriyle aynı panelde açılan (katlanır) yeni parça formu.
 */
class DeviceParts extends Component
{
    #[Locked]
    public ?int $productId = null;

    public string $search = '';

    public string $variant = '';

    /** Katlanır yeni parça formu: boşsa kapalı */
    public string $newType = '';

    public string $newCode = '';

    public string $newName = '';

    public string $newManufacturer = '';

    public string $newVariant = '';

    #[On('show-device-parts')]
    public function show(int $id): void
    {
        $this->productId = Auth::user()->products()->findOrFail($id)->id;
        $this->reset('search', 'variant');
        $this->closeNew();
        $this->dispatch('open-drawer', name: 'device-parts');
    }

    #[On('cross-ref-saved')]
    public function refresh(): void {}

    #[Computed]
    public function results()
    {
        $term = trim($this->search);
        if (! $this->productId || mb_strlen($term) < 1) {
            return collect();
        }

        return Auth::user()->parts()
            ->where(fn ($q) => $q->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%")->orWhere('sku', 'like', "%$term%"))
            ->orderBy('type')->orderBy('code')->limit(8)->get();
    }

    public function attach(int $partId): void
    {
        $part = Auth::user()->parts()->findOrFail($partId);
        $this->link($part->id, trim($this->variant) ?: null);
        $this->reset('search', 'variant');
    }

    public function detach(int $pivotId): void
    {
        DB::table('part_product')->where('id', $pivotId)->where('product_id', $this->productId)->delete();
        $this->dispatch('cross-ref-saved');
    }

    /* ---------------------------------------------------------------- katlanır yeni parça formu */

    /** Hızlı tür düğmesi: aynı türe tekrar basılırsa form kapanır */
    public function openNew(string $type): void
    {
        if ($this->newType === $type) {
            $this->closeNew();

            return;
        }
        $this->newType = ComponentType::tryFrom($type)?->value ?? 'other';
        $this->resetValidation();
    }

    public function closeNew(): void
    {
        $this->reset('newType', 'newCode', 'newName', 'newManufacturer', 'newVariant');
        $this->resetValidation();
    }

    public function createPart(): void
    {
        abort_unless($this->productId, 404);
        $this->newCode = trim($this->newCode);
        $this->validate([
            'newType' => ['required', Rule::enum(ComponentType::class)],
            'newCode' => ['required', 'string', 'max:120'],
            'newName' => ['nullable', 'string', 'max:120'],
            'newManufacturer' => ['nullable', 'string', 'max:120'],
            'newVariant' => ['nullable', 'string', 'max:60'],
        ], ['newCode.required' => 'Parça / model kodu zorunludur.'], ['newCode' => 'parça kodu']);

        // Aynı tür ve kodla kayıtlı parça varsa yenisi açılmaz, o bağlanır
        $part = Auth::user()->parts()->where('type', $this->newType)->whereRaw('LOWER(code) = ?', [mb_strtolower($this->newCode)])->first()
            ?? Auth::user()->parts()->create([
                'type' => $this->newType, 'code' => $this->newCode,
                'name' => trim($this->newName) ?: $this->newCode,
                'manufacturer' => trim($this->newManufacturer) ?: null,
                'category' => CrossReferenceCatalog::categoryFor($this->newType),
                'cost' => 0, 'price' => 0, 'stock' => 0, 'min_stock' => 0,
            ]);

        $this->link($part->id, trim($this->newVariant) ?: null);
        $this->dispatch('toast', message: $part->wasRecentlyCreated ? "{$part->ref()} oluşturuldu ve bağlandı." : "{$part->ref()} zaten kayıtlıydı; cihaza bağlandı.");
        $this->closeNew();
    }

    protected function link(int $partId, ?string $variant): void
    {
        $exists = DB::table('part_product')->where(['part_id' => $partId, 'product_id' => $this->productId, 'variant' => $variant])->exists();
        if (! $exists) {
            Auth::user()->parts()->find($partId)->products()->attach($this->productId, ['variant' => $variant]);
        }
        $this->dispatch('cross-ref-saved');
    }

    public function render()
    {
        $product = $this->productId
            ? Auth::user()->products()->with(['parts' => fn ($q) => $q->with(['compatibleTo', 'compatibleFrom'])->orderBy('type')->orderBy('code')])->find($this->productId)
            : null;

        return view('livewire.cross-reference.device-parts', [
            'product' => $product,
            'types' => ComponentType::cases(),
        ]);
    }
}
