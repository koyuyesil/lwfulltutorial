<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Teknik servisler için bulut tabanlı satış sonrası servis yönetimi: servis kaydı, SLA takibi, müşteri bilgilendirme ve arıza takibi.">

        <title>{{ isset($title) ? $title . ' · ' : '' }}{{ config('app.name', 'KoyuMavi') }} Servis</title>

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="alternate icon" href="/favicon.ico">
        <meta name="theme-color" content="#0B1E4D">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="flex min-h-screen flex-col">
            <x-navigation.guest />

            @if (isset($header))
                <header class="border-b border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-1">
                {{ $slot }}
            </main>

            <x-footer />
        </div>
    </body>
</html>
