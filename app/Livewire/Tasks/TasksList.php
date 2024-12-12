<?php

namespace App\Livewire\Tasks;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class TasksList extends Component
{

    #[On('task-created')]
    public function render()
    {
        return view('livewire.tasks.tasks-list', [
            'tasks' => Auth::user()->tasks()->paginate(3),
        ]);
    }
}
