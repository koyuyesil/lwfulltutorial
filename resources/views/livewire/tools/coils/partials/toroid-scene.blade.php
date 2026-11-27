{{-- Toroid sahnesi (AL ve µ sekmeleri ortak). $id: gradyan öneki, $label: sağ üst metin (Alpine ifadesi) --}}
<svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
    <defs>
        <linearGradient id="{{ $id }}-cu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".4" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
        <linearGradient id="{{ $id }}-core" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#64748b" /><stop offset=".45" stop-color="#334155" /><stop offset="1" stop-color="#0f172a" /></linearGradient>
    </defs>
    <ellipse cx="200" cy="200" :rx="geo.R + geo.r" ry="12" fill="#000" opacity=".35" />
    <path :d="geo.back" stroke="#7c2d12" stroke-width="2.2" stroke-linecap="round" opacity=".7" />
    {{-- Çekirdek: yan duvar + üst yüzey --}}
    <ellipse cx="200" :cy="120 + geo.wall * 0.5" :rx="geo.R" :ry="geo.ry" stroke="#0f172a" :stroke-width="geo.r * 2" />
    <ellipse cx="200" cy="120" :rx="geo.R" :ry="geo.ry" stroke="url(#{{ $id }}-core)" :stroke-width="geo.r * 2" />
    <ellipse cx="200" cy="120" :rx="geo.R + geo.r - 1" :ry="geo.ry + geo.r * 0.5" stroke="#94a3b8" stroke-opacity=".25" />
    {{-- Alan: çekirdeğin içinde kapalı halkalar (dışarıya neredeyse hiç taşmaz) --}}
    <g :opacity="density.opacity">
        @foreach (range(0, 4) as $j)
            <g x-show="geo.field[{{ $j }}]">
                <ellipse cx="200" cy="120" :rx="geo.field[{{ $j }}]?.rx" :ry="geo.field[{{ $j }}]?.ry" stroke="#7dd3fc" stroke-width="1.5" class="calc-flow"
                    :style="{ animationDuration: density.speed + 's', animationPlayState: flowState }" />
                @foreach ([0, 1] as $k)
                    <path :d="geo.field[{{ $j }}] ? arrow(geo.field[{{ $j }}].arrows[{{ $k }}].x, geo.field[{{ $j }}].arrows[{{ $k }}].y, geo.field[{{ $j }}].arrows[{{ $k }}].ang, 4.5) : ''" fill="#e0f2fe" />
                @endforeach
            </g>
        @endforeach
    </g>
    <path :d="geo.front" stroke="url(#{{ $id }}-cu)" stroke-width="2.6" stroke-linecap="round" />
    <path :d="geo.front" stroke="#fff7ed" stroke-width=".7" stroke-linecap="round" opacity=".5" />
    <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
        <text x="16" y="22" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 3) : 'N ?'"></text>
        <text x="384" y="22" text-anchor="end" fill="#a5b4fc" x-text="{{ $label }}"></text>
    </g>
</svg>
