<?php

namespace App\Livewire\Tasks;

use Livewire\Component;

class Show extends Component
{
    public function render()
    {
        //return view('livewire.tasks.show');
        return view('livewire.tasks.show')->layout('layouts.app');//fullpage diye farklı dizinde ara diye
    }
}
