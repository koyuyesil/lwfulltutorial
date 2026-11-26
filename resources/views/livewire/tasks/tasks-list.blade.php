@use('App\Enums\TaskCategory')
@php
    $groupMeta = [
        'overdue' => ['Gecikmiş', 'text-red-600 dark:text-red-400', 'bg-red-500'],
        'today' => ['Bugün', 'text-amber-600 dark:text-amber-400', 'bg-amber-400'],
        'tomorrow' => ['Yarın', 'text-indigo-600 dark:text-indigo-300', 'bg-indigo-500'],
        'week' => ['Bu hafta', 'text-slate-700 dark:text-slate-200', 'bg-sky-500'],
        'later' => ['Daha sonra', 'text-slate-700 dark:text-slate-200', 'bg-slate-400'],
        'none' => ['Tarihsiz', 'text-slate-500', 'bg-slate-300'],
    ];
    $money = fn ($v) => '₺' . number_format((float) $v, 0, ',', '.');
    $chip = 'inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition';
    $chipOff = 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/5';
    $chipOn = 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
    $isFiltered = $search !== '' || $category !== '' || $filter !== '';
@endphp

<div class="mt-6 space-y-4">
    {{-- Özet --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <x-ui.stat label="Gecikmiş" :value="$stats['overdue']" tone="rose" />
        <x-ui.stat label="Bugün" :value="$stats['today']" tone="amber" />
        <x-ui.stat label="Bu hafta" :value="$stats['week']" tone="indigo" />
        <x-ui.stat label="Tahsil edilecek" :value="$money($stats['income'])" hint="Önümüzdeki 30 gün" tone="emerald" />
        <x-ui.stat label="Ödenecek" :value="$money($stats['expense'])" hint="Önümüzdeki 30 gün" tone="rose" />
        <x-ui.stat label="Tamamlanan" :value="$stats['done7']" hint="Son 7 gün" tone="sky" />
    </div>

    {{-- Hızlı ekleme --}}
    <form wire:submit="quickAdd" class="rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10" x-data>
        <div class="flex items-center gap-3">
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-indigo-600 text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            </span>
            <label for="quick-task" class="sr-only">Hızlı görev ekle</label>
            <input id="quick-task" x-ref="quick" wire:model="quick" autocomplete="off"
                placeholder="Hızlı ekle: “KDV beyannamesi her ay ayın 28'i 12:00” ya da “Ahmet Bey tahsilat yarın 2500 tl”"
                class="min-w-0 flex-1 border-0 bg-transparent px-0 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white">
            <button type="submit" wire:loading.attr="disabled" wire:target="quickAdd"
                class="shrink-0 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900">Ekle ↵</button>
        </div>
        <p class="mt-2 ps-11 text-[11px] text-slate-500">
            Anlar: <strong>bugün, yarın, haftaya, pazartesi…, ayın 15'i</strong>, saat (<strong>14:30</strong>), tekrar (<strong>her gün/hafta/ay/yıl</strong>), tutar (<strong>1500 tl</strong>) ve türü (fatura, tahsil, kira, KDV, sipariş…).
        </p>
    </form>

    {{-- Araç çubuğu --}}
    <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
        <x-ui.search wire:model.live.debounce.300ms="search" placeholder="Görevlerde ara…" />
        <div class="flex flex-wrap gap-1.5">
            @foreach (['' => 'Tümü', 'recurring' => '🔁 Tekrarlayan', 'money' => '₺ Tutarlı'] as $key => $label)
                <button type="button" wire:click="$set('filter', '{{ $key }}')" @class([$chip, $filter === $key ? $chipOn : $chipOff])>{{ $label }}</button>
            @endforeach
        </div>
        <div class="ms-auto flex items-center gap-2">
            @if ($isFiltered)
                <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Temizle</button>
            @endif
            <div class="flex rounded-lg bg-slate-100 p-0.5 dark:bg-white/5" role="group" aria-label="Görünüm">
                @foreach (['list' => 'Gündem', 'calendar' => 'Takvim'] as $mode => $label)
                    <button type="button" wire:click="$set('view', '{{ $mode }}')"
                        @class(['rounded-md px-3 py-1 text-xs font-semibold transition', 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' => $view === $mode, 'text-slate-500 hover:text-slate-700' => $view !== $mode])>{{ $label }}</button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Türler --}}
    <x-scroll-row track="gap-1.5 py-0.5" label="Görev türü">
        <button type="button" wire:click="$set('category', '')" aria-pressed="{{ $category === '' ? 'true' : 'false' }}" @class([$chip, 'shrink-0 whitespace-nowrap', $category === '' ? $chipOn : $chipOff])>Tüm türler</button>
        @foreach (TaskCategory::cases() as $c)
            <button type="button" wire:click="$set('category', '{{ $c->value }}')" aria-pressed="{{ $category === $c->value ? 'true' : 'false' }}" @class([$chip, 'shrink-0 whitespace-nowrap', $category === $c->value ? $chipOn : $chipOff])>
                <span>{{ $c->emoji() }}</span> {{ $c->label() }}
                @if ($n = $categoryCounts[$c->value] ?? 0) <span class="opacity-60">{{ $n }}</span> @endif
            </button>
        @endforeach
    </x-scroll-row>

    <div wire:loading.class="opacity-60" wire:target="search,category,filter,view,shiftMonth,showDone">
    @if ($view === 'list')
        {{-- GÜNDEM --}}
        @if (empty($agenda) && ! $showDone)
            <div class="rounded-xl border-2 border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-white/10 dark:bg-slate-800/50">
                <p class="text-4xl">✅</p>
                <p class="mt-3 font-semibold text-slate-800 dark:text-white">{{ $isFiltered ? 'Bu filtreye uyan açık görev yok' : 'Tüm görevler tamam!' }}</p>
                <p class="mt-1 text-sm text-slate-500">Faturalar, ödemeler ve hatırlatmalar için hazır şablonlarla başlayın.</p>
                <button type="button" x-on:click="$dispatch('open-drawer', { name: 'task-form' }); Livewire.dispatch('create-task')"
                    class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Şablonlardan görev ekle</button>
            </div>
        @endif

        <div class="space-y-4">
            @foreach ($agenda as $bucket => $items)
                <section class="overflow-visible rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                    <header class="flex items-center gap-2 border-b border-slate-100 px-4 py-2.5 dark:border-white/5">
                        <span class="h-2 w-2 rounded-full {{ $groupMeta[$bucket][2] }}"></span>
                        <h3 class="text-sm font-bold {{ $groupMeta[$bucket][1] }}">{{ $groupMeta[$bucket][0] }}</h3>
                        <span class="text-xs text-slate-400">{{ count($items) }}</span>
                        @if ($bucket === 'today')
                            <span class="ms-auto text-xs text-slate-400">{{ now()->locale('tr')->translatedFormat('d F l') }}</span>
                        @endif
                    </header>
                    <ul class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($items as $task)
                            @include('livewire.tasks.partials.task-row', ['task' => $task])
                        @endforeach
                    </ul>
                </section>
            @endforeach

            <div>
                <button type="button" wire:click="$toggle('showDone')" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">
                    <svg @class(['h-4 w-4 transition', 'rotate-90' => $showDone]) fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Tamamlananlar
                </button>
                @if ($showDone)
                    <section class="mt-2 rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                        <ul class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse ($done as $task)
                                @include('livewire.tasks.partials.task-row', ['task' => $task])
                            @empty
                                <li class="px-4 py-6 text-center text-sm text-slate-500">Henüz tamamlanan görev yok.</li>
                            @endforelse
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    @else
        {{-- TAKVİM --}}
        @php
            $cal = $calendar;
            $days = [];
            for ($d = $cal['gridStart']->copy(); $d->lte($cal['gridEnd']); $d->addDay()) { $days[] = $d->copy(); }
        @endphp
        <section class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10"
            x-data="{ dragging: null, over: null }">
            <header class="flex items-center gap-2 border-b border-slate-100 px-4 py-3 dark:border-white/5">
                <h3 class="text-base font-bold capitalize text-slate-900 dark:text-white">{{ $cal['start']->locale('tr')->translatedFormat('F Y') }}</h3>
                <div class="ms-auto flex items-center gap-1">
                    <button type="button" wire:click="shiftMonth(-1)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Önceki ay">‹</button>
                    <button type="button" wire:click="$set('month', '')" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:text-slate-300 dark:ring-white/10">Bugün</button>
                    <button type="button" wire:click="shiftMonth(1)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Sonraki ay">›</button>
                </div>
            </header>
            <div class="grid grid-cols-7 border-b border-slate-100 text-center text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:border-white/5">
                @foreach (['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'] as $wd)
                    <div class="py-2">{{ $wd }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7">
                @foreach ($days as $day)
                    @php
                        $key = $day->toDateString();
                        $inMonth = $day->month === $cal['start']->month;
                        $items = $cal['byDay'][$key] ?? [];
                        $ghosts = $cal['projected'][$key] ?? [];
                    @endphp
                    <div wire:key="day-{{ $key }}"
                        @dragover.prevent="over = '{{ $key }}'" @dragleave="over = null"
                        @drop.prevent="if (dragging) { $wire.reschedule(dragging, '{{ $key }}') } dragging = null; over = null"
                        :class="over === '{{ $key }}' && 'bg-indigo-50 dark:bg-indigo-500/10'"
                        @class(['group relative min-h-28 border-b border-e border-slate-100 p-1.5 dark:border-white/5',
                            'bg-slate-50/60 dark:bg-white/[0.02]' => ! $inMonth])>
                        <div class="flex items-center justify-between">
                            <span @class(['grid h-6 w-6 place-items-center rounded-full text-xs font-semibold',
                                'bg-indigo-600 text-white' => $day->isToday(),
                                'text-slate-700 dark:text-slate-200' => ! $day->isToday() && $inMonth,
                                'text-slate-300 dark:text-slate-600' => ! $inMonth])>{{ $day->day }}</span>
                            <button type="button" title="Bu güne görev ekle"
                                x-on:click="$dispatch('open-drawer', { name: 'task-form' }); Livewire.dispatch('create-task', { date: '{{ $key }}' })"
                                class="rounded p-0.5 text-slate-300 opacity-0 transition hover:bg-slate-100 hover:text-slate-600 group-hover:opacity-100 dark:hover:bg-white/10">＋</button>
                        </div>
                        <div class="mt-1 space-y-1">
                            @foreach (array_slice($items, 0, 4) as $t)
                                <button type="button" draggable="{{ $t->isDone() ? 'false' : 'true' }}"
                                    @dragstart="dragging = {{ $t->id }}" @dragend="dragging = null"
                                    x-on:click="$dispatch('open-drawer', { name: 'task-form' }); Livewire.dispatch('edit-task', { id: {{ $t->id }} })"
                                    title="{{ $t->title }} · {{ $t->due_at->format('H:i') }}"
                                    @class(['flex w-full items-center gap-1 truncate rounded px-1.5 py-0.5 text-left text-[11px] font-medium',
                                        'cursor-grab active:cursor-grabbing' => ! $t->isDone(),
                                        'bg-slate-100 text-slate-400 line-through dark:bg-white/5' => $t->isDone(),
                                        'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200' => ! $t->isDone() && $t->isOverdue(),
                                        $t->category->badge() => ! $t->isDone() && ! $t->isOverdue()])>
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $t->category->dot() }}"></span>
                                    <span class="truncate">{{ $t->title }}</span>
                                </button>
                            @endforeach
                            @foreach (array_slice($ghosts, 0, max(0, 4 - count($items))) as $g)
                                <div title="Tekrar (tahmini): {{ $g->title }}"
                                    class="flex items-center gap-1 truncate rounded border border-dashed border-slate-300 px-1.5 py-0.5 text-[11px] text-slate-500 dark:border-white/15">
                                    <span>🔁</span><span class="truncate">{{ $g->title }}</span>
                                </div>
                            @endforeach
                            @if (($more = count($items) + count($ghosts) - 4) > 0)
                                <p class="px-1 text-[11px] font-semibold text-slate-400">+{{ $more }} daha</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="px-4 py-2 text-[11px] text-slate-500">İpucu: Görevleri sürükleyip başka bir güne bırakarak tarihini değiştirebilirsiniz. Kesikli çizgiler tekrarlayan görevlerin gelecekteki tahmini tarihleridir.</p>
        </section>
    @endif
    </div>
</div>
