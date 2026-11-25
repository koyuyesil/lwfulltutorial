<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Araçlar /</span> Hesaplayıcılar</h2>
</x-slot>

<div>
    <x-tools-tabs />

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" x-data="toolsApp(@js($initial))" wire:ignore
        x-on:keydown.slash.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.search.focus() }">

        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Hesaplayıcılar</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Atölye için elektronik hesaplamalar ve birim dönüştürücüler. Sonuçlar siz yazarken hesaplanır.</p>
            </div>
            <p class="text-xs text-slate-500">Değerleri <code class="rounded bg-slate-200 px-1 font-mono dark:bg-white/10">4k7</code> <code class="rounded bg-slate-200 px-1 font-mono dark:bg-white/10">100n</code> <code class="rounded bg-slate-200 px-1 font-mono dark:bg-white/10">2R2</code> <code class="rounded bg-slate-200 px-1 font-mono dark:bg-white/10">10µ</code> şeklinde yazabilirsiniz.</p>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[280px_1fr]">
            {{-- Araç listesi --}}
            <aside class="lg:sticky lg:top-20 lg:self-start">
                <button type="button" @click="mobileList = ! mobileList"
                    class="flex w-full items-center justify-between rounded-xl bg-white px-4 py-3 text-left shadow-sm ring-1 ring-slate-200 lg:hidden dark:bg-slate-800 dark:ring-white/10">
                    <span class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white"><span x-text="current?.icon"></span><span x-text="current?.title"></span></span>
                    <span class="text-xs font-semibold text-indigo-600">Araç değiştir ▾</span>
                </button>

                <div :class="mobileList ? 'block' : 'hidden lg:block'" class="mt-2 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 lg:mt-0 dark:bg-slate-800 dark:ring-white/10">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
                        <input x-ref="search" x-model="query" type="search" placeholder="Araç ara… ( / )"
                            @keydown.enter.prevent="filtered[0] && open(filtered[0].key)"
                            class="block w-full rounded-lg border-0 bg-slate-100 py-2 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-600 dark:bg-white/5 dark:text-white">
                    </div>

                    <nav class="mt-3 max-h-[70vh] space-y-4 overflow-y-auto" aria-label="Hesaplayıcılar">
                        <template x-for="(items, group) in groups" :key="group">
                            <div>
                                <p class="px-2 text-[11px] font-bold uppercase tracking-[0.15em] text-slate-400" x-text="group"></p>
                                <ul class="mt-1 space-y-0.5">
                                    <template x-for="tool in items" :key="group + tool.key">
                                        <li class="group flex items-center">
                                            <button type="button" @click="open(tool.key)" :aria-current="active === tool.key ? 'page' : null"
                                                :class="active === tool.key ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5'"
                                                class="flex min-w-0 flex-1 items-center gap-3 rounded-lg px-2 py-2 text-left text-sm font-medium transition">
                                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-md bg-slate-100 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-200" x-text="tool.icon"></span>
                                                <span class="truncate" x-text="tool.title"></span>
                                            </button>
                                            <button type="button" @click="toggleFavorite(tool.key)" :title="favorites.includes(tool.key) ? 'Favorilerden çıkar' : 'Favorilere ekle'"
                                                :class="favorites.includes(tool.key) ? 'text-amber-400' : 'text-slate-300 opacity-0 group-hover:opacity-100 dark:text-slate-600'"
                                                class="px-2 text-base transition hover:text-amber-400">★</button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                        <p x-show="filtered.length === 0" class="px-2 py-4 text-center text-sm text-slate-500">Eşleşen araç yok.</p>
                    </nav>
                </div>
            </aside>

            {{-- Seçili araç --}}
            <section class="min-w-0 rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                <header class="flex items-start gap-4 border-b border-slate-100 px-6 py-5 dark:border-white/5">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-linear-to-br from-sky-500 to-indigo-600 text-lg font-black text-white shadow-lg shadow-indigo-500/20" x-text="current?.icon"></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white" x-text="current?.title"></h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400" x-text="current?.desc"></p>
                    </div>
                    <button type="button" @click="toggleFavorite(active)" :class="favorites.includes(active) ? 'text-amber-400' : 'text-slate-300 hover:text-amber-400 dark:text-slate-600'"
                        class="text-2xl transition" :title="favorites.includes(active) ? 'Favorilerden çıkar' : 'Favorilere ekle'">★</button>
                </header>

                <div class="p-6">
                    @foreach (['ohm', 'divider', 'network', 'led', 'colors', 'smd', 'capcode', 'rc', 'reactance', 'timer555', 'db', 'battery', 'power', 'trace', 'awg', 'convert'] as $tool)
                        <template x-if="active === '{{ $tool }}'">
                            <div>@include('livewire.tools.calculators.' . $tool)</div>
                        </template>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
