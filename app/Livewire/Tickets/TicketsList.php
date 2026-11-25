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
    use WithPagination;

    /** '' | 'open' | 'closed' | bir TicketStatus değeri */
    #[Url(as: 'durum')]
    public string $statusFilter = 'open';

    /** '' | 'overdue' | 'at_risk' */
    #[Url(as: 'sla')]
    public string $slaFilter = '';

    public function placeholder()
    {
        return view('skeleton'); // Lazy loading
    }

    public function updated($property): void
    {
        if (in_array($property, ['statusFilter', 'slaFilter'], true)) {
            $this->resetPage();
        }
    }

    public function changeStatus(int $id, string $status)
    {
        $ticket = Auth::user()->tickets()->findOrFail($id);

        try {
            $ticket->transitionTo(TicketStatus::from($status));
        } catch (InvalidArgumentException $e) {
            $this->addError('status.'.$id, $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        Auth::user()->tickets()->findOrFail($id)->delete();
    }

    #[On('ticket-created')]
    public function render()
    {
        $base = Auth::user()->tickets();

        $tickets = (clone $base)
            ->with(['clientProduct.product', 'clientProduct.client'])
            ->when($this->statusFilter === 'open', fn ($q) => $q->open())
            ->when($this->statusFilter === 'closed', fn ($q) => $q->whereNotIn('status', TicketStatus::openValues()))
            ->when(TicketStatus::tryFrom($this->statusFilter), fn ($q, $status) => $q->where('status', $status))
            ->when($this->slaFilter === 'overdue', fn ($q) => $q->overdue())
            ->when($this->slaFilter === 'at_risk', fn ($q) => $q->atRisk())
            // Açık işlerde en acil hedef önce; kapanmışlarda en yeni önce
            ->orderByRaw('CASE WHEN closed_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('due_at')
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.tickets.tickets-list', [
            'tickets' => $tickets,
            'countsByStatus' => (clone $base)
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'overdueCount' => (clone $base)->overdue()->count(),
            'atRiskCount' => (clone $base)->atRisk()->count(),
        ]);
    }
}
