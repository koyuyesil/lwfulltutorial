{{-- Rozet. tone: slate | indigo | sky | emerald | amber | orange | rose | violet · dot: renkli nokta --}}
@props(['tone' => 'slate', 'dot' => false, 'size' => 'sm'])
@php
    $tones = [
        'slate' => ['bg-slate-100 text-slate-700 ring-slate-200 dark:bg-white/10 dark:text-slate-300 dark:ring-white/10', 'bg-slate-400'],
        'indigo' => ['bg-indigo-50 text-indigo-700 ring-indigo-200 dark:bg-indigo-500/15 dark:text-indigo-300 dark:ring-indigo-500/30', 'bg-indigo-500'],
        'sky' => ['bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/30', 'bg-sky-500'],
        'emerald' => ['bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30', 'bg-emerald-500'],
        'amber' => ['bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/30', 'bg-amber-500'],
        'orange' => ['bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-500/15 dark:text-orange-300 dark:ring-orange-500/30', 'bg-orange-500'],
        'rose' => ['bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-500/30', 'bg-rose-500'],
        'violet' => ['bg-violet-50 text-violet-700 ring-violet-200 dark:bg-violet-500/15 dark:text-violet-300 dark:ring-violet-500/30', 'bg-violet-500'],
    ];
    [$tone, $dotColor] = $tones[$tone] ?? $tones['slate'];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 whitespace-nowrap rounded-full font-semibold ring-1 ring-inset', $tone,
    'px-2 py-0.5 text-[11px]' => $size === 'sm', 'px-2.5 py-1 text-xs' => $size !== 'sm']) }}>
    @if ($dot)<span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>@endif
    {{ $slot }}
</span>
