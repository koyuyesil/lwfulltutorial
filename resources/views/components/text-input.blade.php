@props(['disabled' => false])

{{-- Eski Breeze bileşeni: x-ui.input ile aynı görünüm --}}
<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 disabled:opacity-60 dark:bg-slate-950 dark:text-white dark:ring-white/10']) }}>