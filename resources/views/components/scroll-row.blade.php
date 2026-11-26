{{--
    Yatay kaydırmalı çip / sekme satırı. Kaydırma çubuğu gizlidir; devamı olan kenarda yumuşak solma ve ok düğmesi çıkar.
    Seçili öğeyi aria-current, aria-pressed="true" ya da data-active="true" ile işaretleyin: görünür alana kaydırılır.
    track: iç satıra eklenecek sınıflar (gap, padding, lg:flex-col …).
--}}
@props(['track' => 'gap-1.5', 'label' => null])

<div x-data="scrollRow" {{ $attributes->class('relative min-w-0') }}>
    <div x-ref="track" @scroll.passive="update()" @wheel="wheel($event)" :style="mask"
        @if ($label) role="group" aria-label="{{ $label }}" @endif
        class="flex overflow-x-auto overscroll-x-contain [scrollbar-width:none] [&::-webkit-scrollbar]:hidden {{ $track }}">
        {{ $slot }}
    </div>
    <button type="button" x-show="canLeft" x-transition.opacity x-cloak @click="by(-1)" tabindex="-1" aria-hidden="true"
        class="absolute left-0 top-1/2 z-10 grid h-7 w-7 -translate-y-1/2 place-items-center rounded-full bg-white text-slate-600 shadow-md ring-1 ring-slate-200 transition hover:text-slate-900 dark:bg-slate-700 dark:text-slate-200 dark:ring-white/10">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
    </button>
    <button type="button" x-show="canRight" x-transition.opacity x-cloak @click="by(1)" tabindex="-1" aria-hidden="true"
        class="absolute right-0 top-1/2 z-10 grid h-7 w-7 -translate-y-1/2 place-items-center rounded-full bg-white text-slate-600 shadow-md ring-1 ring-slate-200 transition hover:text-slate-900 dark:bg-slate-700 dark:text-slate-200 dark:ring-white/10">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    </button>
</div>
