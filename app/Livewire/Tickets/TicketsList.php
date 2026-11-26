<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketsList extends Component
{
    use \App\Livewire\Concerns\UndoableDelete;
    use WithPagination;

    /** '' | 'open' | 'closed' | bir TicketStatus değeri */
    #[Url(as: 'durum')]
    public string $statusFilter = 'open';

    /** '' | 'overdue' | 'at_risk' */
    #[Url(as: 'sla')]
    public string $slaFilter = '';

    #[Url(as: 'ara', except: '')]
    public string $search = '';

    /** table | cards */
    #[Url(as: 'gorunum', except: 'table')]
    public string $view = 'table';

    public function placeholder()
    {
        return view('skeleton'); // Lazy loading
    }

    public function updated($property): void
    {
        if (in_array($property, ['statusFilter', 'slaFilter', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->statusFilter = 'open';
        $this->reset('slaFilter', 'search');
        $this->resetPage();
    }

    protected function applySearch($query)
    {
        $term = trim($this->search);
        if ($term === '') {
            return $query;
        }
        $like = '%'.$term.'%';
        $digits = preg_replace('/\D/', '', $term);

        return $query->where(function ($q) use ($term, $like, $digits) {
            $q->where('tracking_code', 'like', '%'.strtoupper(preg_replace('/[^A-Za-z0-9-]/', '', $term)).'%')
                ->orWhere('problem', 'like', $like)
                ->orWhereHas('clientProduct', function ($d) use ($like, $digits, $term) {
                    $d->where('imei', 'like', $like)
                        ->orWhere('serial', 'like', $like)
                        ->orWhereHas('product', fn ($p) => $p->where('brand', 'like', $like)->orWhere('model_name', 'like', $like))
                        ->orWhereHas('client', function ($c) use ($like, $digits, $term) {
                            $c->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like)
                                ->orWhere('company', 'like', '%'.\App\Models\Client::trUpper($term).'%');
                            if (strlen($digits) >= 3) {
                                $c->orWhereRaw(\App\Models\Client::phoneDigitsSql().' like ?', ['%'.$digits.'%']);
                            }
                        });
                });
        });
    }

    public function changeStatus(int $id, string $status)
    {
        $ticket = Auth::user()->tickets()->findOrFail($id);

        try {
            $ticket->transitionTo(TicketStatus::from($status));
            $this->dispatch('toast', message: "{$ticket->tracking_code} → {$ticket->status->label()}");
        } catch (InvalidArgumentException $e) {
            $this->addError('status.'.$id, $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        $ticket = Auth::user()->tickets()->findOrFail($id);
        $this->trashWithUndo($ticket, $ticket->tracking_code);
    }

    #[On('ticket-created')]
    public function render()
    {
        $base = Auth::user()->tickets();

        // Arama yapılırken varsayılan "açık işler" filtresi uygulanmaz: geçmiş kayıtlar da bulunur
        $status = $this->search !== '' && $this->statusFilter === 'open' ? '' : $this->statusFilter;

        $tickets = $this->applySearch((clone $base)->getQuery())
            ->with(['clientProduct.product', 'clientProduct.client'])
            ->when($status === 'open', fn ($q) => $q->open())
            ->when($status === 'closed', fn ($q) => $q->whereNotIn('status', TicketStatus::openValues()))
            ->when(TicketStatus::tryFrom($status), fn ($q, $s) => $q->where('status', $s))
            ->when($this->slaFilter === 'overdue', fn ($q) => $q->overdue())
            ->when($this->slaFilter === 'at_risk', fn ($q) => $q->atRisk())
            // Açık işlerde en acil hedef önce; kapanmışlarda en yeni önce
            ->orderByRaw('CASE WHEN closed_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('due_at')
            ->latest('updated_at')
            ->paginate($this->view === 'table' ? 20 : 10);

        return view('livewire.tickets.tickets-list', [
            'tickets' => $tickets,
            'countsByStatus' => (clone $base)
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'overdueCount' => (clone $base)->overdue()->count(),
            'atRiskCount' => (clone $base)->atRisk()->count(),
            'searchingAll' => $status !== $this->statusFilter,
        ]);
    }
}
