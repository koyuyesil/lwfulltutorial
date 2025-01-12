<?php

namespace App\Livewire\Tasks;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class TasksCount extends Component
{
    #[Reactive]
    public $tasksByStatus;
    public function render()
    {
        return view('livewire.tasks.tasks-count');
    }
}
