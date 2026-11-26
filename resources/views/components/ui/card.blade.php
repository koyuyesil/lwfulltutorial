{{--
    Kart. title / description / icon (Heroicons path) / tone başlığı oluşturur; slot "actions" başlığın sağına,
    slot "footer" alta gelir. padding=false: içerik kenara dayalı (tablo vb.).
--}}
@props(['title' => null, 'description' => null, 'icon' => null, 'tone' => 'indigo', 'padding' => true])
@php
    $iconTones = [
        'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
        'sky' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'violet' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
        'slate' => 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-300',
    ];
@endphp

<section {{ $attributes->class('rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10') }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-5 py-4 dark:border-white/5">
            @if ($icon)
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $iconTones[$tone] ?? $iconTones['indigo'] }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                </span>
            @endif
            <div class="min-w-0 flex-1">
                @if ($title)<h3 class="font-bold text-slate-900 dark:text-white">{{ $title }}</h3>@endif
                @if ($description)<p class="text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['p-5' => $padding])>{{ $slot }}</div>
    @isset($footer)
        <footer class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-3 dark:border-white/5 dark:bg-white/[.02]">{{ $footer }}</footer>
    @endisset
</section>
