@php
    $tabs = [
        'genel' => ['Genel', 'Firma bilgileri', 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
        'servis' => ['Servis kayıtları', 'SLA ve teslim hedefleri', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'musteriler' => ['Müşteriler', 'vCard ve CSV aktarımı', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
        'cihaz-modelleri' => ['Cihaz modelleri', 'İçe / dışa aktarma', 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3'],
        'board-id' => ['Board ID kütüphanesi', 'CSV içe / dışa aktarma', 'M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z'],
        'gorevler' => ['Görevler', 'Varsayılan saat ve hatırlatma', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ];
    $card = 'rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10';
    $saveBtn = 'inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60';
@endphp

<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Ayarlar /</span> {{ $tabs[$tab][0] }}</h2>
</x-slot>

<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Ayarlar</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Firma bilgileri ve her modülün kendine ait ayarları.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[240px_1fr]">
        {{-- Sekmeler --}}
        <nav class="flex gap-1 overflow-x-auto lg:flex-col" aria-label="Ayar bölümleri">
            @foreach ($tabs as $key => [$label, $hint, $icon])
                <a href="{{ route('settings', $key) }}" wire:navigate @if ($tab === $key) aria-current="page" @endif
                    @class(['flex shrink-0 items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition',
                        'bg-white font-semibold text-slate-900 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:text-white dark:ring-white/10' => $tab === $key,
                        'text-slate-600 hover:bg-white/60 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5' => $tab !== $key])>
                    <svg @class(['h-5 w-5 shrink-0', 'text-indigo-500' => $tab === $key, 'text-slate-400' => $tab !== $key]) fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                    <span class="min-w-0">
                        <span class="block">{{ $label }}</span>
                        <span class="hidden truncate text-xs font-normal text-slate-500 lg:block">{{ $hint }}</span>
                    </span>
                </a>
            @endforeach
        </nav>

        <div class="min-w-0 space-y-6">
            @switch($tab)
                @case('genel')
                    <form wire:submit="saveGeneral" class="{{ $card }}">
                        <h3 class="font-bold text-slate-900 dark:text-white">Firma bilgileri</h3>
                        <p class="mt-1 text-sm text-slate-500">Müşterinin arıza takip sayfasında ve servis fişlerinde görünür.</p>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <x-ui.field label="Firma adı" for="s-company" error="companyName" :hint="'Boş bırakılırsa: ' . auth()->user()->name" class="sm:col-span-2">
                                <x-ui.input id="s-company" wire:model="companyName" placeholder="Örn. KoyuMavi Teknik Servis" />
                            </x-ui.field>
                            <x-ui.field label="Telefon" for="s-phone" error="companyPhone" optional>
                                <x-ui.input id="s-phone" type="tel" wire:model="companyPhone" />
                            </x-ui.field>
                            <x-ui.field label="Adres" for="s-address" error="companyAddress" optional>
                                <x-ui.input id="s-address" wire:model="companyAddress" />
                            </x-ui.field>
                        </div>
                        <div class="mt-6 flex justify-end"><button class="{{ $saveBtn }}" wire:loading.attr="disabled">Kaydet</button></div>
                    </form>
                    @break

                @case('servis')
                    <form wire:submit="saveTickets" class="{{ $card }}">
                        <h3 class="font-bold text-slate-900 dark:text-white">Teslim hedefleri (SLA)</h3>
                        <p class="mt-1 text-sm text-slate-500">Yeni servis kaydı açıldığında önceliğe göre teslim hedefi bu sürelerle hesaplanır. Sürenin son %25'ine giren işler "riskli" sayılır.</p>
                        <div class="mt-6 grid gap-4 sm:grid-cols-3">
                            @foreach (\App\Enums\PriorityType::cases() as $p)
                                <x-ui.field :label="$p->label() . ' öncelik'" :for="'s-sla-' . $p->value" :error="'sla.' . $p->value"
                                    :hint="isset($sla[$p->value]) && (int) $sla[$p->value] >= 24 ? '≈ ' . round((int) $sla[$p->value] / 24, 1) . ' gün' : null">
                                    <div class="relative">
                                        <x-ui.input :id="'s-sla-' . $p->value" type="number" min="1" max="2160" wire:model.live.debounce.300ms="sla.{{ $p->value }}" class="pr-14" />
                                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">saat</span>
                                    </div>
                                </x-ui.field>
                            @endforeach
                        </div>
                        <div class="mt-6 flex items-center justify-between gap-3">
                            <button type="button" wire:click="resetSla" class="text-sm font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Varsayılana dön (24 / 72 / 120)</button>
                            <button class="{{ $saveBtn }}" wire:loading.attr="disabled">Kaydet</button>
                        </div>
                    </form>
                    @break

                @case('musteriler')
                    <livewire:settings.client-import />
                    @break

                @case('cihaz-modelleri')
                    <livewire:settings.product-import />

                    <form wire:submit="saveProducts" class="{{ $card }}">
                        <h3 class="font-bold text-slate-900 dark:text-white">Modül tercihleri</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <x-ui.field label="Varsayılan görünüm" for="s-view">
                                <select id="s-view" wire:model="productsDefaultView" class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                                    <option value="cards">Kartlar (görselli)</option>
                                    <option value="table">Kompakt tablo</option>
                                </select>
                            </x-ui.field>
                            <x-ui.field label="İçe aktarmada mükerrer kayıtlar" for="s-strategy" hint="İçe aktarma ekranında her seferinde değiştirilebilir">
                                <select id="s-strategy" wire:model="importStrategy" class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                                    <option value="skip">Atla (mevcut kaydı koru)</option>
                                    <option value="update">Güncelle (boş olmayan alanları yaz)</option>
                                </select>
                            </x-ui.field>
                        </div>
                        <div class="mt-6 flex justify-end"><button class="{{ $saveBtn }}" wire:loading.attr="disabled">Kaydet</button></div>
                    </form>
                    @break

                @case('board-id')
                    <livewire:settings.board-id-import />
                    @break

                @case('gorevler')
                    <form wire:submit="saveTasks" class="{{ $card }}">
                        <h3 class="font-bold text-slate-900 dark:text-white">Görev varsayılanları</h3>
                        <p class="mt-1 text-sm text-slate-500">Yeni görev ve hızlı eklemede saat belirtilmezse bu değerler kullanılır.</p>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <x-ui.field label="Varsayılan saat" for="s-time" error="taskDefaultTime">
                                <x-ui.input id="s-time" type="time" wire:model="taskDefaultTime" />
                            </x-ui.field>
                            <x-ui.field label="Varsayılan hatırlatma" for="s-remind" error="taskDefaultRemind">
                                <select id="s-remind" wire:model="taskDefaultRemind" class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                                    @foreach (\App\Models\Task::REMIND_OPTIONS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>
                        <div class="mt-6 flex justify-end"><button class="{{ $saveBtn }}" wire:loading.attr="disabled">Kaydet</button></div>
                    </form>
                    @break
            @endswitch
        </div>
    </div>
</div>
