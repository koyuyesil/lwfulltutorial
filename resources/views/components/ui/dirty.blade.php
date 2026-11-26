{{--
    Kaydedilmemiş değişiklik göstergesi (Livewire wire:dirty). target: izlenecek özellik (ör. form, company).
    Görünürken sayfadan ayrılma / wire:navigate öncesi uyarı verilir (resources/js/ui.js).
--}}
@props(['target' => null])

<span wire:dirty.inline-flex @if ($target) wire:target="{{ $target }}" @endif data-dirty-flag style="display: none"
    {{ $attributes->class('inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400') }}>
    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"></span>
    Kaydedilmemiş değişiklikler
</span>
