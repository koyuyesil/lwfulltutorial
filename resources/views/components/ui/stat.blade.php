@props(['label', 'value', 'hint' => null, 'tone' => 'slate'])

@php
    $bar = [
        'slate' => 'bg-slate-400', 'indigo' => 'bg-indigo-500', 'emerald' => 'bg-emerald-500',
        'amber' => 'bg-amber-400', 'sky' => 'bg-sky-500', 'violet' => 'bg-violet-500', 'rose' => 'bg-rose-500',
    ][$tone] ?? 'bg-slate-400';
@endphp

<div {{ $attributes->class('relative overflow-hidden rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10') }}>
    <span class="absolute inset-y-0 left-0 w-1 {{ $bar }}"></span>
    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</p>
    <p class="mt-1 truncate text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ $value }}</p>
    @if ($hint)
        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
