<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Servis kaydı')]
class TicketsShow extends Component
{
    #[Locked]
    public int $ticketId;

    public string $note = '';

    public bool $notePublic = false;

    public string $dueAt = '';

    public function mount(Ticket $ticket): void
    {
        abort_unless($ticket->user_id === Auth::id(), 404);

        $this->ticketId = $ticket->id;
        $this->dueAt = $ticket->due_at?->format('Y-m-d\TH:i') ?? '';
    }

    protected function ticket(): Ticket
    {
        return Auth::user()->tickets()->findOrFail($this->ticketId);
    }

    public function moveTo(string $status): void
    {
        $this->validate(['note' => 'nullable|string|max:2000']);

        try {
            $this->ticket()->transitionTo(TicketStatus::from($status), trim($this->note) ?: null, $this->notePublic);
        } catch (InvalidArgumentException $e) {
            $this->addError('transition', $e->getMessage());

            return;
        }

        $this->reset('note', 'notePublic');
    }

    public function addNote(): void
    {
        $this->validate(['note' => 'required|string|min:2|max:2000']);

        $this->ticket()->addNote(trim($this->note), $this->notePublic);

        $this->reset('note', 'notePublic');
    }

    public function updateDueAt(): void
    {
        $this->validate(['dueAt' => 'required|date']);

        $ticket = $this->ticket();
        $old = $ticket->due_at;
        $new = Carbon::parse($this->dueAt);

        $ticket->update(['due_at' => $new]);
        $ticket->addNote(sprintf(
            'Teslim hedefi güncellendi: %s → %s',
            $old?->format('d.m.Y H:i') ?? '—',
            $new->format('d.m.Y H:i'),
        ));
    }

    public function render()
    {
        $ticket = $this->ticket()->load([
            'clientProduct.client',
            'clientProduct.product',
            'events.user',
        ]);

        // Her durumda geçen süreyi olay geçmişinden hesapla
        $durations = [];
        $chronological = $ticket->events
            ->filter(fn ($e) => $e->to_status !== null)
            ->sortBy('id')
            ->values();

        foreach ($chronological as $i => $event) {
            $end = $chronological[$i + 1]->created_at ?? ($ticket->status->isClosed() ? $event->created_at : now());
            $key = $event->to_status->value;
            $durations[$key] = ($durations[$key] ?? 0) + $event->created_at->diffInSeconds($end);
        }

        return view('livewire.tickets.tickets-show', [
            'ticket' => $ticket,
            'durations' => $durations,
            'mainPath' => [
                TicketStatus::RECEIVED,
                TicketStatus::DIAGNOSING,
                TicketStatus::AWAITING_APPROVAL,
                TicketStatus::AWAITING_PARTS,
                TicketStatus::IN_REPAIR,
                TicketStatus::TESTING,
                TicketStatus::READY,
                TicketStatus::DELIVERED,
            ],
        ])->layout('layouts.app');
    }
}
