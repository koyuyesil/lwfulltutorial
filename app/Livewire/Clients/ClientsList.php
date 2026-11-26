<?php

namespace App\Livewire\Clients;

use App\Enums\TicketStatus;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ClientsList extends Component
{
    use \App\Livewire\Concerns\UndoableDelete;
    use WithPagination;

    #[Url(as: 'ara', except: '')]
    public string $search = '';

    /** '' | corporate | individual | active */
    #[Url(as: 'filtre', except: '')]
    public string $filter = '';

    /** newest | name | devices | tickets */
    #[Url(as: 'sirala', except: 'newest')]
    public string $sort = 'newest';

    /** table | cards */
    #[Url(as: 'gorunum', except: 'table')]
    public string $view = 'table';

    public function placeholder()
    {
        return view('skeleton');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'filter', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'filter', 'sort');
        $this->resetPage();
    }

    public function delete(int $id)
    {
        $this->trashWithUndo(Auth::user()->clients()->findOrFail($id));

        $this->dispatch('client-deleted', id: $id);
    }

    protected function query(): Builder
    {
        $term = trim($this->search);
        $digits = preg_replace('/\D/', '', $term);

        return Auth::user()->clients()->getQuery()
            ->withCount([
                'clientsProducts as devices_count',
                'tickets as tickets_count',
                'tickets as open_tickets_count' => fn ($q) => $q->whereIn('tickets.status', TicketStatus::openValues()),
            ])
            ->with('clientsProducts.product:id,brand,model_name')
            ->when($term !== '', function (Builder $q) use ($term, $digits) {
                $q->where(function (Builder $q) use ($term, $digits) {
                    $like = '%'.$term.'%';
                    $q->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhereRaw(
                            (DB::getDriverName() === 'mysql' ? "CONCAT(first_name, ' ', last_name)" : "(first_name || ' ' || last_name)").' like ?',
                            [$like],
                        )
                        ->orWhere('company', 'like', '%'.Client::trUpper($term).'%')
                        ->orWhere('email', 'like', $like);
                    if (strlen($digits) >= 3) {
                        $q->orWhereRaw(Client::phoneDigitsSql().' like ?', ['%'.$digits.'%']);
                    }
                    // IMEI / seri no ile müşteriyi bul
                    $q->orWhereHas('clientsProducts', fn ($d) => $d->where('imei', 'like', $like)->orWhere('serial', 'like', $like));
                });
            })
            ->when($this->filter === 'corporate', fn ($q) => $q->whereNotNull('company')->where('company', '!=', ''))
            ->when($this->filter === 'individual', fn ($q) => $q->where(fn ($q) => $q->whereNull('company')->orWhere('company', '')))
            ->when($this->filter === 'active', fn ($q) => $q->whereHas('tickets', fn ($t) => $t->whereIn('tickets.status', TicketStatus::openValues())))
            ->when($this->sort === 'name', fn ($q) => $q->orderBy('first_name')->orderBy('last_name'))
            ->when($this->sort === 'devices', fn ($q) => $q->orderByDesc('devices_count'))
            ->when($this->sort === 'tickets', fn ($q) => $q->orderByDesc('tickets_count'))
            ->latest('id');
    }

    #[On('client-saved')]
    public function render()
    {
        $base = fn () => Auth::user()->clients();

        return view('livewire.clients.clients-list', [
            'clients' => $this->query()->paginate($this->view === 'cards' ? 12 : 15),
            'stats' => [
                'total' => $base()->count(),
                'corporate' => $base()->whereNotNull('company')->where('company', '!=', '')->count(),
                'thisMonth' => $base()->where('created_at', '>=', now()->startOfMonth())->count(),
                'active' => $base()->whereHas('tickets', fn ($t) => $t->whereIn('tickets.status', TicketStatus::openValues()))->count(),
            ],
        ]);
    }
}
