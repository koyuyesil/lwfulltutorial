<?php

namespace App\Livewire\Settings;

use App\Services\PartCatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Ayarlar › Parçalar: CSV dışa aktarma ve içe aktarma (önce önizleme, onayla yazılır). */
class PartImport extends Component
{
    use WithFileUploads;

    public $file;

    public string $strategy = 'skip';

    public string $previewFilter = 'all';

    #[Locked]
    public ?string $token = null;

    #[Locked]
    public ?string $fileName = null;

    #[Locked]
    public array $summary = [];

    #[Locked]
    public array $preview = [];

    public function updatedFile(PartCatalog $catalog): void
    {
        $this->discard(keepFile: true);
        $this->validate(['file' => ['required', 'file', 'extensions:csv,txt', 'max:'.ProductImport::maxUploadKb()]], [
            'file.extensions' => 'Lütfen .csv dosyası seçin.',
            'file.max' => 'Dosya sunucu yükleme sınırını aşıyor.',
        ]);

        $parsed = $catalog->parse(file_get_contents($this->file->getRealPath()));
        $name = $this->file->getClientOriginalName();
        $this->file = null;
        if ($parsed['errors'] || ! $parsed['rows']) {
            $this->addError('file', $parsed['errors'][0] ?? 'Dosyada parça satırı yok.');

            return;
        }

        $analysis = $catalog->analyze(Auth::user(), $parsed['rows']);
        $this->token = (string) Str::uuid();
        Storage::disk('local')->put($this->path(), json_encode($analysis['items']));
        $this->fileName = $name;
        $this->summary = $analysis['summary'] + ['total' => count($analysis['items'])];
        $this->preview = collect($analysis['items'])->take(300)->map(fn ($i) => [
            'line' => $i['line'], 'status' => $i['status'],
            'name' => $i['data']['name'], 'sku' => $i['data']['sku'], 'category' => $i['data']['category'],
            'code' => $i['data']['code'], 'price' => $i['data']['price'], 'stock' => $i['data']['stock'],
            'messages' => array_merge($i['errors'], $i['warnings']),
        ])->all();
    }

    public function import(PartCatalog $catalog): void
    {
        if (! $this->token || ! Storage::disk('local')->exists($this->path())) {
            $this->addError('file', 'Önizleme süresi doldu; dosyayı yeniden yükleyin.');

            return;
        }
        $this->validate(['strategy' => 'in:skip,update']);

        $r = $catalog->import(Auth::user(), json_decode(Storage::disk('local')->get($this->path()), true), $this->strategy);
        $this->discard();
        $this->dispatch('toast', message: "Parçalar içe aktarıldı: {$r['created']} yeni, {$r['updated']} güncellendi, {$r['skipped']} atlandı.");
    }

    public function discard(bool $keepFile = false): void
    {
        if ($this->token) {
            Storage::disk('local')->delete($this->path());
        }
        $this->reset('token', 'fileName', 'summary', 'preview', 'previewFilter');
        if (! $keepFile) {
            $this->reset('file');
        }
        $this->resetValidation();
    }

    protected function path(): string
    {
        return 'imports/parts-'.Auth::id().'-'.$this->token.'.json';
    }

    public function render()
    {
        return view('livewire.settings.part-import', [
            'partCount' => Auth::user()->parts()->count(),
            'maxKb' => ProductImport::maxUploadKb(),
        ]);
    }
}
