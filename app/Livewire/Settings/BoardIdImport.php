<?php

namespace App\Livewire\Settings;

use App\Services\BoardIdCatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Board ID kütüphanesi CSV içe / dışa aktarma. */
class BoardIdImport extends Component
{
    use WithFileUploads;

    public $file;

    public string $strategy = 'skip';

    public string $previewFilter = 'all';

    #[Locked]
    public ?string $token = null;

    #[Locked]
    public array $summary = [];

    #[Locked]
    public array $preview = [];

    #[Locked]
    public array $parseErrors = [];

    #[Locked]
    public array $parseWarnings = [];

    #[Locked]
    public ?string $fileName = null;

    public function updatedFile(BoardIdCatalog $catalog): void
    {
        $this->discard(keepFile: true);

        $this->validate(['file' => ['required', 'file', 'extensions:csv', 'max:'.ProductImport::maxUploadKb()]], [
            'file.extensions' => 'Lütfen .csv dosyası seçin.',
            'file.max' => 'Dosya sunucu yükleme sınırını aşıyor.',
        ]);

        $parsed = $catalog->parse(file_get_contents($this->file->getRealPath()), $this->file->getClientOriginalName());
        $this->fileName = $this->file->getClientOriginalName();
        $this->parseErrors = $parsed['errors'];
        $this->parseWarnings = $parsed['warnings'] ?? [];
        $this->file = null;

        if (! $parsed['rows']) {
            return;
        }

        $analysis = $catalog->analyze(Auth::user(), $parsed['rows']);
        $this->token = (string) Str::uuid();
        Storage::disk('local')->put($this->path(), json_encode($analysis['items']));

        $this->summary = $analysis['summary'] + [
            'total' => count($analysis['items']),
            'models' => collect($analysis['items'])->pluck('data.codename')->filter()->unique()->count(),
        ];
        $this->preview = collect($analysis['items'])->take(200)->map(fn ($i) => [
            'line' => $i['line'],
            'status' => $i['status'],
            'codename' => $i['data']['codename'],
            'build' => $i['data']['build_name'],
            'r2' => $i['data']['resistances']['R2'] ?? null,
            'r2_org' => $i['data']['resistances']['R2_ORG'] ?? null,
            'hw' => $i['data']['mass_production_hwid'],
            'after' => $i['data']['pre_production_hwid'],
            'methods' => count($i['data']['repair_methods']),
            'errors' => $i['errors'],
        ])->all();
    }

    public function import(BoardIdCatalog $catalog): void
    {
        if (! $this->token || ! Storage::disk('local')->exists($this->path())) {
            $this->addError('file', 'Önizleme süresi doldu; dosyayı yeniden yükleyin.');

            return;
        }
        $this->validate(['strategy' => 'in:skip,update']);

        $result = $catalog->import(Auth::user(), json_decode(Storage::disk('local')->get($this->path()), true), $this->strategy);
        $this->discard();

        $this->dispatch('board-id-saved');
        $extra = $result['products'] ? " · {$result['products']} yeni model oluşturuldu" : '';
        $this->dispatch('toast', message: "İçe aktarma tamamlandı: {$result['created']} yeni, {$result['updated']} güncellendi, {$result['skipped']} atlandı{$extra}.");
    }

    public function discard(bool $keepFile = false): void
    {
        if ($this->token) {
            Storage::disk('local')->delete($this->path());
        }
        $this->reset('token', 'summary', 'preview', 'parseErrors', 'parseWarnings', 'fileName', 'previewFilter');
        if (! $keepFile) {
            $this->reset('file');
        }
        $this->resetValidation();
    }

    protected function path(): string
    {
        return 'imports/board-ids-'.Auth::id().'-'.$this->token.'.json';
    }

    public function render()
    {
        return view('livewire.settings.board-id-import', [
            'maxKb' => ProductImport::maxUploadKb(),
            'boardCount' => Auth::user()->boardIds()->count(),
            'sampleCount' => BoardIdCatalog::sampleCount(),
        ]);
    }
}
