<?php

namespace App\Livewire\CrossReference;

use App\Enums\ComponentType;
use App\Livewire\Settings\ProductImport;
use App\Services\CrossReferenceCatalog;
use App\Support\SampleCrossReference;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Çapraz referans (Premium): cihaz ↔ bileşen ↔ uyumlu bileşen.
 * "Bu RAM hangi telefonlarda var, yerine ne takılır?" ve "Bu cihazda hangi SoC / PMIC var?" sorularının cevabı.
 */
#[Title('Çapraz referans')]
class CrossReferenceIndex extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(as: 'gorunum', except: 'components')]
    public string $view = 'components'; // components | devices

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'tur', except: '')]
    public string $type = '';

    public $file;

    public bool $createDevices = false;

    public ?array $report = null;

    public function updating($name): void
    {
        if (in_array($name, ['search', 'type', 'view'], true)) {
            $this->resetPage();
        }
    }

    #[On('component-saved')]
    public function refreshList(): void
    {
    }

    public function delete(int $id): void
    {
        Auth::user()->components()->findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Bileşen silindi.');
    }

    public function updatedFile(CrossReferenceCatalog $catalog): void
    {
        $this->validate(['file' => ['required', 'file', 'extensions:csv,txt', 'max:'.ProductImport::maxUploadKb()]], [
            'file.extensions' => 'Lütfen .csv dosyası seçin.',
        ]);
        $parsed = $catalog->parse(file_get_contents($this->file->getRealPath()));
        $this->file = null;
        if ($parsed['errors']) {
            $this->addError('file', $parsed['errors'][0]);

            return;
        }
        $this->runImport($catalog, $parsed['rows']);
    }

    public function loadSample(CrossReferenceCatalog $catalog): void
    {
        $handle = fopen('php://temp', 'r+');
        \App\Services\DeviceCatalog::writeCsv(SampleCrossReference::csvRows(), $handle);
        rewind($handle);
        $this->runImport($catalog, $catalog->parse(stream_get_contents($handle))['rows']);
        fclose($handle);
    }

    protected function runImport(CrossReferenceCatalog $catalog, array $rows): void
    {
        $this->report = $catalog->import(Auth::user(), $rows, $this->createDevices);
        $r = $this->report;
        $this->dispatch('toast', message: "Çapraz referans içe aktarıldı: {$r['components_created']} yeni, {$r['components_updated']} güncellenen bileşen, {$r['links']} cihaz bağlantısı.");
    }

    public function render()
    {
        $user = Auth::user();
        $term = trim($this->search);
        $type = ComponentType::tryFrom($this->type);

        $components = $this->view === 'components'
            ? $user->components()
                ->when($type, fn ($q) => $q->where('type', $type->value))
                ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%")->orWhere('manufacturer', 'like', "%$term%")
                    ->orWhereHas('products', fn ($p) => $p->where('model_name', 'like', "%$term%")->orWhere('codename', 'like', "%$term%")->orWhere('brand', 'like', "%$term%"))))
                ->with(['products:id,brand,model_name,codename', 'compatibleTo:id,code,type', 'compatibleFrom:id,code,type'])
                ->withCount(['products', 'parts'])
                ->orderBy('type')->orderBy('code')
                ->paginate(30)
            : null;

        $devices = $this->view === 'devices'
            ? $user->products()
                ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('model_name', 'like', "%$term%")->orWhere('brand', 'like', "%$term%")->orWhere('codename', 'like', "%$term%")->orWhere('model_number', 'like', "%$term%")
                    ->orWhereHas('components', fn ($c) => $c->where('code', 'like', "%$term%")->orWhere('name', 'like', "%$term%"))))
                ->when($type, fn ($q) => $q->whereHas('components', fn ($c) => $c->where('type', $type->value)))
                ->with(['components' => fn ($q) => $q->orderBy('type')])
                ->withCount('components')
                ->orderByDesc('components_count')->orderBy('brand')->orderBy('model_name')
                ->paginate(24)
            : null;

        return view('livewire.cross-reference.cross-reference-index', [
            'components' => $components,
            'devices' => $devices,
            'counts' => [
                'components' => $user->components()->count(),
                'devices' => $user->products()->has('components')->count(),
                'compat' => \Illuminate\Support\Facades\DB::table('component_compatibilities')
                    ->whereIn('component_id', $user->components()->select('id'))->count(),
            ],
            'typeCounts' => $user->components()->selectRaw('type, count(*) as n')->groupBy('type')->pluck('n', 'type'),
        ])->layout('layouts.app');
    }
}
