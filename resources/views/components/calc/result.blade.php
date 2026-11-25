{{-- Sonuç kartı. value: Alpine ifadesi (metin döndürür). --}}
@props(['label', 'value', 'hint' => null, 'accent' => false])

<div {{ $attributes->class([
    'group relative min-w-0 rounded-xl p-4 ring-1',
    'bg-indigo-600 text-white ring-indigo-600' => $accent,
    'bg-slate-50 ring-slate-200 dark:bg-white/5 dark:ring-white/10' => ! $accent,
]) }}>
    <p @class(['text-xs font-semibold uppercase tracking-wide', 'text-indigo-100' => $accent, 'text-slate-500 dark:text-slate-400' => ! $accent])>{{ $label }}</p>
    <p @class(['mt-1 truncate font-mono text-2xl font-black tabular-nums', 'text-slate-900 dark:text-white' => ! $accent]) x-text="{{ $value }}"></p>
    @if ($hint)
        <p @class(['mt-0.5 text-xs', 'text-indigo-100' => $accent, 'text-slate-500 dark:text-slate-400' => ! $accent])>{!! $hint !!}</p>
    @endif
    <button type="button" x-on:click="copy({{ $value }})" title="Kopyala" aria-label="{{ $label }} değerini kopyala"
        @class(['absolute right-2 top-2 rounded-md p-1 opacity-0 transition group-hover:opacity-100 focus:opacity-100',
            'text-indigo-100 hover:bg-white/10' => $accent, 'text-slate-400 hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-white/10' => ! $accent])>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>
    </button>
</div>
