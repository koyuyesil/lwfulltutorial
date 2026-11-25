<?php

namespace App\Livewire\Settings;

use App\Services\ClientContacts;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Müşteri rehberi içe / dışa aktarma: CSV ve vCard (2.1 / 3.0 / 4.0).
 * İçe aktarmada biçim ve vCard sürümü otomatik algılanır.
 */
class ClientImport extends Component
{
    use WithFileUploads;

    public $file;

    /** Dışa aktarma ve QR için tercih edilen vCard sürümü */
    public string $version = '3.0';

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
    public ?string $fileName = null;

    #[Locked]
    public ?string $detected = null;

    public function mount(): void
    {
        $this->version = (string) Auth::user()->setting('clients.vcard_version');
        $this->strategy = (string) Auth::user()->setting('clients.import_strategy');
    }

    public function updatedVersion(): void
    {
        if (in_array($this->version, ClientContacts::VCARD_VERSIONS, true)) {
            Settings::set(Auth::user(), 'clients.vcard_version', $this->version);
        }
    }

    public function updatedFile(ClientContacts $contacts): void
    {
        $this->discard(keepFile: true);

        $this->validate(['file' => ['required', 'file', 'extensions:csv,txt,vcf,vcard', 'max:'.ProductImport::maxUploadKb()]], [
            'file.extensions' => 'Lütfen .csv ya da .vcf (vCard) dosyası seçin.',
            'file.max' => 'Dosya sunucu yükleme sınırını aşıyor.',
        ]);

        $parsed = $contacts->parse(file_get_contents($this->file->getRealPath()), $this->file->getClientOriginalName());
        $this->fileName = $this->file->getClientOriginalName();
        $this->parseErrors = $parsed['errors'];
        $this->file = null;

        if (! $parsed['records']) {
            return;
        }

        $versions = $parsed['versions'];
        arsort($versions);
        $this->detected = $parsed['format'] === 'vcard'
            ? 'vCard '.implode(' + ', array_keys($versions))
            : 'CSV';

        $analysis = $contacts->analyze(Auth::user(), $parsed['records']);
        $this->token = (string) Str::uuid();
        Storage::disk('local')->put($this->path(), json_encode($analysis['items']));

        $this->summary = $analysis['summary'] + ['total' => count($analysis['items'])];
        $this->preview = collect($analysis['items'])->take(200)->map(fn ($i) => [
            'line' => $i['line'],
            'version' => $i['version'],
            'status' => $i['status'],
            'name' => trim($i['data']['first_name'].' '.$i['data']['last_name']),
            'phone' => $i['data']['phone'],
            'email' => $i['data']['email'],
            'company' => $i['data']['company'],
            'messages' => array_merge($i['errors'], $i['warnings']),
        ])->all();
    }

    public function import(ClientContacts $contacts): void
    {
        if (! $this->token || ! Storage::disk('local')->exists($this->path())) {
            $this->addError('file', 'Önizleme süresi doldu; dosyayı yeniden yükleyin.');

            return;
        }
        $this->validate(['strategy' => 'in:skip,update']);
        Settings::set(Auth::user(), 'clients.import_strategy', $this->strategy);

        $items = json_decode(Storage::disk('local')->get($this->path()), true);
        $result = $contacts->import(Auth::user(), $items, $this->strategy);
        $this->discard();

        $this->dispatch('client-saved');
        $this->dispatch('toast', message: "İçe aktarma tamamlandı: {$result['created']} yeni, {$result['updated']} güncellendi, {$result['skipped']} atlandı.");
    }

    public function discard(bool $keepFile = false): void
    {
        if ($this->token) {
            Storage::disk('local')->delete($this->path());
        }
        $this->reset('token', 'summary', 'preview', 'parseErrors', 'fileName', 'detected', 'previewFilter');
        if (! $keepFile) {
            $this->reset('file');
        }
        $this->resetValidation();
    }

    protected function path(): string
    {
        return 'imports/clients-'.Auth::id().'-'.$this->token.'.json';
    }

    public function render()
    {
        return view('livewire.settings.client-import', [
            'maxKb' => ProductImport::maxUploadKb(),
            'clientCount' => Auth::user()->clients()->count(),
        ]);
    }
}
