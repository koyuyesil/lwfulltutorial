{{-- Formül şeridi --}}
<div {{ $attributes->class('flex items-start gap-2 rounded-xl bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-600 ring-1 ring-slate-200/70 dark:bg-white/5 dark:text-slate-300 dark:ring-white/5') }}>
    <span class="mt-px shrink-0 rounded bg-slate-900 px-1.5 py-0.5 font-mono text-[10px] font-bold text-white dark:bg-white dark:text-slate-900">f(x)</span>
    <div class="min-w-0 font-mono">{{ $slot }}</div>
</div>
