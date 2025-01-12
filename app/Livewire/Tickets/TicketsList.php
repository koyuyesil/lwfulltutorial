<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Ticket;


class TicketsList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton'); // Lazy loading
    }

    public function delete(Ticket $ticket)
    {
        $ticket->delete(); // Ticket silme işlemi
    }

    #[On('ticket-created')]
    public function render()
    {
        // Ticket'ları, müşteri, cihaz ve cihaz bilgisi ile alıyoruz
        $tickets = auth()->user()->tickets()->with([
            'clientProduct.product', // Cihaza ait bilgiler
            'clientProduct.client', // Müşteri bilgileri
        ])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        //dd($tickets);

        return view('livewire.tickets.tickets-list', [
            // 'clients' => $clients, // İlişkili verileri eager loading ile alıyoruz.
            'tickets' => $tickets, // İlişkili verileri eager loading ile alıyoruz
        ]);
    }
}
