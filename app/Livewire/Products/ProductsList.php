<?php

namespace App\Livewire\Products;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsList extends Component
{
    use \App\Livewire\Concerns\UndoableDelete;
    use WithPagination;

    #[Url(as: 'ara', except: '')]
    public string $search = '';

    #[Url(as: 'marka', except: '')]
    public string $brand = '';

    /** newest | brand | popular */
    #[Url(as: 'sirala', except: 'newest')]
    public string $sort = 'newest';

    /** cards | table */
    #[Url(as: 'gorunum', except: 'cards')]
    public string $view = 'cards';

    public function mount(): void
    {
        // Adreste görünüm yoksa firmanın varsayılan tercihi kullanılır
        if (! request()->has('gorunum')) {
            $this->view = (string) Auth::user()->setting('products.default_view');
        }
    }

    public function placeholder()
    {
        return view('skeleton');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'brand', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'brand', 'sort');
        $this->resetPage();
    }

    public function delete(int $id)
    {
        $this->trashWithUndo(Auth::user()->products()->findOrFail($id));
    }

    protected function query(): Builder
    {
        $term = trim($this->search);

        return Auth::user()->products()->getQuery()
            ->withCount([
                'clientProducts as devices_count',
                'tickets as tickets_count',
                'tickets as open_tickets_count' => fn ($q) => $q->whereIn('tickets.status', TicketStatus::openValues()),
                'boardIds as board_ids_count',
            ])
            ->when($term !== '', fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
                foreach (['brand', 'model_name', 'model_number', 'codename', 'chipset', 'manufacturer', 'description'] as $column) {
                    $q->orWhere($column, 'like', '%'.$term.'%');
                }
            }))
            ->when($this->brand !== '', fn ($q) => $q->where('brand', $this->brand))
            ->when($this->sort === 'brand', fn ($q) => $q->orderBy('brand')->orderBy('model_name'))
            ->when($this->sort === 'popular', fn ($q) => $q->orderByDesc('tickets_count'))
            ->latest('id');
    }

    #[On('product-saved')]
    public function render()
    {
        $base = fn () => Auth::user()->products();

        $brands = $base()->selectRaw('brand, COUNT(*) as c')->groupBy('brand')->orderByDesc('c')->pluck('c', 'brand');
        $top = $base()->withCount('tickets')->orderByDesc('tickets_count')->first();

        return view('livewire.products.products-list', [
            'products' => $this->query()->paginate($this->view === 'cards' ? 12 : 20),
            'brands' => $brands,
            'stats' => [
                'total' => $brands->sum(),
                'brands' => $brands->count(),
                'devices' => Auth::user()->clientsProducts()->count(),
                'top' => $top && $top->tickets_count ? $top : null,
            ],
        ]);
    }
}
