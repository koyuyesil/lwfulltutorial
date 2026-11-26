<?php

namespace App\Livewire\Settings;

use App\Models\Task;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Genel ayarlar ve her modülün kendi ayar sekmesi.
 */
#[Title('Ayarlar')]
class SettingsIndex extends Component
{
    public const TABS = ['genel', 'servis', 'musteriler', 'bildirimler', 'cihaz-modelleri', 'board-id', 'gorevler'];

    public string $tab = 'genel';

    /* Genel */
    public string $companyName = '';

    public string $companyPhone = '';

    public string $companyAddress = '';

    /* Servis kayıtları */
    public array $sla = [];

    public array $technicians = [];

    public string $newTechnician = '';

    /* Bildirim şablonları */
    public array $templates = [];

    /* Görevler */
    public string $taskDefaultTime = '09:00';

    public string $taskDefaultRemind = '0';

    /* Cihaz modelleri */
    public string $productsDefaultView = 'cards';

    public string $importStrategy = 'skip';

    public function mount(?string $tab = null): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'genel';
        $user = Auth::user();

        $this->companyName = (string) $user->setting('company.name');
        $this->companyPhone = (string) $user->setting('company.phone');
        $this->companyAddress = (string) $user->setting('company.address');
        $this->sla = $user->setting('tickets.sla_hours');
        $this->technicians = $user->technicians();
        $this->templates = \App\Support\NotificationTemplates::all($user);
        $this->taskDefaultTime = (string) $user->setting('tasks.default_time');
        $this->taskDefaultRemind = (string) $user->setting('tasks.default_remind');
        $this->productsDefaultView = (string) $user->setting('products.default_view');
        $this->importStrategy = (string) $user->setting('products.import_strategy');
    }

    public function saveGeneral(): void
    {
        $data = $this->validate([
            'companyName' => ['nullable', 'string', 'max:120'],
            'companyPhone' => ['nullable', 'string', 'max:30'],
            'companyAddress' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        Settings::set($user, 'company.name', trim($data['companyName']) ?: null);
        Settings::set($user, 'company.phone', trim($data['companyPhone']) ?: null);
        Settings::set($user, 'company.address', trim($data['companyAddress']) ?: null);
        $this->saved();
    }

    public function saveTickets(): void
    {
        $this->validate([
            'sla.high' => ['required', 'integer', 'min:1', 'max:2160'],
            'sla.normal' => ['required', 'integer', 'min:1', 'max:2160'],
            'sla.low' => ['required', 'integer', 'min:1', 'max:2160'],
        ], [
            'sla.*.required' => 'Süre zorunludur.',
            'sla.*.min' => 'En az 1 saat olmalıdır.',
            'sla.*.max' => 'En fazla 90 gün (2160 saat) olabilir.',
        ]);

        Settings::set(Auth::user(), 'tickets.sla_hours', array_map('intval', $this->sla));
        $this->saved('Yeni servis kayıtları bu sürelerle açılacak.');
    }

    public function addTechnician(): void
    {
        $this->validate(['newTechnician' => ['required', 'string', 'min:2', 'max:60']], ['newTechnician.required' => 'Ad yazın.']);
        $name = trim(preg_replace('/\s+/', ' ', $this->newTechnician));
        if (in_array($name, $this->technicians, true)) {
            $this->addError('newTechnician', 'Bu teknisyen zaten listede.');

            return;
        }
        $this->technicians[] = $name;
        $this->newTechnician = '';
        $this->saveTechnicians();
    }

    public function removeTechnician(int $index): void
    {
        unset($this->technicians[$index]);
        $this->technicians = array_values($this->technicians);
        $this->saveTechnicians();
    }

    public function saveTechnicians(): void
    {
        Settings::set(Auth::user(), 'tickets.technicians', array_values(array_unique($this->technicians)));
        $this->saved();
    }

    public function saveTemplates(): void
    {
        $this->validate(['templates' => ['array'], 'templates.*' => ['nullable', 'string', 'max:1000']]);
        $defaults = \App\Support\NotificationTemplates::defaults();
        // Varsayılandan farklı olanlar saklanır; böylece varsayılan metin güncellenirse otomatik yansır
        $custom = array_filter(
            array_intersect_key(array_map(fn ($v) => trim((string) $v), $this->templates), $defaults),
            fn ($v, $k) => $v !== '' && $v !== $defaults[$k],
            ARRAY_FILTER_USE_BOTH,
        );
        Settings::set(Auth::user(), 'notify.templates', $custom);
        $this->templates = \App\Support\NotificationTemplates::all(Auth::user());
        $this->saved();
    }

    public function resetTemplate(string $status): void
    {
        $this->templates[$status] = \App\Support\NotificationTemplates::defaults()[$status] ?? '';
    }

    public function resetSla(): void
    {
        $this->sla = \App\Models\Ticket::SLA_HOURS;
        $this->resetValidation();
    }

    public function saveTasks(): void
    {
        $this->validate([
            'taskDefaultTime' => ['required', 'date_format:H:i'],
            'taskDefaultRemind' => ['nullable', Rule::in(array_keys(Task::REMIND_OPTIONS))],
        ]);

        Settings::set(Auth::user(), 'tasks.default_time', $this->taskDefaultTime);
        Settings::set(Auth::user(), 'tasks.default_remind', $this->taskDefaultRemind);
        $this->saved();
    }

    public function saveProducts(): void
    {
        $this->validate([
            'productsDefaultView' => ['required', Rule::in(['cards', 'table'])],
            'importStrategy' => ['required', Rule::in(['skip', 'update'])],
        ]);

        Settings::set(Auth::user(), 'products.default_view', $this->productsDefaultView);
        Settings::set(Auth::user(), 'products.import_strategy', $this->importStrategy);
        $this->saved();
    }

    protected function saved(string $extra = ''): void
    {
        $this->dispatch('toast', message: trim('Ayarlar kaydedildi. '.$extra));
    }

    public function render()
    {
        return view('livewire.settings.settings-index')->layout('layouts.app');
    }
}
