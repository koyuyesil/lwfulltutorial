{{-- Hata sayfaları ortak düzeni. Değişkenler: $code, $title, $message, $emoji, $retry --}}
@php try { $signedIn = auth()->check(); } catch (\Throwable) { $signedIn = false; } @endphp
<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $code }} · {{ $title }} · {{ config('app.name', 'KoyuMavi') }}</title>
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <meta name="theme-color" content="#0B1E4D">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <script>
            try {
                const t = localStorage.getItem('theme');
                if (t === 'dark' || (t !== 'light' && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
            } catch (e) {}
        </script>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css'])
        @endif
    </head>
    <body class="grid min-h-screen place-items-center bg-slate-50 px-6 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <main class="w-full max-w-lg py-16 text-center">
            <p class="text-6xl" aria-hidden="true">{{ $emoji }}</p>
            <p class="mt-6 text-sm font-bold uppercase tracking-[.3em] text-indigo-600 dark:text-indigo-400">Hata {{ $code }}</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">{{ $title }}</h1>
            <p class="mt-4 text-base text-slate-600 dark:text-slate-400">{{ $message }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if (! empty($retry))
                    <a href="{{ url()->current() }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Sayfayı yenile</a>
                @endif
                <a href="{{ $signedIn ? url('/dashboard') : url('/') }}" class="inline-flex items-center rounded-lg {{ empty($retry) ? 'bg-indigo-600 text-white hover:bg-indigo-500' : 'bg-white text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-white/10' }} px-4 py-2.5 text-sm font-semibold shadow-sm">
                    {{ $signedIn ? 'Panele dön' : 'Ana sayfaya dön' }}
                </a>
                <button type="button" onclick="history.back()" class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10">← Geri git</button>
            </div>
            <p class="mt-12 text-xs text-slate-400">{{ config('app.name', 'KoyuMavi') }} Servis</p>
        </main>
    </body>
</html>
