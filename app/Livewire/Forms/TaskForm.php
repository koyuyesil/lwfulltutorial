<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Rule;
use Livewire\Form;

class TaskForm extends Form
{
    #[Rule('required|min:5')]  // Validation Aspect 
    public string $title='';
    #[Rule('required|min:5')]
    public string $slug='';
    #[Rule('required')]
    public string $description='';
    #[Rule('required')]
    public string $status='';
    #[Rule('required')]
    public string $priority='';
    #[Rule('required')]
    public string $deadline='';

    public function createTask()
    {
        auth()->user()->tasks()->create($this->all());
        session()->flash('message', 'Post successfully updated.');
        //request()->session()->flash('succes',__('Task created successfully'));
    }
}
