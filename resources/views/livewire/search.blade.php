@php
    $labels = ['tickets' => 'Servis kayıtları', 'clients' => 'Müşteriler', 'products' => 'Cihaz modelleri', 'tasks' => 'Görevler'];
@endphp

<div class="relative w-72 lg:w-96" x-data="{ open: false }"
    @click.outside="open = false" @keydown.escape="open = false; $refs.q.blur()"
    x-on:keydown.window.prevent.ctrl.k="$refs.q.focus(); open = true"
    x-on:keydown.window.prevent.meta.k="$refs.q.focus(); open = true">
    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
    <input x-ref="q" wire:model.live.debounce.250ms="search" type="search" autocomplete="off" @focus="open = true" @input="open = true"
        placeholder="Kod, müşteri, telefon, IMEI, model…" aria-label="Genel arama"
        class="block w-full rounded-lg border-0 bg-slate-100 py-2 pl-9 pr-14 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-600 dark:bg-white/5 dark:text-white dark:focus:bg-slate-800">
    <kbd class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-slate-200 px-1.5 text-[10px] font-semibold text-slate-400 dark:border-white/10">Ctrl K</kbd>

    @if (mb_strlen($term) >= 2)
        <div x-show="open" x-cloak x-transition.opacity
            class="absolute right-0 top-full z-50 mt-2 max-h-[70vh] w-[min(32rem,calc(100vw-2rem))] overflow-y-auto rounded-xl bg-white py-2 shadow-2xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
            @forelse ($groups as $group => $items)
                <p class="px-4 pb-1 pt-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 first:pt-1">{{ $labels[$group] }}</p>
                @foreach ($items as $item)
                    @php
                        [$href, $title, $sub, $badge] = match ($group) {
                            'tickets' => [route('tickets.show', $item), $item->tracking_code . ' · ' . $item->clientProduct?->client?->full_name, $item->clientProduct?->product?->display_name . ' — ' . $item->problem, [$item->status->label(), $item->status->badge()]],
                            'clients' => [route('clients.index', ['ara' => $item->full_name]), $item->full_name, $item->phone . ($item->company ? ' · ' . $item->company : ''), null],
                            'products' => [route('products.index', ['ara' => $item->model_name]), $item->display_name, $item->model_number ?: 'Model no yok', null],
                            'tasks' => [route('tasks.index'), $item->title, $item->description, null],
                        };
                    @endphp
                    <a href="{{ $href }}" wire:navigate @click="open = false"
                        class="flex items-center gap-3 px-4 py-2 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none dark:hover:bg-white/5 dark:focus:bg-white/5">
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ $sub }}</span>
                        </span>
                        @if ($badge)
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $badge[1] }}">{{ $badge[0] }}</span>
                        @endif
                    </a>
                @endforeach
            @empty
                <p class="px-4 py-6 text-center text-sm text-slate-500">"{{ $term }}" için sonuç bulunamadı.</p>
            @endforelse
        </div>
    @endif
</div>
