<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Kanban iş panosu: açık işler durum sütunlarında. Kartı sürükleyip başka sütuna bırakınca durum değişir
 * (yalnızca iş akışının izin verdiği geçişler), teknisyen çipine bırakınca o teknisyene atanır.
 */
#[Title('İş panosu')]
class TicketsBoard extends Component
{
    public const COLUMNS = [
        TicketStatus::RECEIVED, TicketStatus::DIAGNOSING, TicketStatus::AWAITING_APPROVAL, TicketStatus::AWAITING_PARTS,
        TicketStatus::IN_REPAIR, TicketStatus::TESTING, TicketStatus::READY,
    ];

    #[Url(as: 'teknisyen', except: '')]
    public string $technician = ''; // '' tümü | '-' atanmamış | ad

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function move(int $id, string $status): void
    {
        $ticket = Auth::user()->tickets()->findOrFail($id);
        $to = TicketStatus::tryFrom($status) ?? abort(422);

        try {
            $ticket->transitionTo($to);
        } catch (InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return;
        }
        $this->dispatch('toast', message: "{$ticket->tracking_code} → {$to->label()}");
    }

    public function assign(int $id, string $name): void
    {
        $ticket = Auth::user()->tickets()->findOrFail($id);
        $name = trim($name);
        abort_unless($name === '' || in_array($name, Auth::user()->technicians(), true), 422);

        $ticket->update(['technician' => $name ?: null]);
        $ticket->addNote($name ? 'Teknisyen atandı: '.$name : 'Teknisyen ataması kaldırıldı.');
        $this->dispatch('toast', message: $name ? "{$ticket->tracking_code} → {$name}" : 'Atama kaldırıldı.');
    }

    public function render()
    {
        $user = Auth::user();
        $open = $user->tickets()->open()
            ->with(['clientProduct.client', 'clientProduct.product'])
            ->withCount('photos')
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(fn ($q) => $q->where('tracking_code', 'like', $term)->orWhere('problem', 'like', $term)
                    ->orWhereHas('clientProduct.client', fn ($c) => $c->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term))
                    ->orWhereHas('clientProduct.product', fn ($p) => $p->where('model_name', 'like', $term)->orWhere('brand', 'like', $term)));
            })
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')->orderBy('due_at')
            ->get();

        $technicians = $user->technicians();
        $workload = collect($technicians)->mapWithKeys(fn ($t) => [$t => $open->where('technician', $t)->count()])->all();
        $unassigned = $open->whereNull('technician')->count();

        $filtered = $open->when($this->technician === '-', fn ($c) => $c->whereNull('technician'))
            ->when(! in_array($this->technician, ['', '-'], true), fn ($c) => $c->where('technician', $this->technician));

        return view('livewire.tickets.tickets-board', [
            'columns' => collect(self::COLUMNS)->mapWithKeys(fn (TicketStatus $s) => [$s->value => [
                'status' => $s,
                'tickets' => $filtered->filter(fn (Ticket $t) => $t->status === $s)->values(),
            ]]),
            'transitions' => collect(TicketStatus::cases())->mapWithKeys(fn (TicketStatus $s) => [$s->value => array_map(fn ($t) => $t->value, $s->allowedTransitions())]),
            'technicians' => $technicians,
            'workload' => $workload,
            'unassigned' => $unassigned,
            'total' => $open->count(),
        ])->layout('layouts.app');
    }
}
