{{-- Eski Breeze bileşeni: x-ui.button (secondary) ile aynı görünüm --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 transition hover:bg-slate-50 disabled:opacity-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5']) }}>
    {{ $slot }}
</button>