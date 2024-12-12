<?php

namespace App\Livewire\Tasks;

use Livewire\Component;

use App\Models\Task;

use Illuminate\Support\Facades\Auth;

class TasksIndex extends Component
{
    //nesting yapıldı incele
    public function render()
    {
        return view('livewire.tasks.tasks-index')->layout('layouts.app');
    }
}
