<?php

use App\Services\DeviceCatalog;
use App\Support\Company;
use App\Support\Settings;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

/**
 * İşletme bilgileri: fişlerde, A4 / A5 baskılarda, müşteri takip sayfasında ve bildirimlerde kullanılır.
 */
new class extends Component
{
    use WithFileUploads;

    public array $company = [];

    public $logoUpload;

    public function mount(): void
    {
        // Kaydedilmiş ham değerler: varsayılanla doldurulanlar (kullanıcı adı, standart koşullar) formda boş görünür
        foreach (array_keys(Company::FIELDS) as $key) {
            $this->company[$key] = $key === 'logo'
                ? Auth::user()->setting('company.logo')
                : (string) (Auth::user()->setting('company.'.$key) ?? '');
        }
    }

    public function save(): void
    {
        $this->validate([
            'company.name' => ['nullable', 'string', 'max:120'],
            'company.legal_name' => ['nullable', 'string', 'max:200'],
            'company.phone' => ['nullable', 'string', 'max:30'],
            'company.email' => ['nullable', 'email', 'max:120'],
            'company.website' => ['nullable', 'string', 'max:120'],
            'company.address' => ['nullable', 'string', 'max:255'],
            'company.city' => ['nullable', 'string', 'max:80'],
            'company.tax_office' => ['nullable', 'string', 'max:80'],
            'company.tax_number' => ['nullable', 'regex:/^[0-9]{10,11}$/'],
            'company.terms' => ['nullable', 'string', 'max:1500'],
            'company.footer' => ['nullable', 'string', 'max:200'],
        ], [
            'company.email.email' => 'Geçerli bir e-posta girin.',
            'company.tax_number.regex' => 'Vergi no 10, TC kimlik no 11 rakam olmalıdır.',
        ]);

        $user = Auth::user();
        foreach ($this->company as $key => $value) {
            if ($key === 'logo') {
                continue;
            }
            Settings::set($user, 'company.'.$key, trim((string) $value) ?: null);
        }

        $this->dispatch('toast', message: 'İşletme bilgileri kaydedildi; fişler ve baskılar güncellendi.');
        $this->dispatch('company-updated');
    }

    public function updatedLogoUpload(DeviceCatalog $catalog): void
    {
        $this->validate(['logoUpload' => ['image', 'max:2048']], [
            'logoUpload.image' => 'Logo PNG, JPG, WebP ya da SVG olmalıdır.',
            'logoUpload.max' => 'Logo 2 MB\'tan küçük olmalıdır.',
        ]);

        [$uri, $error] = $catalog->normalizeImage(base64_encode(file_get_contents($this->logoUpload->getRealPath())));
        $this->logoUpload = null;
        if ($error) {
            $this->addError('logoUpload', $error);

            return;
        }

        Settings::set(Auth::user(), 'company.logo', $uri);
        $this->company['logo'] = $uri;
        $this->dispatch('toast', message: 'Logo güncellendi.');
    }

    public function removeLogo(): void
    {
        Settings::set(Auth::user(), 'company.logo', null);
        $this->company['logo'] = null;
    }

    public function with(): array
    {
        $preview = array_merge(Company::for(Auth::user()), array_filter($this->company, fn ($v) => $v !== '' && $v !== null));

        return ['preview' => $preview, 'completeness' => Company::completeness($preview)];
    }
}; ?>

