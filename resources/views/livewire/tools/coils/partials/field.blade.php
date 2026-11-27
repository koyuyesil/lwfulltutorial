{{--
    Manyetik alan çizgileri (dipol biçimi). $lines: Alpine ifadesi (fieldLines dizisi), $x1 / $x2: S ve N uçlarının x'i (Alpine), $cy: eksen y'si.
    Yoğunluk ve akış hızı endüktansa göre (density); "Durdur" akışı da durdurur (flowState).
--}}
@php $cy = $cy ?? 120; $poles = $poles ?? true; @endphp
<g :opacity="density.opacity">
    @foreach (range(0, 9) as $i)
        <g x-show="{{ $lines }}[{{ $i }}]">
            <path :d="{{ $lines }}[{{ $i }}]?.d" stroke="#38bdf8" stroke-opacity=".22" stroke-width="1.2" />
            <path :d="{{ $lines }}[{{ $i }}]?.d" stroke="#7dd3fc" stroke-width="1.6" stroke-linecap="round" class="calc-flow"
                :style="{ animationDuration: density.speed + 's', animationPlayState: flowState }" />
            <path :d="{{ $lines }}[{{ $i }}] ? arrow({{ $lines }}[{{ $i }}].arrows[1].x, {{ $lines }}[{{ $i }}].arrows[1].y, {{ $lines }}[{{ $i }}].arrows[1].a) : ''" fill="#bae6fd" />
            <path x-show="{{ $i }} < 2" :d="{{ $lines }}[{{ $i }}] ? arrow({{ $lines }}[{{ $i }}].arrows[0].x, {{ $lines }}[{{ $i }}].arrows[0].y, {{ $lines }}[{{ $i }}].arrows[0].a, 4) : ''" fill="#e0f2fe" />
        </g>
    @endforeach
</g>
@if ($poles)
    {{-- Kutuplar: alan N ucundan çıkar, S ucuna döner --}}
    <g font-family="ui-sans-serif" font-size="13" font-weight="900" text-anchor="middle">
        <circle :cx="{{ $x2 }} + 18" cy="{{ $cy }}" r="10" fill="#be123c" fill-opacity=".85" stroke="#fda4af" stroke-opacity=".6" />
        <text :x="{{ $x2 }} + 18" y="{{ $cy + 4.5 }}" fill="#fff">N</text>
        <circle :cx="{{ $x1 }} - 18" cy="{{ $cy }}" r="10" fill="#1d4ed8" fill-opacity=".85" stroke="#93c5fd" stroke-opacity=".6" />
        <text :x="{{ $x1 }} - 18" y="{{ $cy + 4.5 }}" fill="#fff">S</text>
    </g>
@endif
