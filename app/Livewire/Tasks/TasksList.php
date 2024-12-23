<?php

namespace App\Livewire\Tasks;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TasksList extends Component
{
    use WithPagination; //config/livewire.php den sayfa sayısını ayarlayabilirsin env ye de aktarailirsin
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }


    public function changeStatus($id, $status)
    {
        $task = Task::find($id);
        $task->update(['status' => $status]);
    }

    public function delete(Task $task)
    {
        $task->delete();
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
