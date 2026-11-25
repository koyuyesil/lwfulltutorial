@php
    $done = $task->isDone();
    $overdue = $task->isOverdue();
    $reminderDue = ! $done && $task->remind_at && $task->remind_at->isPast();
@endphp

<li wire:key="task-{{ $task->id }}" class="group flex items-start gap-3 px-4 py-3 transition hover:bg-slate-50 dark:hover:bg-white/5"
    x-data="{ leaving: false }" :class="leaving && 'opacity-40'">
    {{-- Tamamla --}}
    <button type="button"
        @if ($done) wire:click="reopen({{ $task->id }})" title="Geri al" @else x-on:click="leaving = true" wire:click="complete({{ $task->id }})" title="Tamamla" @endif
        @class(['mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full border-2 transition',
            'border-emerald-500 bg-emerald-500 text-white' => $done,
            'border-slate-300 text-transparent hover:border-emerald-500 hover:text-emerald-500 dark:border-slate-500' => ! $done])
        aria-label="{{ $done ? 'Tamamlanmadı olarak işaretle' : 'Tamamlandı olarak işaretle' }}">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
    </button>

    <div class="min-w-0 flex-1">
        <button type="button" class="block w-full text-left"
            x-on:click="$dispatch('open-drawer', { name: 'task-form' }); Livewire.dispatch('edit-task', { id: {{ $task->id }} })">
            <span @class(['text-sm font-medium', 'text-slate-400 line-through' => $done, 'text-slate-900 dark:text-white' => ! $done])>
                <span class="me-1">{{ $task->category->emoji() }}</span>{{ $task->title }}
            </span>
            @if ($task->priority === \App\Enums\PriorityType::HIGH && ! $done)
                <span class="ms-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-red-700 dark:bg-red-500/15 dark:text-red-300">Acil</span>
            @endif
        </button>
        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
            @if ($task->due_at)
                <span @class(['inline-flex items-center gap-1 font-medium', 'text-red-600 dark:text-red-400' => $overdue, 'text-amber-600 dark:text-amber-400' => ! $overdue && ! $done && $task->due_at->isToday()])>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    @if ($task->due_at->isToday())
                        Bugün {{ $task->due_at->format('H:i') }}
                    @elseif ($task->due_at->isTomorrow())
                        Yarın {{ $task->due_at->format('H:i') }}
                    @else
                        {{ $task->due_at->locale('tr')->translatedFormat('d M, D H:i') }}
                    @endif
                    @if ($overdue) · {{ $task->due_at->locale('tr')->diffForHumans(['parts' => 1]) }} @endif
                </span>
            @endif
            @if ($task->isRecurring())
                <span class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-300" title="{{ $task->recurrence_until ? 'Bitiş: ' . $task->recurrence_until->format('d.m.Y') : 'Süresiz' }}">
                    🔁 {{ $task->recurrence->describe($task->recurrence_interval) }}
                </span>
            @endif
            @if ($reminderDue)
                <span class="inline-flex items-center gap-1 font-semibold text-amber-600 dark:text-amber-400">🔔 Hatırlatma</span>
            @elseif ($task->remind_at && ! $done)
                <span title="Hatırlatma">🔔 {{ $task->remind_at->locale('tr')->translatedFormat('d M H:i') }}</span>
            @endif
            @if ($task->amount !== null)
                <span @class(['rounded px-1.5 py-0.5 font-semibold tabular-nums',
                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' => $task->category->isIncome(),
                    'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' => ! $task->category->isIncome()])>
                    {{ $task->category->isIncome() ? '+' : '−' }}{{ $task->formattedAmount() }}
                </span>
            @endif
            @if ($task->client)
                <a href="{{ route('clients.index', ['ara' => $task->client->full_name]) }}" wire:navigate class="inline-flex items-center gap-1 hover:text-indigo-600">👤 {{ $task->client->full_name }}</a>
            @endif
            @if ($done && $task->completed_at)
                <span>✓ {{ $task->completed_at->locale('tr')->diffForHumans() }}</span>
            @endif
        </div>
        @if ($task->description && ! $done)
            <p class="mt-1 line-clamp-1 text-xs text-slate-500 dark:text-slate-400">{{ $task->description }}</p>
        @endif
    </div>

    {{-- İşlemler --}}
    @unless ($done)
        <div class="relative flex shrink-0 items-center gap-1 opacity-100 transition sm:opacity-0 sm:group-hover:opacity-100 sm:focus-within:opacity-100"
            x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
            <button type="button" @click="menu = ! menu" title="Ertele / diğer" aria-label="Görev işlemleri"
                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
            </button>
            <div x-show="menu" x-cloak x-transition.origin.top.right role="menu"
                class="absolute right-0 top-full z-20 mt-1 w-52 overflow-hidden rounded-xl bg-white py-1 text-sm shadow-xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-white/10">
                <p class="px-4 pb-1 pt-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Ertele</p>
                @foreach (['1h' => '1 saat', '3h' => '3 saat', 'tomorrow' => 'Yarın 09:00', 'nextweek' => 'Pazartesi 09:00'] as $preset => $label)
                    <button type="button" wire:click="snooze({{ $task->id }}, '{{ $preset }}')" @click="menu = false"
                        class="block w-full px-4 py-1.5 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">{{ $label }}</button>
                @endforeach
                <div class="my-1 border-t border-slate-100 dark:border-white/5"></div>
                <button type="button" @click="menu = false; $dispatch('open-drawer', { name: 'task-form' }); Livewire.dispatch('edit-task', { id: {{ $task->id }} })"
                    class="block w-full px-4 py-1.5 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">Düzenle</button>
                <button type="button" wire:click="delete({{ $task->id }})" wire:confirm="Bu görev silinsin mi?" @click="menu = false"
                    class="block w-full px-4 py-1.5 text-left text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">{{ $task->isRecurring() ? 'Yalnızca bunu sil' : 'Sil' }}</button>
                @if ($task->isRecurring())
                    <button type="button" wire:click="delete({{ $task->id }}, true)" wire:confirm="Bu tekrarlayan görevin tüm açık tekrarları silinecek. Emin misiniz?" @click="menu = false"
                        class="block w-full px-4 py-1.5 text-left text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">Tüm seriyi sil</button>
                @endif
            </div>
        </div>
    @endunless
</li>
