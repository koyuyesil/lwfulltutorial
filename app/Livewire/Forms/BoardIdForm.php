<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\BoardId;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class BoardIdForm extends Form
{

    public $editMode = false;
    public ?BoardId $boardId;

    #[Validate('required|min:5')]
    public ?string $build_name;

    #[Validate('required')]
    public ?int $product_id = 1;//TODO

    public function setContent(BoardId $boardId)
    {
        $this->editMode = true;
        $this->boardId = $boardId;
        $this->build_name = $boardId->build_name;
    }

    public function store()
    {
        if ($this->editMode) {
            $this->boardId->update($this->all());
            $this->reset();
            session()->flash('message', 'Form successfully updated.');
        } else {
            Auth::user()->boardIds()->create($this->all());
            session()->flash('message', 'Form successfully created.');
            //request()->session()->flash('succes',__('Task created successfully'));
            $this->reset();
        }
    }
}
