<?php

namespace App\Livewire\BoardIds;

use App\Enums\ChipType;
use App\Enums\DividerType;
use App\Livewire\Forms\BoardIdForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class BoardIdsForm extends Component
{
    public BoardIdForm $form;

    #[On('create-board-id')]
    public function newBoard(): void
    {
        $this->form->reset();
        $this->resetValidation();
    }

    #[On('edit-board-id')]
    public function edit($id): void
    {
        $this->resetValidation();
        $this->form->setContent(Auth::user()->boardIds()->findOrFail($id));
    }

    /**
     * Model seçilince build adı, modelin kod adından (Android ürün adı, ör. vili, garnet) doldurulur.
     * Kullanıcı kendisi bir ad yazdıysa üzerine yazılmaz.
     */
    public function updatedFormProductId($value, $old = null): void
    {
        $product = $value ? Auth::user()->products()->find($value) : null;
        if (! $product?->codename) {
            return;
        }

        $codenames = Auth::user()->products()->whereNotNull('codename')->pluck('codename')->all();
        if (trim($this->form->build_name) === '' || in_array($this->form->build_name, $codenames, true)) {
            $this->form->build_name = $product->codename;
        }
    }

    /** Tip seçimi; boş direnç alanları tipin varsayılanıyla doldurulur (ADC: R1 100k, R2 10k). */
    public function setType(string $type): void
    {
        $next = DividerType::tryFrom($type) ?? DividerType::ADC;
        $this->form->divider_type = $next->value;
        $defaults = $next->defaults();

        if ($next === DividerType::LOGIC) {
            // Lojikte tek direnç: ikisi de doluysa HIGH (yalnız R1) varsayılır
            if ($this->form->r1 !== '' && $this->form->r2 !== '') {
                $this->form->r2 = '';
            } elseif ($this->form->r1 === '' && $this->form->r2 === '') {
                $this->form->r1 = $defaults['r1'];
            }

            return;
        }

        $this->form->r1 = $this->form->r1 !== '' ? $this->form->r1 : $defaults['r1'];
        $this->form->r2 = $this->form->r2 !== '' ? $this->form->r2 : $defaults['r2'];
    }

    /** Görseldeki entegre seçimi: PMIC ya da CPU/SoC (üç tipte de). */
    public function setChip(string $chip): void
    {
        $this->form->chip = (ChipType::tryFrom($chip) ?? ChipType::PMIC)->value;
    }

    /** Lojik tipte tek direnç kullanılır: seçime göre karşı direnci temizle. */
    public function setLogicLevel(int $level): void
    {
        if ($level === 1) {
            $this->form->r1 = $this->form->r1 ?: '10k';
            $this->form->r2 = '';
        } else {
            $this->form->r2 = $this->form->r2 ?: '10k';
            $this->form->r1 = '';
        }
    }

    #[Computed]
    public function products()
    {
        return Auth::user()->products()->orderBy('brand')->orderBy('model_name')->get(['id', 'brand', 'model_name', 'model_number', 'codename']);
    }

    public function save(): void
    {
        $editing = $this->form->isEditing();
        $board = $this->form->store();

        $this->dispatch('board-id-saved', id: $board->id);
        $this->dispatch('close-drawer', name: 'board-form');
        $this->dispatch('toast', message: ($editing ? 'Güncellendi: ' : 'Eklendi: ').$board->build_name);
    }

    public function render()
    {
        return view('livewire.board-ids.board-ids-form');
    }
}
