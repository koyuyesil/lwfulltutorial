<?php

namespace App\Livewire\CrossReference;

use App\Enums\CompatibilityKind;
use App\Enums\ComponentType;
use App\Models\Component as ComponentModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Bileşen düzenleyici (çekmece): bilgiler + bulunduğu cihazlar + aynı tablodan uyumlu bileşenler.
 */
class ComponentEditor extends Component
{
    #[Locked]
    public ?int $componentId = null;

    public string $type = 'soc';

    public string $code = '';

    public string $name = '';

    public string $manufacturer = '';

    public string $notes = '';

    /** @var list<array{0: string, 1: string}> */
    public array $specs = [];

    public string $deviceSearch = '';

    public string $variant = '';

    public string $compatSearch = '';

    public bool $sameType = true;

    public string $kind = 'direct';

    public string $compatNote = '';

    /** Yeni bileşen oluşturulunca bağlanacak cihaz (cihaz çekmecesinden açılınca) */
    #[Locked]
    public ?int $attachProductId = null;

    #[On('create-component')]
    public function create(?string $type = null, ?int $productId = null): void
    {
        $this->reset();
        $this->resetValidation();
        $this->type = ComponentType::tryFrom((string) $type)?->value ?? 'soc';
        $this->specs = array_map(fn ($k) => [$k, ''], ComponentType::from($this->type)->specKeys());
        $this->attachProductId = $productId && Auth::user()->products()->whereKey($productId)->exists() ? $productId : null;
        $this->dispatch('open-drawer', name: 'component-editor');
    }

    #[On('edit-component')]
    public function edit(int $id): void
    {
        $c = Auth::user()->components()->findOrFail($id);
        $this->reset();
        $this->resetValidation();
        $this->componentId = $c->id;
        $this->type = $c->type->value;
        $this->code = $c->code;
        $this->name = (string) $c->name;
        $this->manufacturer = (string) $c->manufacturer;
        $this->notes = (string) $c->notes;
        $this->specs = collect((array) $c->specs)->map(fn ($v, $k) => [(string) $k, (string) $v])->values()->all();
        $this->dispatch('open-drawer', name: 'component-editor');
    }

    public function updatedType(): void
    {
        // Boş özellik satırları yeni türün önerilen alanlarıyla değişir
        $filled = array_values(array_filter($this->specs, fn ($s) => trim($s[1] ?? '') !== ''));
        $keys = array_column($filled, 0);
        foreach (ComponentType::tryFrom($this->type)?->specKeys() ?? [] as $k) {
            if (! in_array($k, $keys, true)) {
                $filled[] = [$k, ''];
            }
        }
        $this->specs = $filled;
    }

    public function addSpec(): void
    {
        $this->specs[] = ['', ''];
    }

    public function removeSpec(int $i): void
    {
        unset($this->specs[$i]);
        $this->specs = array_values($this->specs);
    }

    protected function component(): ?ComponentModel
    {
        return $this->componentId ? Auth::user()->components()->find($this->componentId) : null;
    }

    public function save(): void
    {
        $this->code = trim($this->code);
        $this->validate([
            'type' => ['required', Rule::enum(ComponentType::class)],
            'code' => ['required', 'string', 'max:120', Rule::unique('components', 'code')->where('user_id', Auth::id())->where('type', $this->type)->ignore($this->componentId)],
            'name' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'specs.*.0' => ['nullable', 'string', 'max:40'],
            'specs.*.1' => ['nullable', 'string', 'max:120'],
        ], [
            'code.required' => 'Parça / model kodu zorunludur.',
            'code.unique' => 'Bu türde aynı kodla bir bileşen zaten var.',
        ]);

        $data = [
            'type' => $this->type,
            'code' => $this->code,
            'name' => trim($this->name) ?: null,
            'manufacturer' => trim($this->manufacturer) ?: null,
            'notes' => trim($this->notes) ?: null,
            'specs' => collect($this->specs)->filter(fn ($s) => trim($s[0] ?? '') !== '' && trim($s[1] ?? '') !== '')
                ->mapWithKeys(fn ($s) => [trim($s[0]) => trim($s[1])])->all() ?: null,
        ];

        if ($c = $this->component()) {
            $c->update($data);
        } else {
            $c = Auth::user()->components()->create($data);
            $this->componentId = $c->id;
            if ($this->attachProductId) {
                $c->products()->attach($this->attachProductId);
                $this->attachProductId = null;
            }
        }

        $this->dispatch('component-saved');
        $this->dispatch('toast', message: 'Bileşen kaydedildi.');
    }

    /* ---------------------------------------------------------------- cihazlar */

    #[Computed]
    public function deviceResults()
    {
        $term = trim($this->deviceSearch);
        if (mb_strlen($term) < 2 || ! $this->componentId) {
            return collect();
        }

        return Auth::user()->products()
            ->where(fn ($q) => $q->where('model_name', 'like', "%$term%")->orWhere('brand', 'like', "%$term%")
                ->orWhere('codename', 'like', "%$term%")->orWhere('model_number', 'like', "%$term%"))
            ->orderBy('brand')->limit(8)->get();
    }

    public function attachDevice(int $productId): void
    {
        $c = $this->component() ?? abort(404);
        $product = Auth::user()->products()->findOrFail($productId);
        $variant = trim($this->variant) ?: null;
        if (! $c->products()->wherePivot('product_id', $product->id)->wherePivot('variant', $variant)->exists()) {
            $c->products()->attach($product->id, ['variant' => $variant]);
        }
        $this->reset('deviceSearch', 'variant');
        $this->dispatch('component-saved');
    }

    public function detachDevice(int $pivotId): void
    {
        $c = $this->component() ?? abort(404);
        \Illuminate\Support\Facades\DB::table('component_product')->where('id', $pivotId)->where('component_id', $c->id)->delete();
        $this->dispatch('component-saved');
    }

    /* ---------------------------------------------------------------- uyumluluk (aynı tablodan) */

    #[Computed]
    public function compatResults()
    {
        $term = trim($this->compatSearch);
        if (! $this->componentId || mb_strlen($term) < 1) {
            return collect();
        }

        return Auth::user()->components()
            ->whereKeyNot($this->componentId)
            ->when($this->sameType, fn ($q) => $q->where('type', $this->type))
            ->where(fn ($q) => $q->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%"))
            ->orderBy('code')->limit(8)->get();
    }

    public function link(int $otherId): void
    {
        $this->validate(['kind' => [Rule::enum(CompatibilityKind::class)], 'compatNote' => ['nullable', 'string', 'max:255']]);
        $c = $this->component() ?? abort(404);
        $other = Auth::user()->components()->findOrFail($otherId);
        $c->linkCompatible($other, CompatibilityKind::from($this->kind), trim($this->compatNote) ?: null);
        $this->reset('compatSearch', 'compatNote');
        $this->dispatch('component-saved');
    }

    public function unlink(int $otherId): void
    {
        $c = $this->component() ?? abort(404);
        $c->unlinkCompatible(Auth::user()->components()->findOrFail($otherId));
        $this->dispatch('component-saved');
    }

    public function render()
    {
        $c = $this->component()?->load(['products', 'parts']);

        return view('livewire.cross-reference.component-editor', [
            'cmp' => $c,
            'compatibles' => $c?->compatibles() ?? collect(),
            // Uyumlu bileşenler üzerinden bu bileşenin yerine kullanılabileceği ek cihazlar
            'reachable' => $c ? Auth::user()->products()->whereIn('id', $c->reachableProductIds())->whereNotIn('id', $c->products->pluck('id'))->get() : collect(),
        ]);
    }
}
