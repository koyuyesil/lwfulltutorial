<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' · ' : '' }}{{ config('app.name', 'KoyuMavi') }} Servis</title>

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="alternate icon" href="/favicon.ico">
        <meta name="theme-color" content="#0B1E4D">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        {{-- jsPDF/html2canvas ve grafikler Vite ile, ihtiyaç anında yüklenir --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-900 dark:text-slate-100" x-data="{ sidebarOpen: false }"
        @keydown.escape.window="sidebarOpen = false">

        <livewire:navigation.app />

        <div class="flex min-h-screen flex-col lg:ps-64">
            {{-- Üst çubuk --}}
            <div class="sticky top-0 z-30 border-b border-slate-200 bg-white/85 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/85">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" @click="sidebarOpen = true" aria-label="Menüyü aç"
                        class="-ms-1 rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:hover:bg-white/10">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>

                    <div class="min-w-0 flex-1 [&_h2]:truncate [&_h2]:text-lg [&_h2]:font-bold [&_h2]:text-slate-900 dark:[&_h2]:text-white">
                        @isset($header)
                            {{ $header }}
                        @else
                            <h2>{{ $title ?? '' }}</h2>
                        @endisset
                    </div>

                    <div class="hidden md:block">
                        <livewire:search />
                    </div>

                    <livewire:notification-bell />

                    <a href="{{ route('tickets.index', ['yeni' => 1]) }}" wire:navigate
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span class="hidden sm:inline">Servis kaydı</span>
                    </a>
                </div>
            </div>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="px-4 py-6 text-xs text-slate-500 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} {{ config('app.name', 'KoyuMavi') }} Servis
            </footer>
        </div>

        <x-toasts />
    </body>
</html>
