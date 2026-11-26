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
                @php $exportBtn = 'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold ring-1 transition'; @endphp
                <a @if ($selected) href="{{ route('cross-reference.selection', ['format' => 'txt', 'view' => $view, 'ids' => implode(',', $selected)]) }}" @endif
                    title="{{ $selected ? 'Seçilen '.count($selected).' satırı sabit aralıklı metin tablo olarak indir' : 'Önce satır seçin' }}"
                    @class([$exportBtn, 'text-slate-700 ring-slate-200 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5' => $selected, 'pointer-events-none text-slate-400 ring-slate-200 opacity-60 dark:ring-white/10' => ! $selected])>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    Metin olarak dışa aktar
                </a>
                <a @if ($selected) href="{{ route('cross-reference.selection', ['format' => 'xlsx', 'view' => $view, 'ids' => implode(',', $selected)]) }}" @endif
                    title="{{ $selected ? 'Seçilen '.count($selected).' satırı Excel olarak indir' : 'Önce satır seçin' }}"
                    @class([$exportBtn, 'text-emerald-700 ring-emerald-300 hover:bg-emerald-50 dark:text-emerald-300 dark:ring-emerald-500/30 dark:hover:bg-emerald-500/10' => $selected, 'pointer-events-none text-slate-400 ring-slate-200 opacity-60 dark:ring-white/10' => ! $selected])>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" /></svg>
                    Excel olarak dışa aktar
                </a>
                <button type="button" x-on:click="Livewire.dispatch('create-component', { type: @js($type ?: null) })"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Bileşen <kbd class="hidden rounded bg-white/20 px-1.5 text-[10px] sm:inline">N</kbd>
                </button>
            </div>
        </div>

        @if ($selected)
            <div class="flex flex-wrap items-center gap-3 rounded-xl bg-indigo-50 px-4 py-2.5 text-sm ring-1 ring-indigo-200 dark:bg-indigo-500/10 dark:ring-indigo-500/30">
                <span class="font-semibold text-indigo-800 dark:text-indigo-200">{{ count($selected) }} {{ $view === 'devices' ? 'cihaz' : 'bileşen' }} seçildi</span>
                <span class="text-xs text-indigo-700/80 dark:text-indigo-200/70">Metin ya da Excel olarak dışa aktarın · sayfalar arasında seçim korunur</span>
                <button type="button" wire:click="clearSelection" class="ms-auto text-xs font-semibold text-indigo-700 hover:underline dark:text-indigo-300">Seçimi temizle</button>
            </div>
        @endif

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
                            <a href="{{ route('settings', 'capraz-referans') }}" wire:navigate class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-amber-950 hover:bg-amber-400">Ayarlar: örnek veri / CSV içe aktar</a>
                            <button type="button" x-on:click="Livewire.dispatch('create-component')" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Bileşen ekle</button>
                        </div>
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-white/5">
                                <tr>
                                    @php $pageIds = $components->pluck('id')->all(); $allOnPage = ! array_diff($pageIds, $selected); @endphp
                                    <th class="w-10 py-3 pl-4"><input type="checkbox" @checked($allOnPage) wire:click="togglePage(@js($pageIds))" aria-label="Sayfadaki tümünü seç" class="rounded border-slate-300 text-indigo-600"></th>
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
                                    <tr wire:key="cmp-{{ $c->id }}" @class(['group cursor-pointer', 'bg-indigo-50/60 dark:bg-indigo-500/10' => in_array($c->id, $selected), 'hover:bg-slate-50 dark:hover:bg-white/5' => ! in_array($c->id, $selected)]) x-on:click="Livewire.dispatch('edit-component', { id: {{ $c->id }} })">
                                        <td class="py-3 pl-4" x-on:click.stop><input type="checkbox" value="{{ $c->id }}" wire:model.live="selected" aria-label="{{ $c->code }} seç" class="rounded border-slate-300 text-indigo-600"></td>
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
                    @php $pageIds = $devices->pluck('id')->all(); $allOnPage = ! array_diff($pageIds, $selected); @endphp
                    <label class="mb-2 inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                        <input type="checkbox" @checked($allOnPage) wire:click="togglePage(@js($pageIds))" class="rounded border-slate-300 text-indigo-600"> Sayfadaki tüm cihazları seç
                    </label>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($devices as $d)
                            <div wire:key="dev-{{ $d->id }}" role="button" tabindex="0" x-on:click="Livewire.dispatch('show-device-components', { id: {{ $d->id }} })"
                                @class(['flex cursor-pointer flex-col rounded-2xl bg-white p-4 text-left shadow-sm ring-1 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800',
                                    'ring-2 ring-indigo-500' => in_array($d->id, $selected), 'ring-slate-200 dark:ring-white/10' => ! in_array($d->id, $selected)])>
                                <div class="flex w-full items-center gap-3">
                                    <input type="checkbox" value="{{ $d->id }}" wire:model.live="selected" x-on:click.stop aria-label="{{ $d->model_name }} seç" class="shrink-0 rounded border-slate-300 text-indigo-600">
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
                            </div>
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

    </div>
</div>
