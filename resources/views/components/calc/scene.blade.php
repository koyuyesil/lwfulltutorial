{{--
    Gerçek CSS 3D sahne (Alpine scene3d). İçerik w×h düzlemde mutlak konumlanır; 3D kutular için <x-pcb-box>.
    Sürükleyerek döndürülür, çift tık başlangıç açısına döner.
--}}
@props(['w' => 360, 'h' => 220, 'rx' => 58, 'rz' => -30])

<div x-data="scene3d({{ $w }}, {{ $h }}, {{ $rx }}, {{ $rz }})" {{ $attributes->class('relative w-full touch-none select-none cursor-grab active:cursor-grabbing') }}
    :style="wrapStyle" @pointerdown="start($event)" @pointermove="move($event)" @pointerup="end()" @pointerleave="end()" @dblclick="reset()"
    title="Sürükleyerek döndürün · çift tık: sıfırla">
    <div class="absolute top-0" :style="stageStyle">
        <div class="relative" :style="sceneStyle">
            {{ $slot }}
        </div>
    </div>
</div>
