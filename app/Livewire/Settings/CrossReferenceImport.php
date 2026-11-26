<?php

namespace App\Livewire\Settings;

use App\Services\CrossReferenceCatalog;
use App\Support\SampleCrossReference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Ayarlar › Çapraz referans: JSON dışa aktarma, JSON içe aktarma (önce önizleme, onayla yazılır) ve örnek veri.
 */
class CrossReferenceImport extends Component
{
    use WithFileUploads;

    public $file;

    /** Mevcut parçalar: skip (bilgileri koru) | update (dosyadakiyle güncelle). Bağlantılar her durumda eklenir. */
    public string $strategy = 'skip';

    /** Önizleme sekmesi: parts | devices */
    public string $previewTab = 'parts';

    #[Locked]
    public ?string $token = null;

    #[Locked]
    public ?string $fileName = null;

    #[Locked]
    public array $summary = [];

    #[Locked]
    public array $preview = [];

    #[Locked]
    public array $previewDevices = [];

    public function updatedFile(CrossReferenceCatalog $catalog): void
    {
        $this->discard(keepFile: true);
        $this->validate(['file' => ['required', 'file', 'extensions:json', 'max:'.ProductImport::maxUploadKb()]], [
            'file.extensions' => 'Lütfen çapraz referans .json dosyası seçin.',
            'file.max' => 'Dosya sunucu yükleme sınırını aşıyor.',
        ]);

        $parsed = $catalog->parse(file_get_contents($this->file->getRealPath()));
        $name = $this->file->getClientOriginalName();
        $this->file = null;
        if ($parsed['errors']) {
            $this->addError('file', $parsed['errors'][0]);

            return;
        }
        $this->analyze($catalog, $parsed['data'], $name);
    }

    public function loadSample(CrossReferenceCatalog $catalog): void
    {
        $this->discard();
        $this->analyze($catalog, $catalog->parse(json_encode(SampleCrossReference::data()))['data'], 'Örnek çapraz referans verisi');
    }

    protected function analyze(CrossReferenceCatalog $catalog, array $data, string $name): void
    {
        $analysis = $catalog->analyze(Auth::user(), $data);
        if (! $analysis['parts']) {
            $this->addError('file', 'Dosyada parça yok.');

            return;
        }

        $this->token = (string) Str::uuid();
        Storage::disk('local')->put($this->path(), json_encode($analysis));
        $this->fileName = $name;
        $this->summary = $analysis['summary'] + ['total' => count($analysis['parts'])];
        $this->preview = collect($analysis['parts'])->take(300)->map(fn ($i) => [
            'status' => $i['status'],
            'type' => $i['data']['type'],
            'code' => $i['data']['code'],
            'name' => $i['data']['name'],
            'manufacturer' => $i['data']['manufacturer'],
            'devices' => $i['device_labels'],
            'compat' => count($i['compatible']),
            'errors' => $i['errors'],
        ])->all();
        $this->previewDevices = collect($analysis['devices'])->take(300)->map(fn ($d) => [
            'status' => $d['status'],
            'brand' => $d['data']['brand'],
            'model_name' => $d['data']['model_name'],
            'codename' => $d['data']['codename'],
            'model_number' => $d['data']['model_number'],
            'chipset' => $d['data']['chipset'],
        ])->all();
    }

    public function import(CrossReferenceCatalog $catalog): void
    {
        if (! $this->token || ! Storage::disk('local')->exists($this->path())) {
            $this->addError('file', 'Önizleme süresi doldu; dosyayı yeniden yükleyin.');

            return;
        }
        $this->validate(['strategy' => 'in:skip,update']);

        $r = $catalog->import(Auth::user(), json_decode(Storage::disk('local')->get($this->path()), true), $this->strategy);
        $this->discard();

        $this->dispatch('cross-ref-saved');
        $this->dispatch('toast', message: "Çapraz referans içe aktarıldı: {$r['parts_created']} yeni parça, {$r['devices_created']} yeni cihaz, {$r['links']} cihaz bağlantısı, {$r['compat']} muadillik.");
    }

    public function discard(bool $keepFile = false): void
    {
        if ($this->token) {
            Storage::disk('local')->delete($this->path());
        }
        $this->reset('token', 'fileName', 'summary', 'preview', 'previewDevices', 'previewTab');
        if (! $keepFile) {
            $this->reset('file');
        }
        $this->resetValidation();
    }

    protected function path(): string
    {
        return 'imports/cross-reference-'.Auth::id().'-'.$this->token.'.json';
    }

    public function render()
    {
        return view('livewire.settings.cross-reference-import', [
            'partCount' => Auth::user()->parts()->reference()->count(),
            'maxKb' => ProductImport::maxUploadKb(),
        ]);
    }
}
