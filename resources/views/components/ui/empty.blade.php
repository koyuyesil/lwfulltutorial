{{--
    Boş durum: emoji ya da simge, başlık, açıklama ve eylem düğmeleri (slot).
    title verilmezse yalnızca kabuk çizilir ve slot olduğu gibi yerleşir (koşullu başlıklar için).
--}}
@props(['emoji' => null, 'icon' => null, 'title' => null, 'description' => null])

<div {{ $attributes->class('rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-14 text-center dark:border-white/10 dark:bg-slate-800/50') }}>
    @if ($emoji)
        <p class="text-4xl" aria-hidden="true">{{ $emoji }}</p>
    @elseif ($icon)
        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
    @endif
    @if ($title)
        <p class="mt-3 font-semibold text-slate-800 dark:text-white">{{ $title }}</p>
        @if ($description)<p class="mx-auto mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>@endif
        @if (trim($slot))<div class="mt-5 flex flex-wrap justify-center gap-2">{{ $slot }}</div>@endif
    @else
        {{ $slot }}
    @endif
</div>
