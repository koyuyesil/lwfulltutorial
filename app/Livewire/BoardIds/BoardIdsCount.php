<?php

namespace App\Livewire\BoardIds;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class BoardIdsCount extends Component
{
    #[Reactive]
    public $boardIdCount;
    public function render()
    {
        return view('livewire.board-ids.board-ids-count');
    }
}
