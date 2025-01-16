<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\BoardId;
use App\Enums\RepairMethod;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class BoardIdForm extends Form
{

    public $editMode = false;
    public ?BoardId $boardId;

    #[Validate('required')]
    public ?int $product_id = 1;//TODO bu ilişkili tablo keyi

    #[Validate('required|min:5')]
    public ?string $build_name;

    #[Validate('required')]
    public ?string $mass_production_hwid;

    #[Validate('required')]
    public ?string $pre_production_hwid;

    #[Validate('required')]
    public $repair_methods=[];

    #[Validate('required')]
    public ?string $resistances;

    #[Validate('required')]
    public ?string $description;

    public function setContent(BoardId $boardId)
    {
        $this->editMode = true;
        $this->boardId = $boardId;

        $this->build_name = $boardId->build_name;
        $this->mass_production_hwid = $boardId->mass_production_hwid;
        $this->pre_production_hwid = $boardId->pre_production_hwid;
        $this->repair_methods = $boardId->repair_methods;
        $this->resistances = $boardId->resistances;
        $this->description = $boardId->description;

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
