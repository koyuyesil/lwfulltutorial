<?php

namespace App\Livewire\Tasks;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Tasks')]
class TasksIndex extends Component
{
    public function render()
    {
        return view('livewire.tasks.tasks-index')->layout('layouts.app');
    }
}
