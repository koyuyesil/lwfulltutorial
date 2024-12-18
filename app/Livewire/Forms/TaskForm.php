<?php

namespace App\Livewire\Forms;

use App\Enums\PriorityType;
use App\Enums\StatusType;
use Livewire\Attributes\Rule;
use Livewire\Form;
use App\Models\Task;

class TaskForm extends Form
{
    public $editMode = false;
    public ?Task $task;
    // Validation Attiribute or Aspect
    #[Rule('required|min:5')]
    public $title;                      //tip güvenli olmadan yazılmış null hatası vermez.
    #[Rule('required|min:5')]
    public ?string $slug;               //tip güvenli ama ? ile işaretli yani null hatasını atlar.
    #[Rule('required')]
    public string $description = '';    //tip güvenli ilk atamayla kalıcı çözüm.
    #[Rule('required')]
    public StatusType $status = StatusType::OPEN;
    #[Rule('required')]
    public PriorityType $priority = PriorityType::LOW;
    #[Rule('required')]
    public string $deadline = '';



    public function setTask(Task $task)
    {
        $this->editMode = true;
        $this->task = $task;
        $this->title = $task->title;
        $this->slug = $task->slug;
        $this->description = $task->description;
        $this->status = $task->status;
        $this->priority = $task->priority;
        $this->deadline = $task->deadline->format('Y-m-d');
    }
    public function createTask()
    {
        if ($this->editMode) {
            $this->task->update($this->all());
            $this->reset();
            session()->flash('message', 'Post successfully updated.');
        } else {
            auth()->user()->tasks()->create($this->all());
            session()->flash('message', 'Post successfully created.');
            //request()->session()->flash('succes',__('Task created successfully'));
            $this->reset();
        }
    }
}
