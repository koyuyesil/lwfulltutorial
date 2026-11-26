@use('App\Enums\ComponentType')
@php
    $chip = 'inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition';
    $chipOn = 'bg-slate-900 text-white ring-slate-900 dark:bg-white dark:text-slate-900 dark:ring-white';
    $chipOff = 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10';
@endphp

<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Premium /</span> Çapraz referans</h2>
</x-slot>

<div>
    <x-tools-tabs />

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8"
        x-on:keydown.n.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName) && ! $event.metaKey && ! $event.ctrlKey) Livewire.dispatch('create-component')">

        {{-- Başlık --}}
        <section class="relative isolate overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl ring-1 ring-white/10 sm:p-8">
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-20 [background-image:linear-gradient(#334155_1px,transparent_1px),linear-gradient(90deg,#334155_1px,transparent_1px)] [background-size:28px_28px]"></div>
            <div class="pointer-events-none absolute -right-16 -top-20 -z-10 h-72 w-72 rounded-full bg-amber-500/25 blur-3xl"></div>
            {{-- İlişki şeması --}}
            <svg class="pointer-events-none absolute right-8 top-1/2 -z-10 hidden h-36 w-80 -translate-y-1/2 opacity-80 lg:block" viewBox="0 0 320 140" fill="none" aria-hidden="true">
                <rect x="4" y="50" width="78" height="40" rx="10" fill="#0f172a" stroke="#38bdf8" /><text x="43" y="75" text-anchor="middle" fill="#7dd3fc" font-size="12" font-weight="700" font-family="ui-sans-serif">Cihaz</text>
                <rect x="121" y="50" width="78" height="40" rx="10" fill="#0f172a" stroke="#fbbf24" /><text x="160" y="75" text-anchor="middle" fill="#fde68a" font-size="12" font-weight="700" font-family="ui-sans-serif">Bileşen</text>
                <rect x="238" y="8" width="78" height="40" rx="10" fill="#0f172a" stroke="#34d399" /><text x="277" y="33" text-anchor="middle" fill="#6ee7b7" font-size="11" font-weight="700" font-family="ui-sans-serif">Uyumlu</text>
                <rect x="238" y="92" width="78" height="40" rx="10" fill="#0f172a" stroke="#a78bfa" /><text x="277" y="117" text-anchor="middle" fill="#c4b5fd" font-size="11" font-weight="700" font-family="ui-sans-serif">Stok parça</text>
                <path d="M82 70 H121" stroke="#64748b" stroke-width="2" /><path d="M82 70 H121" stroke="#7dd3fc" stroke-width="2" class="calc-flow" style="animation-duration:1.2s" />
                <path d="M199 62 L238 30" stroke="#34d399" stroke-width="2" stroke-dasharray="4 3" /><path d="M199 78 L238 110" stroke="#a78bfa" stroke-width="2" />
                <text x="101" y="62" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="ui-monospace">N:N</text>
                <text x="228" y="56" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="ui-monospace">↔</text>
            </svg>

            <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-[.25em] text-amber-300"><svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.85L10 14.9l-5.25 2.75 1-5.85L1.5 7.7l5.9-.9L10 1.5Z"/></svg> Premium</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight">Çapraz referans</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-300">Cihazdaki CPU, bellek ve entegre kodları; aynı bileşenin bulunduğu diğer cihazlar ve yerine takılabilecek uyumlu bileşenler. Bir RAM hem başka bir RAM ile uyumlu olabilir hem de başka telefonlarda bulunabilir.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ([['Bileşen', $counts['components']], ['Eşlenen cihaz', $counts['devices']], ['Uyumluluk', $counts['compat']]] as [$l, $v])
                    <div class="rounded-xl bg-white/5 px-4 py-2 ring-1 ring-white/10"><p class="text-xl font-black">{{ $v }}</p><p class="text-[11px] text-slate-400">{{ $l }}</p></div>
                @endforeach
            </div>
        </section>

        {{-- Araç çubuğu --}}
        <div class="flex flex-col gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200 lg:flex-row lg:items-center dark:bg-slate-800 dark:ring-white/10">
            <div class="inline-flex shrink-0 rounded-lg bg-slate-100 p-1 dark:bg-white/5">
                <button type="button" wire:click="$set('view', 'components')" @class(['rounded-md px-3 py-1.5 text-sm font-semibold', 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' => $view === 'components', 'text-slate-500' => $view !== 'components'])>Bileşenler</button>
                <button type="button" wire:click="$set('view', 'devices')" @class(['rounded-md px-3 py-1.5 text-sm font-semibold', 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' => $view === 'devices', 'text-slate-500' => $view !== 'devices'])>Cihazlar</button>
            </div>
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ $view === 'components' ? 'Parça kodu, ad, üretici ya da cihaz (SM8550, LPDDR5, garnet)…' : 'Cihaz, kod adı ya da bileşen kodu…' }}"
                    class="block w-full rounded-lg border-0 bg-slate-100 py-2 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-600 dark:bg-white/5 dark:text-white">
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('cross-reference.export') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5">CSV indir</a>
                <button type="button" x-on:click="$dispatch('open-drawer', { name: 'xref-import' })" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5">İçe aktar</button>
                <button type="button" x-on:click="Livewire.dispatch('create-component', { type: @js($type ?: null) })"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Bileşen <kbd class="hidden rounded bg-white/20 px-1.5 text-[10px] sm:inline">N</kbd>
                </button>
            </div>
        </div>

        <x-scroll-row track="gap-1.5 py-0.5" label="Bileşen türü">
            <button type="button" wire:click="$set('type', '')" aria-pressed="{{ $type === '' ? 'true' : 'false' }}" @class([$chip, $type === '' ? $chipOn : $chipOff])>Tüm türler</button>
            @foreach (ComponentType::cases() as $t)
                <button type="button" wire:click="$set('type', '{{ $t->value }}')" aria-pressed="{{ $type === $t->value ? 'true' : 'false' }}" @class([$chip, $type === $t->value ? $chipOn : $chipOff])>
                    {{ $t->label() }} @if ($n = $typeCounts[$t->value] ?? 0) <span class="opacity-60">{{ $n }}</span> @endif
                </button>
            @endforeach
        </x-scroll-row>

        <div wire:loading.class="opacity-60" wire:target="search,type,view,gotoPage,nextPage,previousPage,delete">
            @if ($view === 'components')
                @if ($components->isEmpty())
                    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-14 text-center dark:border-white/10 dark:bg-slate-800/50">
                        <p class="text-4xl">🧬</p>
                        <p class="mt-3 font-semibold text-slate-800 dark:text-white">{{ $search || $type ? 'Eşleşen bileşen yok' : 'Çapraz referans kütüphaneniz boş' }}</p>
                        <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">Bileşen ekleyin ya da popüler cihazların yonga seti verisini tek tıkla yükleyin.</p>
                        <div class="mt-4 flex justify-center gap-2">
                            <button type="button" wire:click="loadSample" wire:loading.attr="disabled" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-amber-950 hover:bg-amber-400">Örnek veriyi yükle</button>
                            <button type="button" x-on:click="Livewire.dispatch('create-component')" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Bileşen ekle</button>
                        </div>
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-3">Bileşen</th>
                                    <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">Bulunduğu cihazlar</th>
                                    <th class="hidden px-4 py-3 lg:table-cell">Uyumlu</th>
                                    <th class="px-4 py-3 text-center">Stok</th>
                                    <th class="px-4 py-3"><span class="sr-only">İşlemler</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                @foreach ($components as $c)
                                    @php $compat = $c->compatibleTo->concat($c->compatibleFrom); @endphp
                                    <tr wire:key="cmp-{{ $c->id }}" class="group cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5" x-on:click="Livewire.dispatch('edit-component', { id: {{ $c->id }} })">
                                        <td class="w-full max-w-0 px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <span class="shrink-0 rounded-md px-2 py-1 text-[10px] font-black {{ $c->type->badge() }}">{{ $c->type->short() }}</span>
                                                <div class="min-w-0" title="{{ $c->label() }}">
                                                    <div x-on:click.stop><x-copy-code :code="$c->code" class="text-sm" /></div>
                                                    <p class="truncate text-xs text-slate-500">{{ $c->name ?: '—' }}{{ $c->manufacturer ? ' · '.$c->manufacturer : '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden max-w-80 px-4 py-3 md:table-cell">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($c->products->take(3) as $p)
                                                    <span class="max-w-40 truncate rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-700 dark:bg-white/10 dark:text-slate-200" title="{{ $p->brand }} {{ $p->model_name }}">{{ $p->model_name }}</span>
                                                @endforeach
                                                @if ($c->products_count > 3)<span class="px-1 text-xs text-slate-500">+{{ $c->products_count - 3 }}</span>@endif
                                                @if (! $c->products_count)<span class="text-xs text-slate-400">Cihaz bağlanmadı</span>@endif
                                            </div>
                                        </td>
                                        <td class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">
                                            @forelse ($compat->take(2) as $o)
                                                <span class="me-1 rounded-md bg-emerald-50 px-2 py-0.5 font-mono text-xs text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $o->code }}</span>
                                            @empty
                                                <span class="text-xs text-slate-400">—</span>
                                            @endforelse
                                            @if ($compat->count() > 2)<span class="text-xs text-slate-500">+{{ $compat->count() - 2 }}</span>@endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center text-xs">{{ $c->parts_count ? $c->parts_count.' parça' : '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right" x-on:click.stop>
                                            <button type="button" wire:click="delete({{ $c->id }})" wire:confirm="{{ $c->code }} ve tüm bağlantıları silinsin mi?"
                                                class="rounded-lg px-2 py-1 text-xs font-semibold text-rose-600 opacity-0 hover:bg-rose-50 group-hover:opacity-100 focus:opacity-100 dark:hover:bg-rose-500/10">Sil</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $components->links() }}</div>
                @endif
            @else
                @if ($devices->isEmpty())
                    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-14 text-center dark:border-white/10 dark:bg-slate-800/50">
                        <p class="text-4xl">📱</p>
                        <p class="mt-3 font-semibold text-slate-800 dark:text-white">Eşleşen cihaz yok</p>
                        <p class="mt-1 text-sm text-slate-500">Cihaz modelleri sayfasındaki kayıtlar burada listelenir.</p>
                    </div>
                @else
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($devices as $d)
                            <button type="button" wire:key="dev-{{ $d->id }}" x-on:click="Livewire.dispatch('show-device-components', { id: {{ $d->id }} })"
                                class="flex flex-col rounded-2xl bg-white p-4 text-left shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800 dark:ring-white/10">
                                <div class="flex w-full items-center gap-3">
                                    @if ($d->image)<img src="{{ $d->image }}" alt="" class="h-11 w-11 shrink-0 rounded-lg bg-slate-50 object-contain dark:bg-white/5">@endif
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-bold text-slate-900 dark:text-white" title="{{ $d->brand }} {{ $d->model_name }}">{{ $d->brand }} {{ $d->model_name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $d->codename ?: '—' }}{{ $d->model_number ? ' · '.$d->model_number : '' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $d->components_count }}</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    @forelse ($d->components->take(6) as $c)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-1.5 py-0.5 text-[11px] ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                                            <span class="rounded px-1 text-[9px] font-black {{ $c->type->badge() }}">{{ $c->type->short() }}</span>
                                            <span class="font-mono text-slate-700 dark:text-slate-200">{{ $c->code }}</span>
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">Bileşen eklenmedi — eklemek için tıklayın</span>
                                    @endforelse
                                </div>
                            </button>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $devices->links() }}</div>
                @endif
            @endif
        </div>

        {{-- Çekmeceler --}}
        <x-drawer name="component-editor" title="Bileşen" subtitle="Bilgiler, bulunduğu cihazlar ve aynı tablodan uyumlu bileşenler." width="sm:max-w-3xl">
            <livewire:cross-reference.component-editor />
        </x-drawer>
        <x-drawer name="device-components" title="Cihazın bileşenleri" subtitle="Cihazdaki SoC, bellek ve entegreler; her bileşenin uyumlu alternatifleri." width="sm:max-w-2xl">
            <livewire:cross-reference.device-components />
        </x-drawer>
        <x-drawer name="xref-import" title="Çapraz referans içe aktar" subtitle="CSV ya da hazır örnek veri.">
            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
                <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 px-6 py-10 text-center hover:border-slate-400 dark:border-white/15">
                    <span class="text-3xl">📄</span>
                    <span class="mt-2 text-sm font-semibold text-slate-800 dark:text-white">CSV dosyası seçin</span>
                    <span wire:loading wire:target="file" class="mt-2 text-xs font-semibold text-indigo-600">İçe aktarılıyor…</span>
                    <input type="file" accept=".csv,text/csv" wire:model="file" class="sr-only">
                </label>
                @error('file') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                <label class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="checkbox" wire:model="createDevices" class="mt-0.5 rounded border-slate-300 text-indigo-600">
                    <span>Katalogda olmayan cihazları "Marka &gt; Model" yazımından oluştur<br><span class="text-xs text-slate-500">Kapalıysa eşleşmeyen cihazlar raporda listelenir.</span></span>
                </label>
                <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:ring-amber-500/30">
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Örnek veri: popüler cihazların yonga setleri</p>
                    <p class="mt-1 text-xs text-amber-800 dark:text-amber-200">{{ count(\App\Support\SampleCrossReference::SOCS) }} SoC (Snapdragon, Dimensity, Exynos, Tensor, Kirin, Apple) ve örnek cihaz kataloğundaki modellere bağlantıları. Kodu doğrulanmamış olanlarda not vardır.</p>
                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click="loadSample" wire:loading.attr="disabled" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-amber-950 hover:bg-amber-400">Örnek veriyi yükle</button>
                        <a href="{{ route('cross-reference.sample') }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-amber-900 ring-1 ring-amber-300 hover:bg-amber-100 dark:text-amber-100 dark:ring-amber-500/40 dark:hover:bg-amber-500/10">Örnek CSV indir</a>
                    </div>
                </div>
                @if ($report)
                    <div class="rounded-xl bg-slate-50 p-4 text-sm ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                        <p class="font-semibold text-slate-900 dark:text-white">Son içe aktarma</p>
                        <ul class="mt-2 space-y-0.5 text-slate-600 dark:text-slate-300">
                            <li>{{ $report['components_created'] }} yeni, {{ $report['components_updated'] }} güncellenen bileşen</li>
                            <li>{{ $report['links'] }} cihaz bağlantısı · {{ $report['compat'] }} uyumluluk{{ $report['products_created'] ? ' · '.$report['products_created'].' yeni cihaz' : '' }}</li>
                        </ul>
                        @if ($report['unmatched'])
                            <details class="mt-2">
                                <summary class="cursor-pointer text-xs font-semibold text-amber-700 dark:text-amber-300">{{ count($report['unmatched']) }} eşleşmeyen kayıt</summary>
                                <p class="mt-1 text-xs text-slate-500">{{ implode(' · ', array_slice($report['unmatched'], 0, 60)) }}</p>
                            </details>
                        @endif
                    </div>
                @endif
                <details class="rounded-lg bg-slate-50 p-4 text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">
                    <summary class="cursor-pointer font-semibold">CSV sütunları</summary>
                    <ul class="mt-2 space-y-1">
                        <li><code class="font-mono text-indigo-600">type</code> — {{ collect(ComponentType::cases())->map->value->implode(' · ') }}</li>
                        <li><code class="font-mono text-indigo-600">code</code> — parça / model kodu (zorunlu) · <code class="font-mono text-indigo-600">name</code>, <code class="font-mono text-indigo-600">manufacturer</code>, <code class="font-mono text-indigo-600">notes</code></li>
                        <li><code class="font-mono text-indigo-600">specs</code> — "Bellek tipi=LPDDR5; Kapasite=8 GB"</li>
                        <li><code class="font-mono text-indigo-600">devices</code> — "Xiaomi &gt; Redmi Note 13 Pro [8/256]; garnet"</li>
                        <li><code class="font-mono text-indigo-600">compatible</code> — "ram:K3LK7K70BM-BGCP:pin; ram:H58G56AK6BX069:direct"</li>
                    </ul>
                </details>
            </div>
        </x-drawer>
    </div>
</div>
