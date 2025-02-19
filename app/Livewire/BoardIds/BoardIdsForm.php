<?php

namespace App\Livewire\BoardIds;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\BoardIdForm;
use App\Models\BoardId;

class BoardIdsForm extends Component
{
    public BoardIdForm $form;

    public function addResistance(){$this->form->addResistance();}
    public function removeResistance($index){$this->form->removeResistance($index);}
    public function save()
    {

        //$this->form->repair_methods = json_encode($this->form->repair_methods);//gpt önerdi çok güzel noktada
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
