{{-- Liste arama kutusu. "/" tuşu ile odaklanır. --}}
@props(['placeholder' => 'Ara…', 'full' => false])

<div @class(['relative w-full', 'sm:max-w-sm' => ! $full]) x-data
    x-on:keydown.slash.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.input.focus() }">
    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
    <input x-ref="input" type="search" placeholder="{{ $placeholder }}" autocomplete="off"
        {{ $attributes->class('block w-full rounded-lg border-0 bg-white py-2 pl-9 pr-10 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-800 dark:text-white dark:ring-white/10') }}>
    <kbd class="pointer-events-none absolute right-2.5 top-1/2 hidden -translate-y-1/2 rounded border border-slate-200 px-1.5 text-[10px] font-semibold text-slate-400 sm:block dark:border-white/10">/</kbd>
</div>
