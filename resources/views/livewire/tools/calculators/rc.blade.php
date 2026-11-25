@php $mono = 'font-family="ui-monospace,monospace"'; @endphp
<div x-data="rcCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Direnç (R)" model="r" unit="Ω" />
            <x-calc.input label="Kapasite (C)" model="c" unit="F" />
        </div>
        <template x-if="result">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Zaman sabiti" value="$si(result.tau, 's')" accent hint="τ = R × C · %63 şarj" />
                <x-calc.result label="Kesim frekansı" value="$si(result.fc, 'Hz')" accent hint="−3 dB, birinci derece RC filtre" />
                <x-calc.result label="%95 şarj" value="$si(result.t95, 's')" hint="3τ" />
                <x-calc.result label="%99 şarj" value="$si(result.t99, 's')" hint="5τ" />
            </div>
        </template>
        <x-calc.formula>τ = R × C &nbsp;·&nbsp; fc = 1 / (2π R C) &nbsp;·&nbsp; Vc(t) = V (1 − e<sup>−t/τ</sup>)</x-calc.formula>
    </div>

    <x-calc.stage label="Şarj / deşarj eğrisi" badge="0 → 5τ">
        <svg viewBox="0 0 400 230" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="rc-fill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#38bdf8" stop-opacity=".35" /><stop offset="1" stop-color="#38bdf8" stop-opacity="0" /></linearGradient>
            </defs>
            <g transform="translate(40 20)">
                {{-- ızgara --}}
                @foreach ([0, 0.25, 0.5, 0.75, 1] as $g)
                    <line x1="0" x2="340" y1="{{ 150 - $g * 150 }}" y2="{{ 150 - $g * 150 }}" stroke="#334155" stroke-dasharray="{{ $g ? '3 4' : '' }}" />
                    <text x="-8" y="{{ 154 - $g * 150 }}" text-anchor="end" fill="#64748b" {!! $mono !!} font-size="9">{{ $g * 100 }}%</text>
                @endforeach
                {{-- 1τ / 3τ / 5τ işaretleri --}}
                @foreach ([[1, 63.2, 'sky'], [3, 95, 'violet'], [5, 99.3, 'emerald']] as [$k, $pct, $tone])
                    @php $x = $k / 5 * 340; $y = 150 - $pct / 100 * 150; @endphp
                    <line x1="{{ $x }}" x2="{{ $x }}" y1="0" y2="150" stroke="#475569" stroke-dasharray="2 3" />
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="4.5" fill="#0b1220" stroke="#38bdf8" stroke-width="2" />
                    <text x="{{ $x - ($k === 5 ? 4 : -6) }}" y="{{ $y + 16 }}" text-anchor="{{ $k === 5 ? 'end' : 'start' }}" fill="#bae6fd" {!! $mono !!} font-size="9" font-weight="700">{{ $pct }}%</text>
                    <text x="{{ $x }}" y="166" text-anchor="middle" fill="#cbd5e1" {!! $mono !!} font-size="10" font-weight="700">{{ $k }}τ</text>
                    <text x="{{ $x }}" y="180" text-anchor="middle" fill="#64748b" {!! $mono !!} font-size="9" x-text="result ? $si(result.tau * {{ $k }}, 's', 3) : ''"></text>
                @endforeach
                <path :d="curve() + ' L340 150 L0 150 Z'" fill="url(#rc-fill)" />
                <path :d="curve(340, 150, true)" stroke="#f472b6" stroke-width="2" stroke-dasharray="6 4" />
                <path :d="curve()" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" />
                {{-- eğri üzerinde gezen nokta --}}
                <circle r="6" fill="#f8fafc" stroke="#38bdf8" stroke-width="3">
                    <animateMotion dur="4s" repeatCount="indefinite" :path="curve()" />
                </circle>
                <text x="14" y="22" fill="#f472b6" {!! $mono !!} font-size="9" font-weight="700">deşarj</text>
                <text x="250" y="18" fill="#38bdf8" {!! $mono !!} font-size="9" font-weight="700">şarj Vc(t)</text>
            </g>
        </svg>
        <div class="mt-1 grid grid-cols-2 gap-2 text-center">
            <div class="rounded-lg bg-white/5 px-2 py-2 ring-1 ring-white/10">
                <p class="text-[10px] uppercase tracking-wider text-slate-400">Alçak geçiren · fc</p>
                <p class="font-mono text-sm font-black text-sky-300" x-text="result ? $si(result.fc, 'Hz', 3) : '—'"></p>
            </div>
            <div class="rounded-lg bg-white/5 px-2 py-2 ring-1 ring-white/10">
                <p class="text-[10px] uppercase tracking-wider text-slate-400">%63 şarj · 1τ</p>
                <p class="font-mono text-sm font-black text-sky-300" x-text="result ? $si(result.tau, 's', 3) : '—'"></p>
            </div>
        </div>
    </x-calc.stage>
</div>
