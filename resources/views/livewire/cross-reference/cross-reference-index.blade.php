@use('App\Enums\ComponentType')
@php
    $chip = 'inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition';
    $chipOn = 'bg-slate-900 text-white ring-slate-900 dark:bg-white dark:text-slate-900 dark:ring-white';
    $chipOff = 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10';
    $exportBtn = 'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold ring-1 transition';
    $exportOn = 'text-slate-700 ring-slate-200 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5';
    $exportOff = 'pointer-events-none text-slate-400 ring-slate-200 opacity-60 dark:ring-white/10';
@endphp

<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Araçlar /</span> Çapraz referans</h2>
</x-slot>

<div>
    <x-tools-tabs />

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8"
        x-on:keydown.n.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName) && ! $event.metaKey && ! $event.ctrlKey) Livewire.dispatch('create-ref-part')">

        {{-- Başlık --}}
        <section class="relative isolate overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl ring-1 ring-white/10 sm:p-8">
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-20 [background-image:linear-gradient(#334155_1px,transparent_1px),linear-gradient(90deg,#334155_1px,transparent_1px)] [background-size:28px_28px]"></div>
            <div class="pointer-events-none absolute -right-16 -top-20 -z-10 h-72 w-72 rounded-full bg-amber-500/25 blur-3xl"></div>
            {{-- İlişki şeması --}}
            <svg class="pointer-events-none absolute right-8 top-1/2 -z-10 hidden h-36 w-80 -translate-y-1/2 opacity-80 lg:block" viewBox="0 0 320 140" fill="none" aria-hidden="true">
                <rect x="4" y="50" width="78" height="40" rx="10" fill="#0f172a" stroke="#38bdf8" /><text x="43" y="75" text-anchor="middle" fill="#7dd3fc" font-size="12" font-weight="700" font-family="ui-sans-serif">Cihaz</text>
                <rect x="121" y="50" width="78" height="40" rx="10" fill="#0f172a" stroke="#fbbf24" /><text x="160" y="75" text-anchor="middle" fill="#fde68a" font-size="12" font-weight="700" font-family="ui-sans-serif">Parça</text>
                <rect x="238" y="50" width="78" height="40" rx="10" fill="#0f172a" stroke="#34d399" /><text x="277" y="75" text-anchor="middle" fill="#6ee7b7" font-size="12" font-weight="700" font-family="ui-sans-serif">Muadil</text>
                <path d="M82 70 H121" stroke="#64748b" stroke-width="2" /><path d="M82 70 H121" stroke="#7dd3fc" stroke-width="2" class="calc-flow" style="animation-duration:1.2s" />
                <path d="M199 70 H238" stroke="#34d399" stroke-width="2" stroke-dasharray="4 3" />
                <text x="101" y="62" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="ui-monospace">N:N</text>
                <text x="218" y="62" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="ui-monospace">⇄</text>
            </svg>

            <p class="text-xs font-bold uppercase tracking-[.25em] text-amber-300">Pro</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight">Çapraz referans</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-300">Telefonun CPU, bellek ve entegre parçaları; aynı parçanın bulunduğu diğer telefonlar ve yerine takılabilecek muadiller. Parçalar <a href="{{ route('parts.index') }}" wire:navigate class="font-semibold text-amber-200 underline decoration-amber-200/40 hover:decoration-amber-200">Parça ve stok</a> listesiyle aynı kayıttır.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ([['Parça', $counts['parts']], ['Eşlenen cihaz', $counts['devices']], ['Muadillik', $counts['compat']]] as [$l, $v])
                    <div class="rounded-xl bg-white/5 px-4 py-2 ring-1 ring-white/10"><p class="text-xl font-black">{{ $v }}</p><p class="text-[11px] text-slate-400">{{ $l }}</p></div>
                @endforeach
            </div>
        </section>

        {{-- Araç çubuğu --}}
        <div class="flex flex-col gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200 lg:flex-row lg:items-center dark:bg-slate-800 dark:ring-white/10">
            <div class="inline-flex shrink-0 rounded-lg bg-slate-100 p-1 dark:bg-white/5" role="tablist">
                @foreach (['devices' => 'Cihazlar', 'parts' => 'Parçalar'] as $k => $l)
                    <button type="button" role="tab" aria-selected="{{ $view === $k ? 'true' : 'false' }}" wire:click="$set('view', '{{ $k }}')" @class(['rounded-md px-3 py-1.5 text-sm font-semibold', 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' => $view === $k, 'text-slate-500' => $view !== $k])>{{ $l }}</button>
                @endforeach
            </div>
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ $view === 'parts' ? 'Parça kodu, ad, üretici ya da cihaz (SM8550, LPDDR5, garnet)' : 'Cihaz, kod adı ya da parça kodu' }}"
                    class="block w-full rounded-lg border-0 bg-slate-100 py-2 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-600 dark:bg-white/5 dark:text-white">
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @foreach (['txt' => 'Metin', 'xlsx' => 'Excel', 'json' => 'JSON'] as $fmt => $label)
                    <a @if ($selected) href="{{ route('cross-reference.selection', ['format' => $fmt, 'view' => $view, 'ids' => implode(',', $selected)]) }}" @endif
                        title="{{ $selected ? 'Seçilen '.count($selected).' satırı '.$label.' olarak indir' : 'Önce satır seçin' }}"
                        @class([$exportBtn, $selected ? $exportOn : $exportOff])>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ $label }}
                    </a>
                @endforeach
                <x-ui.button x-on:click="Livewire.dispatch('create-ref-part', { type: {{ \Illuminate\Support\Js::from($type ?: null) }} })" icon="M12 4.5v15m7.5-7.5h-15">
                    Parça <kbd class="hidden rounded bg-white/20 px-1.5 text-[10px] sm:inline">N</kbd>
                </x-ui.button>
            </div>
        </div>

        @if ($selected)
            <div class="flex flex-wrap items-center gap-3 rounded-xl bg-indigo-50 px-4 py-2.5 text-sm ring-1 ring-indigo-200 dark:bg-indigo-500/10 dark:ring-indigo-500/30">
                <span class="font-semibold text-indigo-800 dark:text-indigo-200">{{ count($selected) }} {{ $view === 'devices' ? 'cihaz' : 'parça' }} seçildi</span>
                <span class="text-xs text-indigo-700/80 dark:text-indigo-200/70">Metin, Excel ya da JSON olarak dışa aktarın · sayfalar arasında seçim korunur</span>
                <button type="button" wire:click="clearSelection" class="ms-auto text-xs font-semibold text-indigo-700 hover:underline dark:text-indigo-300">Seçimi temizle</button>
            </div>
        @endif

        <x-scroll-row track="gap-1.5 py-0.5" label="Parça türü">
            <button type="button" wire:click="$set('type', '')" aria-pressed="{{ $type === '' ? 'true' : 'false' }}" @class([$chip, $type === '' ? $chipOn : $chipOff])>Tüm türler</button>
            @foreach (ComponentType::cases() as $t)
                <button type="button" wire:click="$set('type', '{{ $t->value }}')" aria-pressed="{{ $type === $t->value ? 'true' : 'false' }}" @class([$chip, $type === $t->value ? $chipOn : $chipOff])>
                    {{ $t->label() }} @if ($n = $typeCounts[$t->value] ?? 0) <span class="opacity-60">{{ $n }}</span> @endif
                </button>
            @endforeach
        </x-scroll-row>

        <div wire:loading.class="opacity-60" wire:target="search,type,view,gotoPage,nextPage,previousPage,delete">
            @if ($view === 'devices')
                @if ($devices->isEmpty())
                    <x-ui.empty emoji="📱" title="{{ $search || $type ? 'Eşleşen cihaz yok' : 'Henüz cihaz yok' }}" description="Cihaz modelleri sayfasındaki kayıtlar burada listelenir; bir cihaza tıklayıp parçalarını ekleyin ya da örnek veriyi yükleyin.">
                        <x-ui.button variant="warning" :href="route('settings', 'capraz-referans')">Ayarlar: örnek veri / JSON içe aktar</x-ui.button>
                    </x-ui.empty>
                @else
                    @php $pageIds = $devices->pluck('id')->all(); $allOnPage = ! array_diff($pageIds, $selected); @endphp
                    <label class="mb-2 inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                        <input type="checkbox" @checked($allOnPage) wire:click="togglePage(@js($pageIds))" class="rounded border-slate-300 text-indigo-600"> Sayfadaki tüm cihazları seç
                    </label>
                    <div data-list class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($devices as $d)
                            <div wire:key="dev-{{ $d->id }}" role="button" tabindex="0"
                                x-on:click="Livewire.dispatch('show-device-parts', { id: {{ $d->id }} })" x-on:keydown.enter="Livewire.dispatch('show-device-parts', { id: {{ $d->id }} })"
                                @class(['flex cursor-pointer flex-col rounded-2xl bg-white p-4 text-left shadow-sm ring-1 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800',
                                    'ring-2 ring-indigo-500' => in_array($d->id, $selected), 'ring-slate-200 dark:ring-white/10' => ! in_array($d->id, $selected)])>
                                <div class="flex w-full items-center gap-3">
                                    <input type="checkbox" value="{{ $d->id }}" wire:model.live="selected" x-on:click.stop aria-label="{{ $d->model_name }} seç" class="shrink-0 rounded border-slate-300 text-indigo-600">
                                    @if ($d->image)<img src="{{ $d->image }}" alt="" class="h-11 w-11 shrink-0 rounded-lg bg-slate-50 object-contain dark:bg-white/5">@endif
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-bold text-slate-900 dark:text-white" title="{{ $d->brand }} {{ $d->model_name }}">{{ $d->brand }} {{ $d->model_name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $d->codename ?: '-' }}{{ $d->model_number ? ' · '.$d->model_number : '' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300" title="Parça sayısı">{{ $d->parts_count }}</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @forelse ($d->parts->take(6) as $c)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-1.5 py-0.5 text-[11px] ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                                            @if ($c->type)<span class="rounded px-1 text-[9px] font-black {{ $c->type->badge() }}">{{ $c->type->short() }}</span>@endif
                                            <span class="font-mono text-slate-700 dark:text-slate-200">{{ $c->ref() }}</span>
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">Parça eklenmedi - eklemek için tıklayın</span>
                                    @endforelse
                                    @if ($d->parts_count > 6)<span class="px-1 text-[11px] text-slate-500">+{{ $d->parts_count - 6 }}</span>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $devices->links() }}</div>
                @endif
            @else
                @if ($parts->isEmpty())
                    <x-ui.empty emoji="🧩" title="{{ $search || $type ? 'Eşleşen parça yok' : 'Çapraz referans kütüphaneniz boş' }}" description="Parça ekleyin ya da popüler cihazların yonga seti verisini Ayarlar'dan yükleyin.">
                        <x-ui.button variant="warning" :href="route('settings', 'capraz-referans')">Ayarlar: örnek veri / JSON içe aktar</x-ui.button>
                        <x-ui.button x-on:click="Livewire.dispatch('create-ref-part')">Parça ekle</x-ui.button>
                    </x-ui.empty>
                @else
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-white/5">
                                <tr>
                                    @php $pageIds = $parts->pluck('id')->all(); $allOnPage = ! array_diff($pageIds, $selected); @endphp
                                    <th class="w-10 py-3 pl-4"><input type="checkbox" @checked($allOnPage) wire:click="togglePage(@js($pageIds))" aria-label="Sayfadaki tümünü seç" class="rounded border-slate-300 text-indigo-600"></th>
                                    <th class="px-4 py-3">Parça</th>
                                    <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">Bulunduğu cihazlar</th>
                                    <th class="hidden px-4 py-3 lg:table-cell">Muadiller</th>
                                    <th class="px-4 py-3 text-center">Stok</th>
                                    <th class="px-4 py-3"><span class="sr-only">İşlemler</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                @foreach ($parts as $c)
                                    @php $compat = $c->compatibleTo->concat($c->compatibleFrom); @endphp
                                    <tr wire:key="part-{{ $c->id }}" @class(['group cursor-pointer', 'bg-indigo-50/60 dark:bg-indigo-500/10' => in_array($c->id, $selected), 'hover:bg-slate-50 dark:hover:bg-white/5' => ! in_array($c->id, $selected)]) x-on:click="Livewire.dispatch('open-part', { id: {{ $c->id }} })">
                                        <td class="py-3 pl-4" x-on:click.stop><input type="checkbox" value="{{ $c->id }}" wire:model.live="selected" aria-label="{{ $c->ref() }} seç" class="rounded border-slate-300 text-indigo-600"></td>
                                        <td class="w-full max-w-0 px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                @if ($c->type)<span class="shrink-0 rounded-md px-2 py-1 text-[10px] font-black {{ $c->type->badge() }}">{{ $c->type->short() }}</span>@endif
                                                <div class="min-w-0" title="{{ $c->label() }}">
                                                    <div x-on:click.stop><x-copy-code :code="$c->ref()" class="text-sm" /></div>
                                                    <p class="truncate text-xs text-slate-500">{{ $c->code && $c->name !== $c->code ? $c->name : '' }}{{ $c->manufacturer ? ' · '.$c->manufacturer : '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden max-w-80 px-4 py-3 md:table-cell">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($c->products->take(3) as $p)
                                                    <span class="max-w-40 truncate rounded-md bg-sky-50 px-2 py-0.5 text-xs text-sky-800 dark:bg-sky-500/10 dark:text-sky-200" title="{{ $p->brand }} {{ $p->model_name }}">{{ $p->model_name }}</span>
                                                @endforeach
                                                @if ($c->products_count > 3)<span class="px-1 text-xs text-slate-500">+{{ $c->products_count - 3 }}</span>@endif
                                                @if (! $c->products_count)<span class="text-xs text-slate-400">Bağımsız parça</span>@endif
                                            </div>
                                        </td>
                                        <td class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">
                                            @forelse ($compat->take(2) as $o)
                                                <span class="me-1 rounded-md bg-emerald-50 px-2 py-0.5 font-mono text-xs text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">⇄ {{ $o->ref() }}</span>
                                            @empty
                                                <span class="text-xs text-slate-400">-</span>
                                            @endforelse
                                            @if ($compat->count() > 2)<span class="text-xs text-slate-500">+{{ $compat->count() - 2 }}</span>@endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            <span @class(['rounded-md px-2 py-0.5 font-mono text-xs font-bold', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $c->stock > 0, 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400' => $c->stock <= 0])>{{ $c->stock }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right" x-on:click.stop>
                                            <button type="button" wire:click="delete({{ $c->id }})" class="rounded-lg px-2 py-1 text-xs font-semibold text-rose-600 opacity-0 hover:bg-rose-50 group-hover:opacity-100 focus:opacity-100 dark:hover:bg-rose-500/10">Sil</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $parts->links() }}</div>
                @endif
            @endif
        </div>

        {{-- Çekmeceler: parça düzenleyici cihaz çekmecesinin ÜSTÜNDE açılır (DOM'da sonra gelir) --}}
        <x-drawer name="device-parts" title="Telefonun parçaları" subtitle="Cihazdaki SoC, bellek ve entegreler; her parçanın muadilleri." width="sm:max-w-3xl">
            <livewire:cross-reference.device-parts />
        </x-drawer>
        <x-drawer name="part-editor" title="Parça" subtitle="Bilgiler, bulunduğu cihazlar ve muadiller." width="sm:max-w-3xl">
            <livewire:cross-reference.part-editor />
        </x-drawer>
    </div>
</div>
