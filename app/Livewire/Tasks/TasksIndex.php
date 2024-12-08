<?php

namespace App\Livewire\Tasks;

use Livewire\Component;

use App\Livewire\Forms\TaskForm;


class TasksIndex extends Component
{
    public TaskForm $form;

    public function save()
    {
        $this->form->validate(); 

        $this->form->createTask();

        $this->form->reset();

    }


    public function render()
    {
        //return view('livewire.tasks.tasks-index');

        return view('livewire.tasks.tasks-index')->layout('layouts.app');

        // return view('livewire.tasks.tasks-index', [
        //     'form' => $this->form,
        // ])->layout('layouts.app');
        
    }
}
