<?php

namespace App\Livewire\Tickets;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Servis kayıtları')]
class TicketsIndex extends Component
{
    /** ?yeni=1 ile gelindiyse (üst çubuk, müşteri kartı) kayıt paneli açık başlar. */
    public bool $openNew = false;

    public function mount(): void
    {
        $this->openNew = request()->boolean('yeni');
    }

    public function render()
    {
        return view('livewire.tickets.tickets-index')->layout('layouts.app');
    }
}
