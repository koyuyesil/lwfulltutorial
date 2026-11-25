@php
    // Grup renkleri (Tailwind sınıfları derlemede görünsün diye tam yazılır)
    $tones = [
        'sky' => ['grad' => 'from-sky-500 to-cyan-400', 'text' => 'text-sky-600 dark:text-sky-300', 'soft' => 'bg-sky-50 ring-sky-200 dark:bg-sky-500/10 dark:ring-sky-500/20', 'dot' => 'bg-sky-500'],
        'amber' => ['grad' => 'from-amber-500 to-orange-400', 'text' => 'text-amber-600 dark:text-amber-300', 'soft' => 'bg-amber-50 ring-amber-200 dark:bg-amber-500/10 dark:ring-amber-500/20', 'dot' => 'bg-amber-500'],
        'violet' => ['grad' => 'from-violet-500 to-fuchsia-400', 'text' => 'text-violet-600 dark:text-violet-300', 'soft' => 'bg-violet-50 ring-violet-200 dark:bg-violet-500/10 dark:ring-violet-500/20', 'dot' => 'bg-violet-500'],
        'emerald' => ['grad' => 'from-emerald-500 to-lime-400', 'text' => 'text-emerald-600 dark:text-emerald-300', 'soft' => 'bg-emerald-50 ring-emerald-200 dark:bg-emerald-500/10 dark:ring-emerald-500/20', 'dot' => 'bg-emerald-500'],
        'rose' => ['grad' => 'from-rose-500 to-orange-400', 'text' => 'text-rose-600 dark:text-rose-300', 'soft' => 'bg-rose-50 ring-rose-200 dark:bg-rose-500/10 dark:ring-rose-500/20', 'dot' => 'bg-rose-500'],
        'slate' => ['grad' => 'from-slate-600 to-slate-400', 'text' => 'text-slate-600 dark:text-slate-300', 'soft' => 'bg-slate-50 ring-slate-200 dark:bg-white/5 dark:ring-white/10', 'dot' => 'bg-slate-500'],
    ];
    $toolCount = collect($groups)->sum(fn ($g) => count($g['tools']));
@endphp

<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Araçlar /</span> Hesaplayıcılar</h2>
</x-slot>

