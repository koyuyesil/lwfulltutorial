<?php

namespace App\Livewire\Settings;

use App\Services\CrossReferenceCatalog;
use App\Services\DeviceCatalog;
use App\Support\SampleCrossReference;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Ayarlar > Çapraz referans: CSV içe aktarma, CSV dışa aktarma ve örnek veriyi yükleme. */
class CrossReferenceImport extends Component
{
    use WithFileUploads;

    public $file;

    public bool $createDevices = false;

    public ?array $report = null;

    public function updatedFile(CrossReferenceCatalog $catalog): void
    {
        $this->validate(['file' => ['required', 'file', 'extensions:csv,txt', 'max:'.ProductImport::maxUploadKb()]], [
            'file.extensions' => 'Lütfen .csv dosyası seçin.',
            'file.max' => 'Dosya sunucu yükleme sınırını aşıyor.',
        ]);
        $parsed = $catalog->parse(file_get_contents($this->file->getRealPath()));
        $this->file = null;
        if ($parsed['errors']) {
            $this->addError('file', $parsed['errors'][0]);

            return;
        }
        $this->run($catalog, $parsed['rows']);
    }

    public function loadSample(CrossReferenceCatalog $catalog): void
    {
        $handle = fopen('php://temp', 'r+');
        DeviceCatalog::writeCsv(SampleCrossReference::csvRows(), $handle);
        rewind($handle);
        $rows = $catalog->parse(stream_get_contents($handle))['rows'];
        fclose($handle);
        $this->run($catalog, $rows);
    }

    protected function run(CrossReferenceCatalog $catalog, array $rows): void
    {
        $this->report = $catalog->import(Auth::user(), $rows, $this->createDevices);
        $r = $this->report;
        $this->dispatch('toast', message: "Çapraz referans içe aktarıldı: {$r['components_created']} yeni, {$r['components_updated']} güncellenen bileşen, {$r['links']} cihaz bağlantısı.");
    }

    public function render()
    {
        return view('livewire.settings.cross-reference-import', [
            'componentCount' => Auth::user()->components()->count(),
            'maxKb' => ProductImport::maxUploadKb(),
        ]);
    }
}
