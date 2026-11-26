<?php

namespace App\Livewire\Settings;

use App\Support\Trash;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Ayarlar › Çöp kutusu: silinen kayıtları geri al ya da kalıcı sil. */
class TrashBin extends Component
{
    #[Url(as: 'tur', except: '')]
    public string $type = '';

    public function mount(): void
    {
        if ($this->type !== '' && ! isset(Trash::TYPES[$this->type])) {
            $this->type = '';
        }
    }

    public function restore(string $type, int $id): void
    {
        $model = Trash::restore(Auth::user(), $type, $id);
        $this->dispatch('toast', message: Trash::title($model).' geri alındı.');
    }

    public function forceDelete(string $type, int $id): void
    {
        $model = Trash::find(Auth::user(), $type, $id);
        $title = Trash::title($model);
        $model->forceDelete();
        $this->dispatch('toast', message: "{$title} kalıcı olarak silindi.", type: 'info');
    }

    public function emptyTrash(): void
    {
        $user = Auth::user();
        $count = 0;
        foreach (array_keys(Trash::TYPES) as $type) {
            if ($this->type !== '' && $this->type !== $type) {
                continue;
            }
            Trash::query($user, $type)->get()->each(function ($m) use (&$count) {
                $m->forceDelete();
                $count++;
            });
        }
        $this->dispatch('toast', message: "{$count} kayıt kalıcı olarak silindi.", type: 'info');
    }

    #[On('trash-restored')]
    public function refreshList(): void {}

    public function render()
    {
        $user = Auth::user();
        $counts = Trash::counts($user);

        $items = collect(array_keys(Trash::TYPES))
            ->filter(fn ($t) => $this->type === '' || $this->type === $t)
            ->flatMap(fn ($t) => Trash::query($user, $t)->latest('deleted_at')->limit(100)->get()
                ->map(fn ($m) => [
                    'type' => $t,
                    'id' => $m->getKey(),
                    'title' => Trash::title($m),
                    'deleted_at' => $m->deleted_at,
                    'purge_at' => $m->deleted_at->copy()->addDays(Trash::RETENTION_DAYS),
                ]))
            ->sortByDesc('deleted_at')
            ->values();

        return view('livewire.settings.trash-bin', [
            'items' => $items,
            'counts' => $counts,
            'total' => array_sum($counts),
            'retention' => Trash::RETENTION_DAYS,
        ]);
    }
}
