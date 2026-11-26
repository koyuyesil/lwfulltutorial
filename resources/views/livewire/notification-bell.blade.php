<div class="relative" wire:poll.60s x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" @click="open = ! open" :aria-expanded="open"
        aria-label="Bildirimler{{ $total ? ': ' . $total . ' yeni' : '' }}"
        class="relative rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
        <svg @class(['h-6 w-6', 'origin-top animate-[wiggle_1s_ease-in-out_2]' => $total]) fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
        @if ($total)
            <span class="absolute -right-0.5 -top-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[11px] font-bold leading-none text-white ring-2 ring-white dark:ring-slate-900">
                {{ $total > 99 ? '99+' : $total }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition.origin.top.right
        class="absolute right-0 top-full z-50 mt-2 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
        <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-white/5">
            <p class="font-bold text-slate-900 dark:text-white">Bildirimler</p>
            <span class="text-xs text-slate-500">{{ $total ? $total . ' öğe dikkat bekliyor' : 'Her şey yolunda' }}</span>
        </header>

        <div class="max-h-[65vh] overflow-y-auto">
            @if ($total === 0)
                <div class="px-6 py-10 text-center">
                    <p class="text-3xl">🎉</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Bekleyen bildirim yok</p>
                    <p class="text-xs text-slate-500">Gecikmiş görev ya da servis kaydı bulunmuyor.</p>
                </div>
            @endif

            @if ($tickets->isNotEmpty())
                <p class="px-4 pb-1 pt-3 text-[11px] font-bold uppercase tracking-wider text-red-500">Geciken servis kayıtları · {{ $ticketCount }}</p>
                @foreach ($tickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" wire:navigate @click="open = false"
                        class="flex items-start gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-white/5">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-red-500"></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex min-w-0 items-center gap-1 text-sm font-semibold text-slate-900 dark:text-white"><x-copy-code :code="$ticket->tracking_code" plain text-class="" /> <span class="truncate">· {{ $ticket->clientProduct?->client?->full_name }}</span></span>
                            <span class="block truncate text-xs text-slate-500">{{ $ticket->clientProduct?->product?->display_name }} · {{ $ticket->status->label() }} · hedef {{ $ticket->due_at->locale('tr')->diffForHumans() }}</span>
                        </span>
                    </a>
                @endforeach
            @endif

            @if ($tasks->isNotEmpty())
                <p class="px-4 pb-1 pt-3 text-[11px] font-bold uppercase tracking-wider text-amber-600">Görevler · {{ $taskCount }}</p>
                @foreach ($tasks as $task)
                    @php
                        $state = $task->isOverdue() && ! $task->due_at->isToday() ? 'Gecikti' : ($task->due_at?->isToday() ? 'Bugün ' . $task->due_at->format('H:i') : 'Hatırlatma');
                    @endphp
                    <div wire:key="bell-task-{{ $task->id }}" class="group flex items-start gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-white/5">
                        <span class="text-base leading-5">{{ $task->category->emoji() }}</span>
                        <a href="{{ route('tasks.index') }}" wire:navigate @click="open = false" class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $task->title }}</span>
                            <span class="block truncate text-xs text-slate-500">
                                <span @class(['font-semibold', 'text-red-600' => $state === 'Gecikti', 'text-amber-600' => $state !== 'Gecikti'])>{{ $state }}</span>
                                @if ($task->due_at && $state === 'Gecikti') · {{ $task->due_at->locale('tr')->diffForHumans() }} @endif
                                @if ($task->amount !== null) · {{ $task->formattedAmount() }} @endif
                            </span>
                        </a>
                        <div class="flex shrink-0 gap-1">
                            <button type="button" wire:click="snooze({{ $task->id }})" title="1 saat ertele"
                                class="rounded-md px-1.5 py-1 text-xs text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10">⏰</button>
                            <button type="button" wire:click="complete({{ $task->id }})" title="Tamamla"
                                class="rounded-md px-1.5 py-1 text-xs font-bold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10">✓</button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <footer class="grid grid-cols-2 border-t border-slate-100 text-center text-xs font-semibold dark:border-white/5">
            <a href="{{ route('tasks.index') }}" wire:navigate @click="open = false" class="py-2.5 text-indigo-600 hover:bg-slate-50 dark:text-indigo-300 dark:hover:bg-white/5">Tüm görevler</a>
            <a href="{{ route('tickets.index', ['sla' => 'overdue']) }}" wire:navigate @click="open = false" class="border-s border-slate-100 py-2.5 text-indigo-600 hover:bg-slate-50 dark:border-white/5 dark:text-indigo-300 dark:hover:bg-white/5">Geciken servisler</a>
        </footer>
    </div>
</div>
