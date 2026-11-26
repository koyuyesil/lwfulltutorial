<?php

namespace App\Livewire\CrossReference;

use App\Enums\ComponentType;
use App\Models\Part;
use App\Services\CrossReferenceCatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Çapraz referans parça düzenleyici (çekmece): tür, kod ve teknik bilgiler;
 * altında bulunduğu cihazlar ve muadiller (Parts\PartRelations).
 */
class PartEditor extends Component
{
    #[Locked]
    public ?int $partId = null;

    public string $type = 'soc';

    public string $code = '';

    public string $name = '';

    public string $manufacturer = '';

    public string $notes = '';

    /** @var list<array{0: string, 1: string}> */
    public array $specs = [];

    #[On('create-ref-part')]
    public function create(?string $type = null): void
    {
        $this->reset();
        $this->resetValidation();
        $this->type = ComponentType::tryFrom((string) $type)?->value ?? 'soc';
        $this->specs = array_map(fn ($k) => [$k, ''], ComponentType::from($this->type)->specKeys());
        $this->dispatch('open-drawer', name: 'part-editor');
    }

    #[On('open-part')]
    public function edit(int $id): void
    {
        $part = Auth::user()->parts()->findOrFail($id);
        $this->reset();
        $this->resetValidation();
        $this->partId = $part->id;
        $this->type = $part->type?->value ?? 'other';
        $this->code = (string) $part->code;
        $this->name = $part->name === $part->code ? '' : (string) $part->name;
        $this->manufacturer = (string) $part->manufacturer;
        $this->notes = (string) $part->notes;
        $this->specs = collect((array) $part->specs)->map(fn ($v, $k) => [(string) $k, (string) $v])->values()->all();
        $this->dispatch('open-drawer', name: 'part-editor');
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

    protected function part(): ?Part
    {
        return $this->partId ? Auth::user()->parts()->find($this->partId) : null;
    }

    public function save(): void
    {
        $this->code = trim($this->code);
        $this->validate([
            'type' => ['required', Rule::enum(ComponentType::class)],
            'code' => ['required', 'string', 'max:120', Rule::unique('parts', 'code')->where('user_id', Auth::id())->where('type', $this->type)->whereNull('deleted_at')->ignore($this->partId)],
            'name' => ['nullable', 'string', 'max:120'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'specs.*.0' => ['nullable', 'string', 'max:40'],
            'specs.*.1' => ['nullable', 'string', 'max:120'],
        ], [
            'code.required' => 'Parça / model kodu zorunludur.',
            'code.unique' => 'Bu türde aynı kodla bir parça zaten var.',
        ]);

        $data = [
            'type' => $this->type,
            'code' => $this->code,
            'name' => trim($this->name) ?: $this->code,
            'manufacturer' => trim($this->manufacturer) ?: null,
            'notes' => trim($this->notes) ?: null,
            'specs' => collect($this->specs)->filter(fn ($s) => trim($s[0] ?? '') !== '' && trim($s[1] ?? '') !== '')
                ->mapWithKeys(fn ($s) => [trim($s[0]) => trim($s[1])])->all() ?: null,
        ];

        if ($part = $this->part()) {
            $part->update($data);
        } else {
            $part = Auth::user()->parts()->create($data + [
                'category' => CrossReferenceCatalog::categoryFor($this->type), 'cost' => 0, 'price' => 0, 'stock' => 0, 'min_stock' => 0,
            ]);
            $this->partId = $part->id;
        }

        $this->dispatch('cross-ref-saved');
        $this->dispatch('toast', message: 'Parça kaydedildi.');
    }

    public function render()
    {
        return view('livewire.cross-reference.part-editor', ['part' => $this->part()]);
    }
}
