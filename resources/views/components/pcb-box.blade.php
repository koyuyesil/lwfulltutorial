{{--
    CSS 3D kutu (PCB bileşeni). Üst yüz translateZ(depth) ile yükselir, dört yan yüz kartla üst yüzü birleştirir.
    x, y, w, h: sahne koordinatı (px). depth: Alpine ifadesi (yükseklik, px). Slot: üst yüz içeriği.
    side / side2: doğu-batı yüzleri (side2 = ışık almayan), ns / ns2: güney-kuzey yüzleri.
--}}
@props(['x', 'y', 'w', 'h', 'depth', 'top' => '#111827', 'side' => '#1f2937', 'side2' => '#030712', 'ns' => null, 'ns2' => null, 'radius' => 2])
@php
    $ns ??= $side;
    $ns2 ??= $side2;
@endphp

<div {{ $attributes->merge(['class' => 'absolute']) }}
    style="left: {{ $x }}px; top: {{ $y }}px; width: {{ $w }}px; height: {{ $h }}px; transform-style: preserve-3d;">
    {{-- Kartın üzerine düşen gölge --}}
    <div class="absolute inset-0 rounded-sm transition-all duration-500"
        :style="`background: rgba(0,0,0,.5); filter: blur(${ ({{ $depth }}) * 0.45 }px); transform: translate(${ ({{ $depth }}) * 0.5 }px, ${ ({{ $depth }}) * 0.5 }px)`"></div>
    {{-- Yan yüzler: kuzey, güney, batı, doğu --}}
    <div class="absolute transition-all duration-500" :style="`left:0; top:0; width:{{ $w }}px; height:${ {{ $depth }} }px; background:{{ $ns2 }}; transform-origin:top; transform:rotateX(90deg)`"></div>
    <div class="absolute transition-all duration-500" :style="`left:0; top:{{ $h }}px; width:{{ $w }}px; height:${ {{ $depth }} }px; background:{{ $ns }}; transform-origin:top; transform:rotateX(90deg)`"></div>
    <div class="absolute transition-all duration-500" :style="`left:0; top:0; width:${ {{ $depth }} }px; height:{{ $h }}px; background:{{ $side2 }}; transform-origin:left; transform:rotateY(-90deg)`"></div>
    <div class="absolute transition-all duration-500" :style="`left:{{ $w }}px; top:0; width:${ {{ $depth }} }px; height:{{ $h }}px; background:{{ $side }}; transform-origin:left; transform:rotateY(-90deg)`"></div>
    {{-- Üst yüz --}}
    <div class="absolute inset-0 overflow-hidden transition-transform duration-500"
        style="background: {{ $top }}; border-radius: {{ $radius }}px;"
        :style="{ transform: `translateZ(${ {{ $depth }} }px)` }">
        {{ $slot }}
    </div>
</div>
