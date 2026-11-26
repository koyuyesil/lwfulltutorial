{{--
    Ortak düğme. href verilirse <a>, yoksa <button>.
    variant: primary | secondary | ghost | danger | danger-ghost | success | warning   size: sm | md | lg
    icon: Heroicons (outline) path "d" değeri · loading: wire:target (yüklenirken döner simge gösterir)
--}}
@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'icon' => null, 'type' => 'button', 'loading' => null, 'navigate' => true])
@php
    $variants = [
        'primary' => 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline-indigo-600',
        'secondary' => 'bg-white text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white',
        'danger-ghost' => 'text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-500/10',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-500 focus-visible:outline-rose-600',
        'success' => 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-500 focus-visible:outline-emerald-600',
        'warning' => 'bg-amber-500 text-amber-950 shadow-sm hover:bg-amber-400 focus-visible:outline-amber-500',
    ];
    $sizes = [
        'sm' => 'gap-1.5 rounded-lg px-2.5 py-1.5 text-xs',
        'md' => 'gap-2 rounded-lg px-4 py-2.5 text-sm',
        'lg' => 'gap-2 rounded-xl px-5 py-3 text-base',
    ];
    $iconSize = $size === 'sm' ? 'h-3.5 w-3.5' : 'h-4 w-4';
    $classes = 'inline-flex shrink-0 items-center justify-center whitespace-nowrap font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 '
        .($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($navigate && ! $attributes->has('target')) wire:navigate @endif {{ $attributes->class($classes) }}>
        @if ($icon)<svg class="{{ $iconSize }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }} @if ($loading) wire:loading.attr="disabled" wire:target="{{ $loading }}" @endif>
        @if ($loading)
            <svg wire:loading wire:target="{{ $loading }}" class="{{ $iconSize }} shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3" /><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
        @endif
        @if ($icon)<svg @if ($loading) wire:loading.remove wire:target="{{ $loading }}" @endif class="{{ $iconSize }} shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>@endif
        {{ $slot }}
    </button>
@endif
