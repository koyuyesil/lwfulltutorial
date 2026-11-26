<?php

namespace App\Livewire;

use App\Support\Trash;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Düzende bir kez bulunur: "Silindi · Geri al" bildirimindeki düğme bu olayı tetikler.
 * Geri yüklenince listeler "trash-restored" olayıyla yenilenir.
 */
class TrashUndo extends Component
{
    #[On('trash-restore')]
    public function restore(string $type, int $id): void
    {
        $model = Trash::restore(Auth::user(), $type, $id);

        $this->dispatch('trash-restored', type: $type, id: $id);
        $this->dispatch('toast', message: Trash::title($model).' geri alındı.', type: 'success');
    }

    public function render(): string
    {
        return '<div hidden></div>';
    }
}