<div>
    <x-tools-tabs />

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" x-data="toolsApp(@js($initial), @js($tools))" wire:ignore
        x-on:keydown.slash.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.search.focus() }">

        {{-- Üst bölüm --}}
        <section class="relative isolate overflow-hidden rounded-3xl bg-slate-950 px-6 py-8 text-white shadow-xl ring-1 ring-white/10 sm:px-10">
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-20 [background-image:linear-gradient(#334155_1px,transparent_1px),linear-gradient(90deg,#334155_1px,transparent_1px)] [background-size:28px_28px]"></div>
            <div class="pointer-events-none absolute -left-24 -top-24 -z-10 h-72 w-72 rounded-full bg-indigo-600/40 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 right-0 -z-10 h-72 w-96 rounded-full bg-cyan-500/25 blur-3xl"></div>
            {{-- Dekoratif devre yolu --}}
            <svg class="pointer-events-none absolute right-6 top-6 -z-10 hidden h-40 w-80 opacity-60 md:block" viewBox="0 0 320 160" fill="none" aria-hidden="true">
                <path d="M0 120 H90 l20 -20 H190 l20 20 H320" stroke="#22d3ee" stroke-opacity=".35" stroke-width="3" />
                <path d="M0 120 H90 l20 -20 H190 l20 20 H320" stroke="#67e8f9" stroke-width="3" class="calc-flow" style="animation-duration: 1.4s" />
                <path d="M60 30 H150 V100" stroke="#a78bfa" stroke-opacity=".4" stroke-width="3" />
                <rect x="210" y="22" width="70" height="46" rx="6" fill="#0f172a" stroke="#475569" />
                @foreach (range(0, 3) as $i)
                    <rect x="{{ 218 + $i * 16 }}" y="14" width="6" height="8" fill="#94a3b8" /><rect x="{{ 218 + $i * 16 }}" y="68" width="6" height="8" fill="#94a3b8" />
                @endforeach
                <text x="245" y="50" text-anchor="middle" fill="#e2e8f0" font-family="ui-monospace,monospace" font-size="12" font-weight="700">NE555</text>
                <circle cx="150" cy="100" r="5" fill="#f59e0b" />
                <circle cx="60" cy="30" r="5" fill="#a78bfa" />
            </svg>

            <p class="text-xs font-bold uppercase tracking-[.25em] text-cyan-300">Atölye laboratuvarı</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Elektronik hesaplayıcılar</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-300">{{ $toolCount }} araç, {{ count($groups) }} başlık. İlgili hesaplamalar alt alta; sonuçlar ve canlı devre / 3D görseller siz yazarken güncellenir.</p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative w-full max-w-md">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
                    <input x-ref="search" x-model="query" type="search" placeholder="Araç ara: led, 555, smd, kWh…"
                        @keydown.enter.prevent="tools.find((t) => matches(t.key)) && go(tools.find((t) => matches(t.key)).key)"
                        class="block w-full rounded-xl border-0 bg-white/10 py-3 pl-10 pr-12 text-sm text-white placeholder:text-slate-400 ring-1 ring-inset ring-white/15 backdrop-blur focus:bg-white/15 focus:ring-2 focus:ring-cyan-400">
                    <kbd class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded border border-white/20 px-1.5 font-mono text-[10px] text-slate-300">/</kbd>
                </div>
                <p class="text-xs text-slate-400">Değer yazımı:
                    @foreach (['4k7', '100n', '2R2', '10µ', '1,5M'] as $ex)
                        <code class="ml-1 rounded bg-white/10 px-1.5 py-0.5 font-mono text-cyan-200">{{ $ex }}</code>
                    @endforeach
                </p>
            </div>

            {{-- Grup kısayolları --}}
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($groups as $gKey => $g)
                    <button type="button" x-show="groupVisible('{{ $gKey }}')" @click="go('{{ array_key_first($g['tools']) }}')"
                        class="group inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-200 ring-1 ring-white/10 transition hover:bg-white/10">
                        <span class="h-2 w-2 rounded-full bg-linear-to-br {{ $tones[$g['tone']]['grad'] }}"></span>
                        {{ $g['title'] }}
                        <span class="rounded-full bg-white/10 px-1.5 text-[10px] text-slate-300">{{ count($g['tools']) }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Favoriler --}}
        <div x-show="favoriteTools.length && ! q" x-cloak class="mt-4 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-500">★ Favoriler</span>
            <template x-for="t in favoriteTools" :key="'fav' + t.key">
                <button type="button" @click="go(t.key)" class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20">
                    <span x-text="t.icon"></span><span x-text="t.title"></span>
                </button>
            </template>
        </div>

        <div class="mt-6 grid gap-8 lg:grid-cols-[230px_minmax(0,1fr)]">
            {{-- İçindekiler (kaydırmayı izler) --}}
            <aside class="lg:sticky lg:top-20 lg:self-start">
                <button type="button" @click="mobileList = ! mobileList"
                    class="flex w-full items-center justify-between rounded-xl bg-white px-4 py-3 text-left shadow-sm ring-1 ring-slate-200 lg:hidden dark:bg-slate-800 dark:ring-white/10">
                    <span class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white"><span x-text="current?.icon"></span><span x-text="current?.title"></span></span>
                    <span class="text-xs font-semibold text-indigo-600">İçindekiler ▾</span>
                </button>

                <nav :class="mobileList ? 'block' : 'hidden lg:block'" class="mt-2 max-h-[calc(100vh-7rem)] space-y-5 overflow-y-auto rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200 lg:mt-0 dark:bg-slate-800 dark:ring-white/10" aria-label="Hesaplayıcılar">
                    @foreach ($groups as $gKey => $g)
                        <div x-show="groupVisible('{{ $gKey }}')">
                            <p class="flex items-center gap-2 px-2 text-[11px] font-bold uppercase tracking-[.15em] {{ $tones[$g['tone']]['text'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $tones[$g['tone']]['dot'] }}"></span>{{ $g['title'] }}
                            </p>
                            <ul class="relative mt-1 space-y-0.5 border-l border-slate-200 pl-2 ml-2.5 dark:border-white/10">
                                @foreach ($g['tools'] as $key => [$title, , $icon])
                                    <li x-show="matches('{{ $key }}')" class="group flex items-center">
                                        <button type="button" @click="go('{{ $key }}')" :aria-current="active === '{{ $key }}' ? 'true' : null"
                                            :class="active === '{{ $key }}' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5'"
                                            class="relative flex min-w-0 flex-1 items-center gap-2.5 rounded-lg px-2 py-1.5 text-left text-sm font-medium transition">
                                            <span :class="active === '{{ $key }}' ? 'opacity-100' : 'opacity-0'" class="absolute -left-[13px] top-1/2 h-5 w-0.5 -translate-y-1/2 rounded-full bg-indigo-500 transition"></span>
                                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-md bg-slate-100 text-[11px] font-bold text-slate-600 dark:bg-white/10 dark:text-slate-200">{{ $icon }}</span>
                                            <span class="truncate">{{ $title }}</span>
                                        </button>
                                        <button type="button" @click="toggleFavorite('{{ $key }}')" :title="favorites.includes('{{ $key }}') ? 'Favorilerden çıkar' : 'Favorilere ekle'"
                                            :class="favorites.includes('{{ $key }}') ? 'text-amber-400' : 'text-slate-300 opacity-0 group-hover:opacity-100 dark:text-slate-600'"
                                            class="px-1.5 text-sm transition hover:text-amber-400">★</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                    <p x-show="matchCount === 0" x-cloak class="px-2 py-4 text-center text-sm text-slate-500">Eşleşen araç yok.</p>
                </nav>
            </aside>

            {{-- Gruplar: ilgili araçlar alt alta --}}
            <div class="min-w-0 space-y-12">
                @foreach ($groups as $gKey => $g)
                    @php $t = $tones[$g['tone']]; @endphp
                    <section x-show="groupVisible('{{ $gKey }}')" aria-labelledby="grup-{{ $gKey }}">
                        <header class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-linear-to-br {{ $t['grad'] }} text-sm font-black text-white shadow-lg">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="min-w-0">
                                <h2 id="grup-{{ $gKey }}" class="text-xl font-black tracking-tight text-slate-900 dark:text-white">{{ $g['title'] }}</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $g['desc'] }}</p>
                            </div>
                        </header>

                        <div class="relative mt-5 space-y-6 before:absolute before:bottom-6 before:left-[21px] before:top-0 before:w-px before:bg-linear-to-b before:from-slate-300 before:to-transparent dark:before:from-white/15">
                            @foreach ($g['tools'] as $key => [$title, $desc, $icon])
                                <article id="arac-{{ $key }}" data-tool-card="{{ $key }}" x-show="matches('{{ $key }}')"
                                    class="relative ml-0 scroll-mt-24 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 transition sm:ml-11 dark:bg-slate-800/80 dark:ring-white/10"
                                    :class="active === '{{ $key }}' ? 'shadow-lg ring-indigo-300 dark:ring-indigo-500/40' : ''">
                                    <span class="absolute inset-x-0 top-0 h-1 bg-linear-to-r {{ $t['grad'] }}"></span>
                                    <header class="flex items-start gap-4 px-5 pb-3 pt-5 sm:px-6">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg font-black ring-1 {{ $t['soft'] }} {{ $t['text'] }}">{{ $icon }}</span>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $desc }}</p>
                                        </div>
                                        <button type="button" @click="copy(location.origin + location.pathname + '?arac={{ $key }}')" title="Bağlantıyı kopyala" aria-label="{{ $title }} bağlantısını kopyala"
                                            class="rounded-lg p-1.5 text-slate-300 transition hover:bg-slate-100 hover:text-slate-600 dark:text-slate-500 dark:hover:bg-white/10 dark:hover:text-slate-200">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                                        </button>
                                        <button type="button" @click="toggleFavorite('{{ $key }}')" :class="favorites.includes('{{ $key }}') ? 'text-amber-400' : 'text-slate-300 hover:text-amber-400 dark:text-slate-600'"
                                            class="text-2xl leading-none transition" :title="favorites.includes('{{ $key }}') ? 'Favorilerden çıkar' : 'Favorilere ekle'">★</button>
                                    </header>
                                    <div class="px-5 pb-6 sm:px-6">
                                        @include('livewire.tools.calculators.' . $key)
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div x-show="matchCount === 0" x-cloak class="rounded-2xl border-2 border-dashed border-slate-300 py-16 text-center dark:border-white/10">
                    <p class="text-4xl">🔎</p>
                    <p class="mt-2 font-semibold text-slate-700 dark:text-slate-200">"<span x-text="query"></span>" için araç bulunamadı</p>
                    <button type="button" @click="query = ''" class="mt-3 text-sm font-semibold text-indigo-600">Aramayı temizle</button>
                </div>
            </div>
        </div>
    </div>
</div>
