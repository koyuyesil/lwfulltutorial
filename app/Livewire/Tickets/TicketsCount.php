<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class TicketsCount extends Component
{
    #[Reactive]
    public $ticketsByStatus;
    public function render()
    {
        return view('livewire.tickets.tickets-count');
    }
}
