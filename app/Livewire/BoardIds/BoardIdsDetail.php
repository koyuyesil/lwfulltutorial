<?php

namespace App\Livewire\BoardIds;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/** Board ID detay paneli: büyük PCB görünümü, gerilim ve bilgiler. */
class BoardIdsDetail extends Component
{
    public ?int $boardId = null;

    #[On('show-board-id')]
    public function load(int $id): void
    {
        $this->boardId = Auth::user()->boardIds()->findOrFail($id)->id;
    }

    #[On('board-id-saved')]
    public function refresh(): void
    {
    }

    #[On('board-id-deleted')]
    public function clear(int $id): void
    {
        if ($this->boardId === $id) {
            $this->boardId = null;
            $this->dispatch('close-drawer', name: 'board-detail');
        }
    }

    public function render()
    {
        return view('livewire.board-ids.board-ids-detail', [
            'board' => $this->boardId ? Auth::user()->boardIds()->with('product')->find($this->boardId) : null,
        ]);
    }
}
