<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Tickets')]
class TicketsIndex extends Component
{
    public function render()
    {
        return view('livewire.tickets.tickets-index')->layout('layouts.app');
    }
}
