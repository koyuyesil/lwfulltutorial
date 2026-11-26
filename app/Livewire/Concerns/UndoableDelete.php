<?php

namespace App\Livewire\Concerns;

use App\Support\Trash;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

/** Liste bileşenleri: kaydı çöp kutusuna taşır ve "Geri al" düğmeli bildirim gösterir. */
trait UndoableDelete
{
    protected function trashWithUndo(Model $model, ?string $title = null): void
    {
        $title ??= Trash::title($model);
        $model->delete();

        $this->dispatch('toast',
            message: "{$title} silindi.",
            type: 'info',
            timeout: 8000,
            action: ['label' => 'Geri al', 'event' => 'trash-restore', 'params' => ['type' => Trash::typeOf($model), 'id' => $model->getKey()]],
        );
    }

    /** Geri alınınca listeyi yeniden çiz. */
    #[On('trash-restored')]
    public function refreshAfterRestore(): void {}
}
