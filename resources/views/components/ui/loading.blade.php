{{--
    Liste / kart üstünde ince, belirsiz ilerleme çubuğu. Kapsayıcının "relative" olması gerekir.
    target: wire:target (virgülle birden çok eylem). 150 ms'den kısa işlemlerde görünmez.
--}}
@props(['target' => null])

<div wire:loading.delay @if ($target) wire:target="{{ $target }}" @endif
    {{ $attributes->class('pointer-events-none absolute inset-x-0 top-0 z-10 h-0.5 overflow-hidden rounded-full') }} role="progressbar" aria-label="Yükleniyor">
    <div class="ui-progress h-full w-1/3 rounded-full bg-indigo-500"></div>
</div>
