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
        // $customers = Customer::with([
        //     'customerDevices.device',  // Cihaz ve cihazın bilgileri
        //     'customerDevices.ticket'  // Cihaza ait ticket'lar (servis)
        // ])->paginate(3);

        // Ticket'ları, müşteri, cihaz ve cihaz bilgisi ile alıyoruz
        $tickets = Ticket::with([
            'customerDevice.device', // Cihaza ait bilgiler
            'customerDevice.customer', // Müşteri bilgileri
        ])
        ->orderBy('updated_at', 'desc')
        ->paginate(10);

        return view('livewire.tickets.tickets-list', [
            // 'customers' => $customers, // İlişkili verileri eager loading ile alıyoruz.
            'tickets' => $tickets, // İlişkili verileri eager loading ile alıyoruz
        ]);
    }
}
