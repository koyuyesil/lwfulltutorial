@php
    $sorts = ['newest' => 'En yeni', 'brand' => 'Markaya göre', 'popular' => 'En çok servis alan'];
    $palette = ['from-sky-500 to-blue-600', 'from-indigo-500 to-violet-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-slate-600 to-slate-800', 'from-fuchsia-500 to-purple-600', 'from-cyan-500 to-sky-600'];
    $brandColor = fn ($brand) => $palette[crc32(mb_strtolower((string) $brand)) % count($palette)];
    $isFiltered = $search !== '' || $brand !== '' || $sort !== 'newest';
@endphp

<div class="mt-6 space-y-4">
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat label="Model" :value="$stats['total']" tone="indigo" />
        <x-ui.stat label="Marka" :value="$stats['brands']" tone="violet" />
        <x-ui.stat label="Kayıtlı müşteri cihazı" :value="$stats['devices']" tone="sky" />
        <x-ui.stat label="En çok servis alan" :value="$stats['top']?->display_name ?? '—'" :hint="$stats['top'] ? $stats['top']->tickets_count . ' servis kaydı' : 'Henüz veri yok'" tone="amber" />
    </div>

    <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
        <x-ui.search wire:model.live.debounce.300ms="search" placeholder="Marka, model, model no…" />

        <div class="ms-auto flex items-center gap-2">
            @if ($isFiltered)
                <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Temizle</button>
            @endif
            <select wire:model.live="sort" aria-label="Sırala"
                class="rounded-lg border-0 bg-slate-100 py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-600 dark:bg-white/5 dark:text-slate-200">
                @foreach ($sorts as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex rounded-lg bg-slate-100 p-0.5 dark:bg-white/5" role="group" aria-label="Görünüm">
                @foreach (['cards' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z', 'table' => 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5'] as $mode => $icon)
                    <button type="button" wire:click="$set('view', '{{ $mode }}')" title="{{ $mode === 'table' ? 'Kompakt liste' : 'Kartlar' }}"
                        @class(['rounded-md p-1.5 transition', 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' => $view === $mode, 'text-slate-400 hover:text-slate-600' => $view !== $mode])>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Marka filtreleri --}}
    @if ($brands->count() > 1)
        <div class="flex gap-1.5 overflow-x-auto pb-1">
            <button type="button" wire:click="$set('brand', '')" @class([
                'shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition',
                'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => $brand === '',
                'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10' => $brand !== '',
            ])>Tüm markalar</button>
            @foreach ($brands as $name => $count)
                <button type="button" wire:click="$set('brand', @js($name))" @class([
                    'inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition',
                    'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => $brand === $name,
                    'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10' => $brand !== $name,
                ])>
                    <span class="h-2 w-2 rounded-full bg-linear-to-br {{ $brandColor($name) }}"></span>
                    {{ $name }} <span class="opacity-60">{{ $count }}</span>
                </button>
            @endforeach
        </div>
    @endif

    <div wire:loading.class="opacity-60" wire:target="search,brand,sort,view,gotoPage,nextPage,previousPage">
        @if ($products->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-white/10 dark:bg-slate-800/50">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                @if ($isFiltered)
                    <p class="mt-3 font-semibold text-slate-800 dark:text-white">Aramanıza uyan model yok</p>
                    <button type="button" wire:click="clearFilters" class="mt-2 text-sm font-semibold text-indigo-600">Filtreleri temizle</button>
                @else
                    <p class="mt-3 font-semibold text-slate-800 dark:text-white">Kataloğunuz boş</p>
                    <p class="mt-1 text-sm text-slate-500">En sık servis verdiğiniz modelleri ekleyin; servis kaydı açarken hazır olsun.</p>
                    <button type="button" x-on:click="$dispatch('open-drawer', { name: 'product-form' }); Livewire.dispatch('create-product')"
                        class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Yeni model</button>
                    <a href="{{ route('settings', 'cihaz-modelleri') }}" wire:navigate
                        class="ms-2 mt-4 inline-block rounded-lg px-4 py-2 text-sm font-semibold text-indigo-600 ring-1 ring-indigo-200 hover:bg-indigo-50 dark:text-indigo-300 dark:ring-indigo-500/30">CSV'den içe aktar</a>
                @endif
            </div>
        @elseif ($view === 'cards')
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $product)
                    <article wire:key="product-{{ $product->id }}"
                        class="group relative flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-slate-800 dark:ring-white/10">
                        @if ($product->image)
                            <div class="relative flex h-36 items-center justify-center bg-slate-50 p-3 dark:bg-white/5">
                                <img src="{{ $product->image }}" alt="{{ $product->display_name }}" loading="lazy" class="h-full w-full object-contain">
                                <span class="absolute left-3 top-3 rounded-md bg-white/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-700 shadow-sm dark:bg-slate-900/90 dark:text-slate-200">{{ $product->brand }}</span>
                            </div>
                        @else
                            <div class="relative h-20 bg-linear-to-br {{ $brandColor($product->brand) }} p-4">
                                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-white/80">{{ $product->brand }}</span>
                                <span class="absolute -bottom-6 right-4 grid h-12 w-12 place-items-center rounded-xl bg-white text-lg font-black text-slate-800 shadow-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:text-white dark:ring-white/10">
                                    {{ mb_strtoupper(mb_substr($product->brand, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <div class="flex flex-1 flex-col p-4 pt-3">
                            <h3 @class(['font-bold text-slate-900 dark:text-white', 'pr-14' => ! $product->image])>{{ $product->model_name }}</h3>
                            <p class="font-mono text-xs text-slate-500">
                                {{ $product->model_number ?: '—' }}@if ($product->codename) · <span class="text-indigo-600 dark:text-indigo-300" title="Kod adı">{{ $product->codename }}</span>@endif
                            </p>
                            @if ($product->chipset || $product->release_year)
                                <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $product->chipset }}{{ $product->chipset && $product->release_year ? ' · ' : '' }}{{ $product->release_year }}</p>
                            @endif
                            @if ($product->description)
                                <p class="mt-2 line-clamp-2 text-xs text-slate-600 dark:text-slate-400">{{ $product->description }}</p>
                            @endif
                            <div class="mt-auto flex flex-wrap gap-1.5 pt-4 text-[11px] font-semibold">
                                <span class="rounded-md bg-slate-100 px-2 py-1 text-slate-600 dark:bg-white/10 dark:text-slate-300" title="Bu modelde kayıtlı müşteri cihazı">{{ $product->devices_count }} cihaz</span>
                                <a href="{{ route('tickets.index', ['ara' => $product->model_name, 'durum' => '']) }}" wire:navigate class="rounded-md bg-slate-100 px-2 py-1 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 dark:bg-white/10 dark:text-slate-300">{{ $product->tickets_count }} servis</a>
                                @if ($product->open_tickets_count)
                                    <span class="rounded-md bg-amber-100 px-2 py-1 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300">{{ $product->open_tickets_count }} açık</span>
                                @endif
                                @if ($product->board_ids_count)
                                    <span class="rounded-md bg-violet-100 px-2 py-1 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">{{ $product->board_ids_count }} board ID</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex border-t border-slate-100 text-xs font-semibold dark:border-white/5">
                            <button type="button" x-on:click="$dispatch('open-drawer', { name: 'product-form' }); Livewire.dispatch('edit-product', { id: {{ $product->id }} })"
                                class="flex-1 py-2.5 text-slate-600 transition hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-white/5">Düzenle</button>
                            <button type="button" wire:click="delete({{ $product->id }})"
                                wire:confirm="{{ $product->display_name }} silinecek. Bu modele bağlı {{ $product->devices_count }} müşteri cihazı ve servis kayıtları da silinir. Emin misiniz?"
                                class="flex-1 border-s border-slate-100 py-2.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:border-white/5 dark:hover:bg-rose-500/10">Sil</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-white/5 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Model</th>
                            <th class="hidden px-4 py-3 md:table-cell">Model no / kod adı</th>
                            <th class="hidden px-4 py-3 lg:table-cell">Üretici</th>
                            <th class="px-4 py-3 text-right">Cihaz</th>
                            <th class="px-4 py-3 text-right">Servis</th>
                            <th class="px-4 py-3"><span class="sr-only">İşlemler</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($products as $product)
                            <tr wire:key="product-row-{{ $product->id }}" class="hover:bg-slate-50 dark:hover:bg-white/5">
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-3">
                                        @if ($product->image)
                                            <img src="{{ $product->image }}" alt="" loading="lazy" class="h-8 w-8 shrink-0 rounded-lg bg-slate-50 object-contain ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                                        @else
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-linear-to-br text-xs font-bold text-white {{ $brandColor($product->brand) }}">{{ mb_strtoupper(mb_substr($product->brand, 0, 1)) }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $product->model_name }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $product->brand }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden px-4 py-2.5 font-mono text-xs text-slate-600 md:table-cell dark:text-slate-300">
                                    {{ $product->model_number ?: '—' }}
                                    @if ($product->codename) <span class="block text-indigo-600 dark:text-indigo-300">{{ $product->codename }}</span> @endif
                                </td>
                                <td class="hidden px-4 py-2.5 text-slate-600 lg:table-cell dark:text-slate-300">{{ $product->manufacturer }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ $product->devices_count }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">
                                    {{ $product->tickets_count }}
                                    @if ($product->open_tickets_count)
                                        <span class="ms-1 rounded bg-amber-100 px-1.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-500/15 dark:text-amber-300">{{ $product->open_tickets_count }} açık</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-2.5 text-right text-xs font-semibold">
                                    <button type="button" x-on:click="$dispatch('open-drawer', { name: 'product-form' }); Livewire.dispatch('edit-product', { id: {{ $product->id }} })" class="rounded-md px-2 py-1 text-indigo-600 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-500/10">Düzenle</button>
                                    <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="{{ $product->display_name }} ve bağlı kayıtlar silinsin mi?" class="rounded-md px-2 py-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">Sil</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>{{ $products->links() }}</div>
</div>
