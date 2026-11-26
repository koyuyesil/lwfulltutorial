@php
    $chip = 'inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition';
    $chipOn = 'bg-slate-900 text-white ring-slate-900 dark:bg-white dark:text-slate-900 dark:ring-white';
    $chipOff = 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10';
    $initials = fn ($n) => mb_strtoupper(collect(preg_split('/\s+/', trim($n)))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode(''));
@endphp

<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Servis /</span> İş panosu</h2>
</x-slot>

<div class="mx-auto max-w-[110rem] space-y-4 px-4 py-6 sm:px-6 lg:px-8"
    x-data="{
        dragId: null, from: null, over: null, overTech: null,
        transitions: @js($transitions),
        allowed(to) { return this.from && (this.from === to || this.transitions[this.from]?.includes(to)); },
        start(e, id, status) { this.dragId = id; this.from = status; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', id); },
        end() { this.dragId = null; this.from = null; this.over = null; this.overTech = null; },
        drop(status) { const id = this.dragId, from = this.from; this.end(); if (id && from !== status && this.transitions[from]?.includes(status)) $wire.move(id, status); },
        dropTech(name) { const id = this.dragId; this.end(); if (id) $wire.assign(id, name); },
    }">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">İş panosu</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $total }} açık iş. Kartı sürükleyip sütuna bırakın: durum değişir · teknisyene bırakın: atanır.</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Kod, müşteri, model…"
                    class="w-56 rounded-lg border-0 bg-white py-2 pl-9 pr-3 text-sm shadow-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-800 dark:text-white dark:ring-white/10">
            </div>
            <a href="{{ route('tickets.index') }}" wire:navigate class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-white/10">Liste görünümü</a>
        </div>
    </div>

    {{-- Teknisyenler: filtre + bırakma hedefi --}}
    <x-scroll-row track="gap-1.5 py-1" label="Teknisyen">
        <button type="button" wire:click="$set('technician', '')" aria-pressed="{{ $technician === '' ? 'true' : 'false' }}" @class([$chip, $technician === '' ? $chipOn : $chipOff])>Herkes <span class="opacity-60">{{ $total }}</span></button>
        <button type="button" wire:click="$set('technician', '-')" aria-pressed="{{ $technician === '-' ? 'true' : 'false' }}"
            @dragover.prevent="overTech = '-'" @dragleave="overTech = null" @drop.prevent="dropTech('')"
            :class="overTech === '-' ? 'ring-2 ring-indigo-500 scale-105' : ''"
            @class([$chip, $technician === '-' ? $chipOn : $chipOff])>Atanmamış <span class="opacity-60">{{ $unassigned }}</span></button>
        @foreach ($technicians as $tech)
            <button type="button" wire:click="$set('technician', @js($tech))" aria-pressed="{{ $technician === $tech ? 'true' : 'false' }}"
                @dragover.prevent="overTech = @js($tech)" @dragleave="overTech = null" @drop.prevent="dropTech(@js($tech))"
                :class="overTech === @js($tech) ? 'ring-2 ring-indigo-500 scale-105' : ''"
                @class([$chip, $technician === $tech ? $chipOn : $chipOff])>
                <span class="grid h-5 w-5 place-items-center rounded-full bg-indigo-600 text-[9px] font-bold text-white">{{ $initials($tech) }}</span>
                {{ $tech }} <span class="opacity-60">{{ $workload[$tech] ?? 0 }}</span>
            </button>
        @endforeach
        <a href="{{ route('settings', 'servis') }}" wire:navigate class="{{ $chip }} {{ $chipOff }}">+ Teknisyen</a>
    </x-scroll-row>

    {{-- Sütunlar --}}
    <div class="-mx-4 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid auto-cols-[minmax(260px,1fr)] grid-flow-col gap-3" wire:loading.class="opacity-70" wire:target="move,assign,technician,search">
            @foreach ($columns as $value => $col)
                @php $s = $col['status']; @endphp
                <section class="flex min-h-[60vh] flex-col rounded-2xl bg-slate-100/80 ring-1 ring-slate-200 transition dark:bg-white/5 dark:ring-white/10"
                    @dragover.prevent="over = @js($value)" @dragleave.self="over = null" @drop.prevent="drop(@js($value))"
                    :class="{
                        'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-500/10': dragId && over === @js($value) && allowed(@js($value)) && from !== @js($value),
                        'ring-2 ring-rose-400 bg-rose-50/60 dark:bg-rose-500/10': dragId && over === @js($value) && ! allowed(@js($value)),
                        'opacity-50': dragId && ! allowed(@js($value)),
                    }">
                    <header class="flex items-center gap-2 px-3 py-2.5">
                        <span class="h-2.5 w-2.5 rounded-full {{ $s->bar() }}"></span>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $s->label() }}</h2>
                        <span class="ms-auto rounded-full bg-white px-2 text-xs font-bold text-slate-500 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10">{{ $col['tickets']->count() }}</span>
                    </header>
                    <div class="flex-1 space-y-2 px-2 pb-2">
                        @foreach ($col['tickets'] as $t)
                            @php
                                $sla = $t->slaState();
                                $client = $t->clientProduct->client;
                                $product = $t->clientProduct->product;
                            @endphp
                            <article wire:key="card-{{ $t->id }}" draggable="true"
                                @dragstart="start($event, {{ $t->id }}, @js($value))" @dragend="end()"
                                :class="dragId === {{ $t->id }} ? 'opacity-40 rotate-1' : ''"
                                @class(['cursor-grab rounded-xl border-l-4 bg-white p-3 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing dark:bg-slate-800 dark:ring-white/10',
                                    'border-l-red-500' => $sla === 'overdue', 'border-l-amber-400' => $sla === 'warning', 'border-l-emerald-400' => $sla === 'ok', 'border-l-slate-200 dark:border-l-white/10' => in_array($sla, ['closed', 'none'])])>
                                <div class="flex items-center justify-between gap-2">
                                    <x-copy-code :code="$t->tracking_code" :href="route('tickets.show', $t)" class="text-xs" />
                                    @if ($t->technician)
                                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-indigo-600 text-[10px] font-bold text-white" title="{{ $t->technician }}">{{ $initials($t->technician) }}</span>
                                    @endif
                                </div>
                                <p class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $product->brand }} {{ $product->model_name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $client->full_name }}</p>
                                <p class="mt-1 line-clamp-2 text-xs text-slate-600 dark:text-slate-300">{{ $t->problem }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                                    @if ($t->due_at)
                                        <span @class(['rounded-full px-1.5 py-0.5 font-semibold',
                                            'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' => $sla === 'overdue',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200' => $sla === 'warning',
                                            'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300' => ! in_array($sla, ['overdue', 'warning'])])>⏱ {{ $t->due_at->locale('tr')->diffForHumans(['short' => true]) }}</span>
                                    @endif
                                    @if ($t->priority === \App\Enums\PriorityType::HIGH) <span class="rounded-full bg-rose-600 px-1.5 py-0.5 font-bold text-white">ACİL</span> @endif
                                    @if ($t->quote_status === 'pending') <span class="rounded-full bg-amber-100 px-1.5 py-0.5 font-semibold text-amber-800 dark:bg-amber-500/15 dark:text-amber-200">teklif bekliyor</span> @endif
                                    @if ($t->quote_status === 'approved') <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 font-semibold text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200">✓ onaylı</span> @endif
                                    @if ($t->photos_count) <span class="text-slate-400">📷 {{ $t->photos_count }}</span> @endif
                                    @if ($t->signed_at) <span class="text-slate-400" title="Kabul formu imzalı">✍</span> @endif
                                </div>
                            </article>
                        @endforeach
                        @if ($col['tickets']->isEmpty())
                            <p class="rounded-xl border-2 border-dashed border-slate-300 px-3 py-6 text-center text-xs text-slate-400 dark:border-white/10">Boş</p>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
