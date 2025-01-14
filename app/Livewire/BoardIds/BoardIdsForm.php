<?php

namespace App\Livewire\BoardIds;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\BoardIdForm;
use App\Models\BoardId;

class BoardIdsForm extends Component
{
    public BoardIdForm $form;

    public function save()
    {
        $this->form->validate();
        $this->form->store();

        $this->dispatch('created');
    }
    #[On('edit')]//bu event sadece formu doldurur
    public function edit($id)
    {
        $boardId = BoardId::findOrFail($id);
        $this->form->setContent($boardId);
    }

    public function render()
    {
        return view('livewire.board-ids.board-ids-form');
    }
}
