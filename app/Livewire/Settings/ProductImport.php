<?php

namespace App\Livewire\Settings;

use App\Services\DeviceCatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Cihaz modeli CSV içe aktarma: yükle → önizle (yeni / mükerrer / hatalı) → içe aktar.
 * Ayrıştırılan satırlar (görseller dahil) geçici dosyada tutulur; tarayıcıya yalnızca özet gider.
 */
class ProductImport extends Component
{
    use WithFileUploads;

    public $file;

    #[Locked]
    public ?string $token = null;

    #[Locked]
    public array $summary = [];

    #[Locked]
    public array $preview = [];

    #[Locked]
    public array $parseErrors = [];

    #[Locked]
    public ?string $fileName = null;

    public string $strategy = 'skip';

    public string $previewFilter = 'all';

    public function mount(): void
    {
        $this->strategy = (string) Auth::user()->setting('products.import_strategy');
    }

    /** Sunucunun izin verdiği en büyük yükleme (KB). */
    public static function maxUploadKb(): int
    {
        $toKb = function (string $v): int {
            $n = (int) $v;

            return match (strtolower(substr(trim($v), -1))) {
                'g' => $n * 1024 * 1024, 'm' => $n * 1024, 'k' => $n, default => (int) ceil($n / 1024),
            };
        };

        return min(12 * 1024, $toKb((string) ini_get('upload_max_filesize')), $toKb((string) ini_get('post_max_size')));
    }

    public function updatedFile(DeviceCatalog $catalog): void
    {
        $this->discard(keepFile: true);

        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:'.self::maxUploadKb()]], [
            'file.mimes' => 'Lütfen .csv uzantılı bir dosya seçin.',
            'file.max' => 'Dosya sunucu yükleme sınırını aşıyor.',
        ]);

        $this->analyzeFile($catalog, $this->file->getRealPath(), $this->file->getClientOriginalName());
        $this->file = null;
    }

    /** Örnek katalog normal yükleme gibi önizlemeye alınır; içe aktarma yine onayla yapılır. */
    public function loadSample(DeviceCatalog $catalog): void
    {
        $this->discard();
        $tmp = tempnam(sys_get_temp_dir(), 'smp');
        $handle = fopen($tmp, 'w');
        DeviceCatalog::writeCsv(\App\Support\SampleDeviceCatalog::csvRows(), $handle);
        fclose($handle);
        $this->analyzeFile($catalog, $tmp, 'Örnek cihaz kataloğu');
        @unlink($tmp);
    }

    protected function analyzeFile(DeviceCatalog $catalog, string $path, string $name): void
    {
        $parsed = $catalog->parse($path);
        $this->fileName = $name;
        $this->parseErrors = $parsed['errors'];

        if (! $parsed['rows']) {
            return;
        }

        $analysis = $catalog->analyze(Auth::user(), $parsed['rows']);
        $this->token = (string) Str::uuid();
        Storage::disk('local')->put($this->path(), json_encode($analysis['items']));

        $this->summary = $analysis['summary'] + ['total' => count($analysis['items']), 'images' => collect($analysis['items'])->whereNotNull('data.image')->count()];
        $this->preview = collect($analysis['items'])->take(200)->map(fn ($item) => [
            'line' => $item['line'],
            'status' => $item['status'],
            'brand' => $item['data']['brand'],
            'model_name' => $item['data']['model_name'],
            'model_number' => $item['data']['model_number'],
            'codename' => $item['data']['codename'],
            'release_year' => $item['data']['release_year'],
            'chipset' => $item['data']['chipset'],
            'has_image' => $item['data']['image'] !== null,
            'messages' => array_merge($item['errors'], $item['warnings']),
        ])->all();
    }

    public function import(DeviceCatalog $catalog): void
    {
        if (! $this->token || ! Storage::disk('local')->exists($this->path())) {
            $this->addError('file', 'Önizleme süresi doldu; dosyayı yeniden yükleyin.');

            return;
        }

        $this->validate(['strategy' => 'in:skip,update']);

        $items = json_decode(Storage::disk('local')->get($this->path()), true);

        $result = $catalog->import(Auth::user(), $items, $this->strategy);
        $this->discard();

        $this->dispatch('product-saved');
        $this->dispatch('toast', message: "İçe aktarma tamamlandı: {$result['created']} yeni, {$result['updated']} güncellendi, {$result['skipped']} atlandı.");
    }

    public function discard(bool $keepFile = false): void
    {
        if ($this->token) {
            Storage::disk('local')->delete($this->path());
        }
        $this->reset('token', 'summary', 'preview', 'parseErrors', 'fileName', 'previewFilter');
        if (! $keepFile) {
            $this->reset('file');
        }
        $this->resetValidation();
    }

    protected function path(): string
    {
        return 'imports/'.Auth::id().'-'.$this->token.'.json';
    }

    public function render()
    {
        return view('livewire.settings.product-import', [
            'maxKb' => self::maxUploadKb(),
            'productCount' => Auth::user()->products()->count(),
            'imageCount' => Auth::user()->products()->whereNotNull('image')->count(),
        ]);
    }
}
