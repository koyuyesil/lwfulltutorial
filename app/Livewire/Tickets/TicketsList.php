<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Enums\StatusType;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TicketsList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton'); // Lazy loading
    }

    public function changeStatus(int $id, string $status)
    {
        Auth::user()->tickets()->findOrFail($id)
            ->update(['status' => StatusType::from($status)]);
    }

    public function delete(int $id)
    {
        Auth::user()->tickets()->findOrFail($id)->delete();
    }

    #[On('ticket-created')]
    public function render()
    {
        // Ticket'ları, müşteri, cihaz ve cihaz bilgisi ile alıyoruz
        $tickets = Auth::user()->tickets()->with([
            'clientProduct.product', // Cihaza ait bilgiler
            'clientProduct.client', // Müşteri bilgileri
        ])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
        //dd($tickets);

        return view('livewire.tickets.tickets-list', [
            // 'clients' => $clients, // İlişkili verileri eager loading ile alıyoruz.
            'tickets' => $tickets, // İlişkili verileri eager loading ile alıyoruz
            'ticketsByStatus' => Auth::user()->tickets()->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->orderBy('status', 'desc')
                ->get()
        ]);
    }
}
