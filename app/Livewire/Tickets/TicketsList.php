<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Ticket;
use App\Models\Customer;
use App\Models\Device;

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
        // // Müşterileri ve her bir cihazı ve her cihazın ticket'larını alıyoruz
        // $client = Client::with([
        //     'clientProduct.product', // Cihaza ait bilgiler
        //     'clientProduct.client', // Müşteri bilgileri
        // ])->paginate(3);

        // Ticket'ları, müşteri, cihaz ve cihaz bilgisi ile alıyoruz
        $tickets = Ticket::with([
            'clientProduct.product', // Cihaza ait bilgiler
            'clientProduct.client', // Müşteri bilgileri
        ])
        ->orderBy('updated_at', 'desc')
        ->paginate(10);

        return view('livewire.tickets.tickets-list', [
            // 'customers' => $customers, // İlişkili verileri eager loading ile alıyoruz.
            'tickets' => $tickets, // İlişkili verileri eager loading ile alıyoruz
        ]);
    }
}
