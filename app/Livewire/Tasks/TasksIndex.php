<?php

namespace App\Livewire\Tasks;

use Livewire\Component;
use App\Enums\PriotyType;
use App\Enums\StatusType;
use Livewire\Attributes\Rule;

class TasksIndex extends Component
{
    #[Rule('required|min:5')]//validate ile kullanılır.
    public string $title;
    #[Rule('required|min:5')]
    public string $slug;
    #[Rule('required')]
    public string $description ;
    #[Rule('required')]
    public string $status;
    #[Rule('required')]
    public string $priority;
    #[Rule('required')]
    public string $deadline;

    public array $statusTypes = [];
    public array $priorityTypes = [];
    
    public function mount()
    {
        // Enumları bileşen yüklenirken alıp, özelliklere atıyoruz
        $this->statusTypes = StatusType::cases();
        $this->priorityTypes = PriotyType::cases();
    }
    public function save()
    {

        $this->validate();//rule ile kullanılır
        auth()->user()->tasks()->create($this->only(['title','slug','description','status','priority','deadline']));

        $this->reset();

        // $this->dispatch('task-saved');
    }
    

    public function render()
    {
        //return view('livewire.tasks.tasks-index');
        return view('livewire.tasks.tasks-index', [
            'statusTypes' => $this->statusTypes,
            'priorityTypes' => $this->priorityTypes,
        ])->layout('layouts.app');//fullpage diye farklı dizinde ara diye


        // Enumları doğrudan view'a gönderiyoruz. NOT namespaceler eklenecek
        // return view('livewire.tasks.tasks-index', [
        //     'statusTypes' => StatusType::cases(),
        //     'priorityTypes' => PriotyType::cases(),
        // ]);



    }
}
