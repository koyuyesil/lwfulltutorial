<?php

namespace App\Livewire\CrossReference;

use App\Enums\ComponentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Çapraz referans: cihaz → parça → muadil parça.
 * "Bu telefonda hangi SoC / RAM / PMIC var?" ve "Bu parça hangi telefonlarda var, yerine ne takılır?"
 * Parçalar parça tablosundadır; cihaz ve muadil ilişkileri pivot tablolarda.
 */
#[Title('Çapraz referans')]
class CrossReferenceIndex extends Component
{
    use \App\Livewire\Concerns\UndoableDelete;
    use WithPagination;

    #[Url(as: 'gorunum', except: 'devices')]
    public string $view = 'devices'; // devices | parts

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'tur', except: '')]
    public string $type = '';

    /** Dışa aktarma için seçilen satırlar (görünüme göre cihaz ya da parça kimlikleri) */
    public array $selected = [];

    public function mount(): void
    {
        if (! in_array($this->view, ['devices', 'parts'], true)) {
            $this->view = 'devices';
        }
    }

    public function updating($name): void
    {
        if (in_array($name, ['search', 'type', 'view'], true)) {
            $this->resetPage();
        }
        if ($name === 'view') {
            $this->selected = [];
        }
    }

    #[On('cross-ref-saved')]
    public function refreshList(): void {}

    public function delete(int $id): void
    {
        $this->trashWithUndo(Auth::user()->parts()->findOrFail($id));
        $this->selected = array_values(array_diff($this->selected, [$id]));
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

        $parts = $this->view === 'parts'
            ? $user->parts()->reference()
                ->when($type, fn ($q) => $q->where('type', $type->value))
                ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%")->orWhere('manufacturer', 'like', "%$term%")->orWhere('sku', 'like', "%$term%")
                    ->orWhereHas('products', fn ($p) => $p->where('model_name', 'like', "%$term%")->orWhere('codename', 'like', "%$term%")->orWhere('brand', 'like', "%$term%"))))
                ->with(['products:id,brand,model_name,codename', 'compatibleTo:id,code,name,type', 'compatibleFrom:id,code,name,type'])
                ->withCount('products')
                ->orderBy('type')->orderBy('code')->orderBy('name')
                ->paginate(30)
            : null;

        $devices = $this->view === 'devices'
            ? $user->products()
                ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('model_name', 'like', "%$term%")->orWhere('brand', 'like', "%$term%")->orWhere('codename', 'like', "%$term%")->orWhere('model_number', 'like', "%$term%")
                    ->orWhereHas('parts', fn ($c) => $c->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%"))))
                ->when($type, fn ($q) => $q->whereHas('parts', fn ($c) => $c->where('type', $type->value)))
                ->with(['parts' => fn ($q) => $q->orderBy('type')->orderBy('code')])
                ->withCount('parts')
                ->orderByDesc('parts_count')->orderBy('brand')->orderBy('model_name')
                ->paginate(24)
            : null;

        $refIds = $user->parts()->reference()->select('id');

        return view('livewire.cross-reference.cross-reference-index', [
            'parts' => $parts,
            'devices' => $devices,
            'counts' => [
                'parts' => $user->parts()->reference()->count(),
                'devices' => $user->products()->has('parts')->count(),
                'compat' => DB::table('part_compatibilities')->whereIn('part_id', $refIds)->count(),
            ],
            'typeCounts' => $user->parts()->whereNotNull('type')->selectRaw('type, count(*) as n')->groupBy('type')->pluck('n', 'type'),
        ])->layout('layouts.app');
    }
}
