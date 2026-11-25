<?php

namespace App\Livewire\BoardIds;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class BoardIdsList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }
    public function delete(int $id)
    {
        // Sadece oturumdaki kullanıcının kaydı silinebilir
        Auth::user()->boardIds()->findOrFail($id)->delete();
    }

    #[On('created')]
    public function render()
    {
        return view('livewire.board-ids.board-ids-list', [
            'boardIds' => Auth::user()->boardIds()->with('product')->latest()->paginate(10),
        ]);
    }
}
