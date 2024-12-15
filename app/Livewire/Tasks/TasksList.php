<?php

namespace App\Livewire\Tasks;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TasksList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }

    #[On('task-created')]
    public function render()
    {
        return view('livewire.tasks.tasks-list', [
            'tasks' => Auth::user()->tasks()->paginate(3),
            'tasksByStatus' => Auth::user()->tasks()->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderBy('status', 'desc')
            ->get()
        ]);
    }
}
