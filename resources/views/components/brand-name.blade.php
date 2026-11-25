{{-- "KoyuMavi Servis" yazı logosu. dark: koyu zemin üzerinde (sol menü, giriş paneli) --}}
@props(['dark' => false, 'suffix' => true])

<span {{ $attributes->class(['font-extrabold tracking-tight', 'text-white' => $dark, 'text-slate-900 dark:text-white' => ! $dark]) }}>Koyu<span @class(['text-sky-400' => $dark, 'text-sky-600 dark:text-sky-400' => ! $dark])>Mavi</span>@if ($suffix)<span @class(['ms-1 font-semibold', 'text-slate-400' => $dark, 'text-slate-400 dark:text-slate-500' => ! $dark])>Servis</span>@endif</span>
