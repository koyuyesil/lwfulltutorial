{{-- Sayfa başlığı: üst etiket (eyebrow), başlık, açıklama ve sağda eylemler (slot). --}}
@props(['title', 'description' => null, 'eyebrow' => null])

<div {{ $attributes->class('flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="min-w-0">
        @if ($eyebrow)<p class="text-xs font-bold uppercase tracking-[.2em] text-indigo-600 dark:text-indigo-400">{{ $eyebrow }}</p>@endif
        <h1 class="truncate text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
        @if ($description)<p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>@endif
    </div>
    @if (trim($slot))<div class="flex shrink-0 flex-wrap items-center gap-2">{{ $slot }}</div>@endif
</div>
