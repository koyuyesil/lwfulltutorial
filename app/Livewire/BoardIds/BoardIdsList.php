<?php

namespace App\Livewire\BoardIds;

use App\Enums\DividerType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BoardIdsList extends Component
{
    use \App\Livewire\Concerns\UndoableDelete;
    use WithPagination;

    #[Url(as: 'ara', except: '')]
    public string $search = '';

    /** '' | adc | logic | pullup */
    #[Url(as: 'tip', except: '')]
    public string $type = '';

    public function placeholder()
    {
        return view('skeleton');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'type'], true)) {
            $this->resetPage();
        }
    }

    public function delete(int $id): void
    {
        $this->trashWithUndo(Auth::user()->boardIds()->findOrFail($id));

        $this->dispatch('board-id-deleted', id: $id);
    }

#[On('board-id-saved')]
    public function render()
    {
        $term = trim($this->search);

        $boards = Auth::user()->boardIds()
            ->with('product:id,brand,model_name,model_number,codename,image')
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('build_name', 'like', "%$term%")
                ->orWhere('mass_production_hwid', 'like', "%$term%")
                ->orWhere('pre_production_hwid', 'like', "%$term%")
                ->orWhereHas('product', fn ($p) => $p->where('brand', 'like', "%$term%")
                    ->orWhere('model_name', 'like', "%$term%")
                    ->orWhere('codename', 'like', "%$term%"))))
            ->when(DividerType::tryFrom($this->type), fn ($q, $t) => $q->where('divider_type', $t))
            ->latest('id')
            ->paginate(12);


        return view('livewire.board-ids.board-ids-list', [
            'boards' => $boards,
            'typeCounts' => Auth::user()->boardIds()->selectRaw('divider_type, COUNT(*) as c')->groupBy('divider_type')->pluck('c', 'divider_type'),
        ]);
    }
}
