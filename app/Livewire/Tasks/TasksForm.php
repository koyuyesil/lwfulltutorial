<?php

namespace App\Livewire\Tasks;

use App\Livewire\Forms\TaskForm;
use Livewire\Component;

class TasksForm extends Component
{
    public TaskForm $form;

    public function save()
    {
        $this->form->validate();
        $this->form->createTask();
        //$this->redirect('tasks',navigate: false); //false wiewde navlar ayarlı ise istekler azaltılır
        $this->dispatch('task-created');// bunun yerine parent save yada redirect yapılabilir
        $this->form->reset();
    }
    public function render()
    {
        return view('livewire.tasks.tasks-form');
    }
}
