<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\TaskForm;

class TasksForm extends Component
{
    public TaskForm $form;

    public function save()
    {
        $this->form->validate();
        $this->form->createTask();
        //$this->redirect('tasks',navigate: false); //false wiewde navlar ayarlı ise istekler azaltılır
        $this->dispatch('task-created');// bunun yerine parent(yani index).save yada redirect yapılabilir
    }

    #[On('edit-task')]//bu event sadece formu doldurur
    public function editTask($id)
    {
        $task = auth()->user()->tasks()->findOrFail($id);
        $this->form->setTask($task);
    }
    public function render()
    {
        return view('livewire.tasks.tasks-form');
    }
}
