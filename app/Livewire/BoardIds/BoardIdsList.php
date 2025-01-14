<?php

namespace App\Livewire\BoardIds;

use App\Models\BoardId;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BoardIdsList extends Component
{
    use WithPagination;
    public function placeholder()
    {
        return view('skeleton');//lazzy loading
    }
    public function delete(BoardId $boardId)
    {
        $boardId->delete();
    }

    #[On('created')]
    public function render()
    {

         $boardIds = Auth::user()->boardIds()->with('product')->paginate(10); // Pagination ekleniyor
         $boardIdCount=Auth::user()->boardIds()->select('product', DB::raw('COUNT(*) as count'))
         ->get();
        return view('livewire.board-ids.board-ids-list', [
             'boardIds' => $boardIds,  // Eager loading kullanarak ilişkili cihazları alıyoruz.
             'boardIdCount' => $boardIdCount,
         ]);
    }
}
