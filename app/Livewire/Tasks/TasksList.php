<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Enums\StatusType;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TasksList extends Component
{
    use WithPagination; //config/livewire.php den sayfa sayısını ayarlayabilirsin env ye de aktarailirsin
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }

    public function changeStatus(int $id, string $status)
    {
        Auth::user()->tasks()->findOrFail($id)
            ->update(['status' => StatusType::from($status)]);
    }

    public function delete(int $id)
    {
        Auth::user()->tasks()->findOrFail($id)->delete();
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
