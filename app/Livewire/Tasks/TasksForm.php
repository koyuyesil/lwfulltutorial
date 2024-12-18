<?php

namespace App\Livewire\Tasks;

use App\Livewire\Forms\TaskForm;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Task;

class TasksForm extends Component
{
    public TaskForm $form;

    public function save()
    {
        $this->form->validate();
        $this->form->createTask();
        //$this->redirect('tasks',navigate: false); //false wiewde navlar ayarlı ise istekler azaltılır
        $this->dispatch('task-created');// bunun yerine parent save yada redirect yapılabilir
    }

    #[On('edit-task')]//bu event sadece formu doldurur
    public function editTask($id)
    {
        $task = Task::findOrFail($id);
        $this->form->setTask($task);
    }
    public function render()
    {
        return view('livewire.tasks.tasks-form');
    }
}
