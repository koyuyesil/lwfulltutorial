<?php

namespace App\Livewire\Tasks;

use App\Livewire\Forms\TaskForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TasksForm extends Component
{
    public TaskForm $form;

    #[On('create-task')]
    public function newTask(?string $template = null, ?string $date = null): void
    {
        $this->form->reset();
        $this->resetValidation();
        $this->form->due_time = (string) Auth::user()->setting('tasks.default_time');
        $this->form->remind_before = (string) Auth::user()->setting('tasks.default_remind');

        if ($template) {
            $this->form->applyTemplate($template);
        } elseif ($date) {
            // Takvimde bir güne tıklanınca o gün için görev açılır
            $this->form->due_date = $date;
        }
    }

    #[On('edit-task')]
    public function editTask($id): void
    {
        $this->resetValidation();
        $this->form->setTask(Auth::user()->tasks()->findOrFail($id));
    }

    public function useTemplate(string $key): void
    {
        $this->form->applyTemplate($key);
        $this->resetValidation();
    }

    #[Computed]
    public function clients()
    {
        return Auth::user()->clients()->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'company']);
    }

    public function save(): void
    {
        $editing = $this->form->isEditing();
        $task = $this->form->save();

        $this->dispatch('task-saved', id: $task->id);
        $this->dispatch('close-drawer', name: 'task-form');
        $this->dispatch('toast', message: $editing ? 'Görev güncellendi.' : "Görev eklendi: {$task->title}");
    }

    public function render()
    {
        return view('livewire.tasks.tasks-form');
    }
}
