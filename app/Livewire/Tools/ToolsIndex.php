<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Tools')]
class ToolsIndex extends Component
{
    public function render()
    {
        return view('livewire.tools.tools-index')->layout('layouts.app');
    }
}
