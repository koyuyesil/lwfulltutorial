@php
    $links = [
        ['href' => route('home') . '#ozellikler', 'label' => 'Özellikler', 'active' => false],
        ['href' => route('home') . '#sektorler', 'label' => 'Sektörler', 'active' => false],
        ['href' => route('home') . '#fiyatlar', 'label' => 'Fiyatlar', 'active' => false],
        ['href' => route('services.index'), 'label' => 'Çözümler', 'active' => request()->routeIs('services.*')],
    ];
@endphp

<nav x-data="{ open: false }" @keydown.escape.window="open = false"
    class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/80">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Logo --}}
        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5">
            <x-application-logo class="h-9 w-9 shrink-0 rounded-xl shadow-lg shadow-sky-900/30" />
            <x-brand-name class="text-base" />
        </a>

        {{-- Masaüstü linkleri --}}
        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" @class([
                    'rounded-lg px-3 py-2 text-sm font-medium transition',
                    'text-slate-900 bg-slate-100 dark:bg-white/10 dark:text-white' => $link['active'],
                    'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' => ! $link['active'],
                ])>{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ route('tracking') }}" wire:navigate @class([
                'ms-1 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold transition',
                'bg-emerald-600 text-white' => request()->routeIs('tracking'),
                'text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-50 dark:text-emerald-300 dark:ring-emerald-500/30 dark:hover:bg-emerald-500/10' => ! request()->routeIs('tracking'),
            ])>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
                Arıza Takibi
            </a>
        </div>

        {{-- Hesap --}}
        <div class="hidden items-center gap-2 lg:flex">
            <x-theme-toggle />
            @auth
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                    Panele git →
                </a>
            @else
                <a href="{{ route('login') }}" wire:navigate
                    class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5">
                    Giriş yap
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" wire:navigate
                        class="rounded-xl bg-linear-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
                        Ücretsiz deneyin
                    </a>
                @endif
            @endauth
        </div>

        {{-- Mobil menü düğmesi --}}
        <x-theme-toggle class="lg:hidden" />
        <button type="button" @click="open = ! open" :aria-expanded="open" aria-label="Menüyü aç/kapat"
            class="inline-flex items-center justify-center rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-white/10">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Mobil menü --}}
    <div x-show="open" x-cloak x-transition.origin.top
        class="border-t border-slate-200 bg-white px-4 pb-4 pt-2 lg:hidden dark:border-white/10 dark:bg-slate-950">
        <div class="grid gap-1">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" @click="open = false"
                    class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5">{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ route('tracking') }}" wire:navigate
                class="rounded-lg bg-emerald-50 px-3 py-2.5 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">🔍 Arıza Takibi</a>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-200 pt-3 dark:border-white/10">
            @auth
                <a href="{{ route('dashboard') }}" wire:navigate class="col-span-2 rounded-xl bg-slate-900 px-4 py-2.5 text-center text-sm font-semibold text-white dark:bg-white dark:text-slate-900">Panele git</a>
            @else
                <a href="{{ route('login') }}" wire:navigate class="rounded-xl px-4 py-2.5 text-center text-sm font-semibold text-slate-700 ring-1 ring-slate-200 dark:text-slate-200 dark:ring-white/10">Giriş yap</a>
                <a href="{{ route('register') }}" wire:navigate class="rounded-xl bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white">Ücretsiz deneyin</a>
            @endauth
        </div>
    </div>
</nav>
