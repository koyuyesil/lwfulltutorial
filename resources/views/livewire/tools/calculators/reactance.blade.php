@php $mono = 'font-family="ui-monospace,monospace"'; @endphp
<div x-data="reactanceCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="grid gap-4 sm:grid-cols-3">
            <x-calc.input label="Frekans (f)" model="f" unit="Hz" />
            <x-calc.input label="Endüktans (L)" model="l" unit="H" />
            <x-calc.input label="Kapasite (C)" model="c" unit="F" />
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="XL (bobin)" value="$si(result.XL, 'Ω')" />
            <x-calc.result label="XC (kond.)" value="$si(result.XC, 'Ω')" />
            <x-calc.result label="Rezonans f0" value="$si(result.f0, 'Hz')" accent class="sm:col-span-2" />
        </div>
        <x-calc.formula>XL = 2π f L &nbsp;·&nbsp; XC = 1 / (2π f C) &nbsp;·&nbsp; f0 = 1 / (2π √(L C))</x-calc.formula>
    </div>

    <x-calc.stage label="Reaktans / frekans" badge="log–log · f0 ±3 dekad">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <g transform="translate(30 14)">
                @foreach (range(0, 6) as $i)
                    <line x1="{{ $i * 60 }}" x2="{{ $i * 60 }}" y1="0" y2="200" stroke="#1e293b" />
                @endforeach
                @foreach (range(0, 4) as $i)
                    <line x1="0" x2="360" y1="{{ $i * 50 }}" y2="{{ $i * 50 }}" stroke="#1e293b" />
                @endforeach
                <rect width="360" height="200" stroke="#334155" />
                <path :d="chart ? chart.xl : ''" stroke="#c084fc" stroke-width="3" stroke-linecap="round" />
                <path :d="chart ? chart.xc : ''" stroke="#fbbf24" stroke-width="3" stroke-linecap="round" />
                {{-- rezonans --}}
                <g x-show="chart && chart.f0">
                    <line :x1="chart?.f0?.x" :x2="chart?.f0?.x" y1="0" y2="200" stroke="#34d399" stroke-dasharray="4 4" />
                    <circle :cx="chart?.f0?.x" :cy="chart?.f0?.y" r="7" fill="#34d399" opacity=".25" />
                    <circle :cx="chart?.f0?.x" :cy="chart?.f0?.y" r="4" fill="#34d399" />
                    <text :x="(chart?.f0?.x ?? 0) + 8" y="14" fill="#6ee7b7" {!! $mono !!} font-size="10" font-weight="800" x-text="'f0 ' + $si(result.f0, 'Hz', 3)"></text>
                </g>
                {{-- seçili frekans --}}
                <g x-show="chart && chart.at">
                    <line :x1="chart?.at?.x" :x2="chart?.at?.x" y1="0" y2="200" stroke="#f8fafc" stroke-opacity=".5" />
                    <circle x-show="chart?.at?.yl !== null" :cx="chart?.at?.x" :cy="chart?.at?.yl ?? -10" r="4.5" fill="#0b1220" stroke="#c084fc" stroke-width="2.5" />
                    <circle x-show="chart?.at?.yc !== null" :cx="chart?.at?.x" :cy="chart?.at?.yc ?? -10" r="4.5" fill="#0b1220" stroke="#fbbf24" stroke-width="2.5" />
                    <text :x="(chart?.at?.x ?? 0) + 6" y="194" fill="#e2e8f0" {!! $mono !!} font-size="10" x-text="'f ' + f + 'Hz'"></text>
                </g>
                <text x="0" y="216" fill="#64748b" {!! $mono !!} font-size="9" x-text="chart ? $si(chart.fLo, 'Hz', 2) : ''"></text>
                <text x="360" y="216" text-anchor="end" fill="#64748b" {!! $mono !!} font-size="9" x-text="chart ? $si(chart.fHi, 'Hz', 2) : ''"></text>
            </g>
        </svg>
        <div class="flex flex-wrap items-center justify-center gap-4 text-[11px] text-slate-300">
            <span class="flex items-center gap-1.5"><span class="h-1 w-4 rounded bg-violet-400"></span>XL (bobin) artar</span>
            <span class="flex items-center gap-1.5"><span class="h-1 w-4 rounded bg-amber-400"></span>XC (kondansatör) azalır</span>
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>kesişim = rezonans</span>
        </div>
    </x-calc.stage>
</div>
