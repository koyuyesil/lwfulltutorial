{{-- Araçlar modülünün alt sekmeleri. Board ID, servislere sunulan premium ek araçtır. --}}
@php
    $tabs = [
        ['route' => 'tools.index', 'label' => 'Elektronik hesaplayıcılar', 'premium' => false],
        ['route' => 'board-ids.index', 'label' => 'Board ID kütüphanesi', 'premium' => true],
        ['route' => 'cross-reference.index', 'label' => 'Çapraz referans', 'premium' => true],
    ];
@endphp

<div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
    <nav class="rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10" aria-label="Araçlar">
        <x-scroll-row track="gap-1">
        @foreach ($tabs as $tab)
            @php $active = request()->routeIs($tab['route']); @endphp
            <a href="{{ route($tab['route']) }}" wire:navigate @if ($active) aria-current="page" @endif
                @class([
                    'inline-flex shrink-0 items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition',
                    'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => $active,
                    'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' => ! $active,
                ])>
                {{ $tab['label'] }}
                @if ($tab['premium'])
                    <span class="rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold uppercase text-amber-950">Premium</span>
                @endif
            </a>
        @endforeach
        </x-scroll-row>
    </nav>
</div>