<section id="isletme" class="scroll-mt-24 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
    <header class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-6 py-5 dark:border-white/5">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
        </span>
        <div class="min-w-0 flex-1">
            <h2 class="font-bold text-slate-900 dark:text-white">İşletme bilgileri</h2>
            <p class="text-sm text-slate-500">Servis fişlerinde, A4 / A5 baskılarda, müşteri takip sayfasında ve bildirimlerde kullanılır.</p>
        </div>
        <div class="w-40">
            <div class="flex justify-between text-[11px] font-semibold text-slate-500"><span>Profil</span><span>%{{ $completeness }}</span></div>
            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                <div @class(['h-full rounded-full transition-all', 'bg-emerald-500' => $completeness >= 80, 'bg-amber-400' => $completeness < 80]) style="width: {{ $completeness }}%"></div>
            </div>
        </div>
    </header>

    <div class="grid gap-8 p-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <form wire:submit="save" class="space-y-6">
            {{-- Logo --}}
            <div class="flex items-center gap-4">
                <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-2xl bg-slate-50 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                    @if ($company['logo'])
                        <img src="{{ $company['logo'] }}" alt="Logo" class="h-full w-full object-contain p-1.5">
                    @else
                        <span class="text-2xl font-black text-slate-300 dark:text-slate-600">{{ mb_strtoupper(mb_substr($preview['name'], 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Logo</p>
                    <p class="text-xs text-slate-500">Fiş ve baskıların başlığında görünür. Şeffaf PNG / SVG önerilir.</p>
                    <div class="mt-2 flex items-center gap-2">
                        <label class="cursor-pointer rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900">
                            <span wire:loading.remove wire:target="logoUpload">{{ $company['logo'] ? 'Değiştir' : 'Logo yükle' }}</span>
                            <span wire:loading wire:target="logoUpload">Yükleniyor…</span>
                            <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" wire:model="logoUpload" class="sr-only">
                        </label>
                        @if ($company['logo'])
                            <button type="button" wire:click="removeLogo" class="text-xs font-semibold text-rose-600 hover:underline">Kaldır</button>
                        @endif
                    </div>
                    @error('logoUpload') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="İşletme adı" for="c-name" error="company.name" :hint="'Boşsa: ' . auth()->user()->name">
                    <x-ui.input id="c-name" wire:model.live.debounce.400ms="company.name" placeholder="örn. KoyuMavi Teknik Servis" />
                </x-ui.field>
                <x-ui.field label="Ticari unvan" for="c-legal" error="company.legal_name" optional hint="Faturada / resmi belgede yazan tam unvan">
                    <x-ui.input id="c-legal" wire:model.live.debounce.400ms="company.legal_name" placeholder="örn. KoyuMavi Teknoloji Ltd. Şti." />
                </x-ui.field>
                <x-ui.field label="Telefon" for="c-phone" error="company.phone" optional>
                    <x-ui.input id="c-phone" type="tel" wire:model.live.debounce.400ms="company.phone" placeholder="0 212 555 00 00" />
                </x-ui.field>
                <x-ui.field label="E-posta" for="c-email" error="company.email" optional>
                    <x-ui.input id="c-email" type="email" wire:model.live.debounce.400ms="company.email" placeholder="servis@ornek.com" />
                </x-ui.field>
                <x-ui.field label="Adres" for="c-address" error="company.address" optional class="sm:col-span-2">
                    <x-ui.input id="c-address" wire:model.live.debounce.400ms="company.address" placeholder="Mahalle, cadde, no" />
                </x-ui.field>
                <x-ui.field label="İl / ilçe" for="c-city" error="company.city" optional>
                    <x-ui.input id="c-city" wire:model.live.debounce.400ms="company.city" placeholder="Kadıköy / İstanbul" />
                </x-ui.field>
                <x-ui.field label="Web sitesi" for="c-web" error="company.website" optional>
                    <x-ui.input id="c-web" wire:model.live.debounce.400ms="company.website" placeholder="www.ornek.com" />
                </x-ui.field>
                <x-ui.field label="Vergi dairesi" for="c-taxo" error="company.tax_office" optional>
                    <x-ui.input id="c-taxo" wire:model.live.debounce.400ms="company.tax_office" placeholder="Kadıköy" />
                </x-ui.field>
                <x-ui.field label="Vergi / TC kimlik no" for="c-taxn" error="company.tax_number" optional>
                    <x-ui.input id="c-taxn" inputmode="numeric" wire:model.live.debounce.400ms="company.tax_number" class="font-mono" placeholder="10 ya da 11 hane" />
                </x-ui.field>
                <x-ui.field label="Servis koşulları" for="c-terms" error="company.terms" optional class="sm:col-span-2" hint="Kabul formunda ve fişlerde yazar. Boşsa standart koşullar kullanılır.">
                    <textarea id="c-terms" wire:model.live.debounce.600ms="company.terms" rows="4" placeholder="{{ \App\Support\Company::DEFAULT_TERMS }}"
                        class="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10"></textarea>
                </x-ui.field>
                <x-ui.field label="Fiş alt notu" for="c-footer" error="company.footer" optional class="sm:col-span-2">
                    <x-ui.input id="c-footer" wire:model.live.debounce.400ms="company.footer" placeholder="örn. Garanti süresi 6 aydır · Bizi tercih ettiğiniz için teşekkürler" />
                </x-ui.field>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 dark:border-white/5">
                <span wire:dirty wire:target="company" class="text-xs text-amber-600">Kaydedilmemiş değişiklikler var</span>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500" wire:loading.attr="disabled" wire:target="save">İşletme bilgilerini kaydet</button>
            </div>
        </form>

        {{-- Canlı önizleme: fiş başlığı --}}
        <aside class="xl:sticky xl:top-24 xl:self-start">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Fiş önizlemesi</p>
            <div class="mt-2 rounded-xl bg-white p-4 text-slate-900 shadow-lg ring-1 ring-slate-200 dark:ring-white/10">
                <div class="flex items-start gap-3 border-b-2 border-slate-900 pb-3">
                    @if ($preview['logo'])
                        <img src="{{ $preview['logo'] }}" alt="" class="h-12 w-12 shrink-0 object-contain">
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-base font-black">{{ $preview['name'] }}</p>
                        @if ($preview['legal_name']) <p class="truncate text-[10px] text-slate-500">{{ $preview['legal_name'] }}</p> @endif
                        <p class="text-[10px] leading-snug text-slate-600">{{ \App\Support\Company::addressLine($preview) ?: 'Adres eklenmedi' }}</p>
                        <p class="text-[10px] text-slate-600">{{ implode(' · ', array_filter([$preview['phone'], $preview['email'], $preview['website']])) ?: 'İletişim bilgisi eklenmedi' }}</p>
                        @if ($tax = \App\Support\Company::taxLine($preview)) <p class="text-[10px] text-slate-500">{{ $tax }}</p> @endif
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-slate-500">Takip kodu</p>
                        <p class="font-mono text-sm font-black">KM-7XQ4P9TA</p>
                    </div>
                    <div class="grid h-12 w-12 grid-cols-4 gap-0.5 p-0.5 ring-1 ring-slate-300">
                        @foreach ([1,0,1,1,0,1,0,1,1,1,0,0,1,0,1,1] as $q)<span class="{{ $q ? 'bg-slate-900' : '' }}"></span>@endforeach
                    </div>
                </div>
                <p class="mt-3 line-clamp-3 text-[9px] leading-snug text-slate-500">{{ $preview['terms'] }}</p>
                @if ($preview['footer']) <p class="mt-2 border-t border-dashed border-slate-300 pt-2 text-center text-[10px] text-slate-600">{{ $preview['footer'] }}</p> @endif
            </div>
        </aside>
    </div>
</section>
