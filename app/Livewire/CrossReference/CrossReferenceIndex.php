<?php

namespace App\Livewire\CrossReference;

use App\Enums\ComponentType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Çapraz referans (Premium): cihaz ↔ bileşen ↔ uyumlu bileşen.
 * "Bu RAM hangi telefonlarda var, yerine ne takılır?" ve "Bu cihazda hangi SoC / PMIC var?" sorularının cevabı.
 */
#[Title('Çapraz referans')]
class CrossReferenceIndex extends Component
{
    use WithPagination;

    #[Url(as: 'gorunum', except: 'components')]
    public string $view = 'components'; // components | devices

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'tur', except: '')]
    public string $type = '';

    /** Dışa aktarma için seçilen satırlar (görünüme göre bileşen ya da cihaz kimlikleri) */
    public array $selected = [];

    public function updating($name): void
    {
        if (in_array($name, ['search', 'type', 'view'], true)) {
            $this->resetPage();
        }
        if ($name === 'view') {
            $this->selected = [];
        }
    }

    #[On('component-saved')]
    public function refreshList(): void
    {
    }

    public function delete(int $id): void
    {
        Auth::user()->components()->findOrFail($id)->delete();
        $this->selected = array_values(array_diff($this->selected, [$id]));
        $this->dispatch('toast', message: 'Bileşen silindi.');
    }

    /** Sayfadaki tüm satırları seç / bırak */
    public function togglePage(array $ids): void
    {
        $ids = array_map('intval', $ids);
        $this->selected = array_diff($ids, $this->selected) ? array_values(array_unique([...$this->selected, ...$ids])) : array_values(array_diff($this->selected, $ids));
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function render()
    {
        $user = Auth::user();
        $term = trim($this->search);
        $type = ComponentType::tryFrom($this->type);

        $components = $this->view === 'components'
            ? $user->components()
                ->when($type, fn ($q) => $q->where('type', $type->value))
                ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%")->orWhere('manufacturer', 'like', "%$term%")
                    ->orWhereHas('products', fn ($p) => $p->where('model_name', 'like', "%$term%")->orWhere('codename', 'like', "%$term%")->orWhere('brand', 'like', "%$term%"))))
                ->with(['products:id,brand,model_name,codename', 'compatibleTo:id,code,type', 'compatibleFrom:id,code,type'])
                ->withCount(['products', 'parts'])
                ->orderBy('type')->orderBy('code')
                ->paginate(30)
            : null;

        $devices = $this->view === 'devices'
            ? $user->products()
                ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('model_name', 'like', "%$term%")->orWhere('brand', 'like', "%$term%")->orWhere('codename', 'like', "%$term%")->orWhere('model_number', 'like', "%$term%")
                    ->orWhereHas('components', fn ($c) => $c->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%"))))
                ->when($type, fn ($q) => $q->whereHas('components', fn ($c) => $c->where('type', $type->value)))
                ->with(['components' => fn ($q) => $q->orderBy('type')])
                ->withCount('components')
                ->orderByDesc('components_count')->orderBy('brand')->orderBy('model_name')
                ->paginate(24)
            : null;

        return view('livewire.cross-reference.cross-reference-index', [
            'components' => $components,
            'devices' => $devices,
            'counts' => [
                'components' => $user->components()->count(),
                'devices' => $user->products()->has('components')->count(),
                'compat' => \Illuminate\Support\Facades\DB::table('component_compatibilities')
                    ->whereIn('component_id', $user->components()->select('id'))->count(),
            ],
            'typeCounts' => $user->components()->selectRaw('type, count(*) as n')->groupBy('type')->pluck('n', 'type'),
        ])->layout('layouts.app');
    }
}
