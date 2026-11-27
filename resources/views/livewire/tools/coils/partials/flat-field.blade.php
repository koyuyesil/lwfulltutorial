{{-- Düz bobin (spiral / PCB) alan çizgileri: eğik bobinin üstünde, dönmeyen katman. geo.field: flatFieldLines --}}
<svg viewBox="-110 -110 220 220" class="pointer-events-none absolute inset-0 h-full w-full overflow-visible" fill="none" aria-hidden="true">
    <g :opacity="density.opacity" transform="translate(0 4)">
        @foreach (range(0, 9) as $i)
            <g x-show="geo.field[{{ $i }}]">
                <path :d="geo.field[{{ $i }}]?.d" stroke="#38bdf8" stroke-opacity=".22" stroke-width="1" />
                <path :d="geo.field[{{ $i }}]?.d" stroke="#7dd3fc" stroke-width="1.3" stroke-linecap="round" class="calc-flow"
                    :style="{ animationDuration: density.speed + 's', animationPlayState: flowState }" />
                <path :d="geo.field[{{ $i }}] ? arrow(geo.field[{{ $i }}].arrows[0].x, geo.field[{{ $i }}].arrows[0].y, geo.field[{{ $i }}].arrows[0].a, 3.5) : ''" fill="#e0f2fe" />
            </g>
        @endforeach
        <g font-family="ui-sans-serif" font-size="9" font-weight="900" text-anchor="middle">
            <circle cx="0" cy="-12" r="7" fill="#be123c" fill-opacity=".85" /><text x="0" y="-8.8" fill="#fff">N</text>
        </g>
    </g>
</svg>
