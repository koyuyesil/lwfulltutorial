<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class Navigation extends Component
{
    //public $name = 'Merhaba';
    public function render()
    {
        return view('livewire.auth.navigation') -> with([
            "name"=>"Merhaba"
        ]);
    }
}
