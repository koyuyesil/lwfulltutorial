<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;

class Search extends Component
{
    // bu aspect yazıldığı değişkenin urlden de alınabileceğini ifade eder.
    //yani şpost da aynı değişkeni arar varsa buna eşitler.
    #[Url]
    public $search;
    public function render()
    {
        $results = [];

        if (strlen($this->search) > 2) {
            $results = auth()->user()->tasks()
                ->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%')
                ->get();
        }

        return view('livewire.search', compact('results'));
    }
}
