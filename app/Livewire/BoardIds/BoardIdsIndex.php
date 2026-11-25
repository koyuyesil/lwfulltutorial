<?php

namespace App\Livewire\BoardIds;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Board ID kütüphanesi')]
class BoardIdsIndex extends Component
{
    public function render()
    {
        return view('livewire.board-ids.board-ids-index')->layout('layouts.app');
    }
}
