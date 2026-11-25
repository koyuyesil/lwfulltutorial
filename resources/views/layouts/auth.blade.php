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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="grid min-h-screen lg:grid-cols-2">
            {{-- Marka paneli --}}
            <aside class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(56,189,248,0.35),transparent_40%),radial-gradient(circle_at_80%_80%,rgba(99,102,241,0.35),transparent_40%)]"></div>
                <a href="{{ route('home') }}" wire:navigate class="relative flex items-center gap-2.5">
                    <x-application-logo class="h-10 w-10 shrink-0 rounded-xl shadow-lg shadow-sky-900/30" />
                    <x-brand-name dark class="text-lg" />
                </a>
                <div class="relative">
                    <p class="text-3xl font-black leading-tight">Kabulden teslimata,<br>her servis kaydı kontrol altında.</p>
                    <ul class="mt-8 space-y-3 text-sm text-slate-300">
                        <li class="flex gap-3"><span class="text-emerald-400">✓</span> Adım adım servis iş akışı ve SLA uyarıları</li>
                        <li class="flex gap-3"><span class="text-emerald-400">✓</span> Müşterinize arıza takip kodu ve canlı durum sayfası</li>
                        <li class="flex gap-3"><span class="text-emerald-400">✓</span> Teknisyen performansı ve operasyon grafikleri</li>
                    </ul>
                </div>
                <p class="relative text-xs text-slate-500">&copy; {{ date('Y') }} {{ config('app.name', 'KoyuMavi') }}</p>
            </aside>

            {{-- Form --}}
            <main class="flex flex-col items-center justify-center px-4 py-12 sm:px-8">
                <a href="{{ route('home') }}" wire:navigate class="mb-8 flex items-center gap-2 lg:hidden">
                    <x-application-logo class="h-10 w-10 shrink-0 rounded-xl shadow-lg shadow-sky-900/30" />
                    <x-brand-name class="text-lg" />
                </a>
                <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/60 ring-1 ring-slate-200 dark:bg-slate-900 dark:shadow-none dark:ring-white/10">
                    {{ $slot }}
                </div>
                <a href="{{ route('home') }}" wire:navigate class="mt-6 text-sm text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">← Ana sayfaya dön</a>
            </main>
        </div>
    </body>
</html>
