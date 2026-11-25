@php $mono = 'font-family="ui-monospace,monospace"'; @endphp
<div x-data="capCodeCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <x-calc.input label="Kondansatör kodu" model="code" :si="false" placeholder="104, 473K, 4n7" />

        <div class="flex flex-wrap gap-1.5">
            <span class="text-xs font-semibold text-slate-500">Örnekler:</span>
            <template x-for="ex in examples" :key="ex">
                <button type="button" @click="code = ex" :class="code.toUpperCase() === ex.toUpperCase() ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300'"
                    class="rounded-full px-2.5 py-1 font-mono text-xs font-semibold" x-text="ex"></button>
            </template>
        </div>

        <template x-if="result && result.error"><p class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-300" x-text="result.error"></p></template>
        <template x-if="result && ! result.error">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="pF" value="$num(result.farad * 1e12) + ' pF'" />
                <x-calc.result label="nF" value="$num(result.farad * 1e9) + ' nF'" accent />
                <x-calc.result label="µF" value="$num(result.farad * 1e6) + ' µF'" />
                <x-calc.result label="Tolerans" value="result.tol ?? '—'" />
            </div>
        </template>

        <x-calc.formula>3 haneli kod pF'tır: ilk iki rakam × 10<sup>üçüncü</sup> · 104 = 10 × 10⁴ pF = 100 nF · harf toleranstır (J %5, K %10, M %20)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model · seramik disk" :badge="null">
        <svg viewBox="0 0 400 230" class="w-full" aria-hidden="true">
            <defs>
                <radialGradient id="cap-face" cx=".38" cy=".32" r=".75"><stop offset="0" stop-color="#fdba74" /><stop offset=".55" stop-color="#ea580c" /><stop offset="1" stop-color="#9a3412" /></radialGradient>
                <linearGradient id="cap-edge" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#7c2d12" /><stop offset=".5" stop-color="#c2410c" /><stop offset="1" stop-color="#431407" /></linearGradient>
                <linearGradient id="cap-leg" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#64748b" /><stop offset=".5" stop-color="#f1f5f9" /><stop offset="1" stop-color="#64748b" /></linearGradient>
            </defs>
            <ellipse cx="200" cy="218" rx="120" ry="8" fill="#000" opacity=".4" />
            <rect x="170" y="140" width="5" height="78" fill="url(#cap-leg)" />
            <rect x="225" y="140" width="5" height="78" fill="url(#cap-leg)" />
            <g class="calc-float">
                {{-- Disk: kenar (kalınlık) + ön yüz → 3D madeni para --}}
                <ellipse cx="208" cy="92" rx="76" ry="72" fill="url(#cap-edge)" />
                <ellipse cx="200" cy="90" rx="76" ry="72" fill="url(#cap-face)" />
                <ellipse cx="176" cy="60" rx="30" ry="16" fill="#fff" opacity=".18" transform="rotate(-25 176 60)" />
                <text x="200" y="98" text-anchor="middle" fill="#fff7ed" {!! $mono !!} font-size="30" font-weight="900" letter-spacing="2" x-text="(code || '—').toUpperCase().slice(0, 5)"></text>
                <text x="200" y="124" text-anchor="middle" fill="#fed7aa" {!! $mono !!} font-size="11" font-weight="700" x-text="result && ! result.error ? $si(result.farad, 'F', 3) : ''"></text>
            </g>
        </svg>
        {{-- Log ölçek 1 pF … 10 mF --}}
        <div class="mt-2">
            <div class="relative h-2.5 rounded-full bg-linear-to-r from-sky-500 via-amber-400 to-rose-500">
                <span class="absolute top-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-slate-900 shadow-lg transition-all duration-500" :style="{ left: scale + '%' }"></span>
            </div>
            <div class="relative mt-1.5 h-3 font-mono text-[10px] text-slate-400">
                @foreach (['1 pF' => 0, '1 nF' => 30, '1 µF' => 60, '1 mF' => 90] as $l => $pos)
                    <span class="absolute {{ $pos ? '-translate-x-1/2' : '' }}" style="left: {{ $pos }}%">{{ $l }}</span>
                @endforeach
            </div>
            <p class="mt-2 text-center text-xs text-slate-300" x-text="usage"></p>
        </div>
    </x-calc.stage>
</div>
