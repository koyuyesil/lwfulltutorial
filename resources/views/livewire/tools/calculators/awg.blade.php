@php $mono = 'font-family="ui-monospace,monospace"'; @endphp
<div x-data="awgCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
            <button type="button" @click="mode = 'awg'" :class="mode === 'awg' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">AWG → mm²</button>
            <button type="button" @click="mode = 'mm2'" :class="mode === 'mm2' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">mm² → AWG</button>
        </div>
        <div class="max-w-xs">
            <template x-if="mode === 'awg'"><x-calc.input label="AWG" model="awg" :si="false" step="1" min="0" max="40" hint="örn. 22, 30 ya da 2/0" /></template>
            <template x-if="mode === 'mm2'"><x-calc.input label="Kesit" model="mm2" unit="mm²" /></template>
        </div>
        <template x-if="result">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Kesit" value="$num(result.area, 4) + ' mm²'" accent />
                <x-calc.result label="AWG" value="mode === 'mm2' ? $num(result.awg, 3) + ' (≈ ' + result.nearest + ')' : String(awg)" />
                <x-calc.result label="Çap" value="$num(result.d, 4) + ' mm'" />
                <x-calc.result label="Direnç (bakır)" value="$si(result.rPerM, 'Ω') + '/m'" />
            </div>
        </template>
        <x-calc.formula>d = 0,127 mm × 92<sup>(36 − AWG) / 39</sup> &nbsp;·&nbsp; A = π d² / 4 &nbsp;·&nbsp; ρ<sub>Cu</sub> = 0,0172 Ω·mm²/m</x-calc.formula>
    </div>

    <x-calc.stage label="Kesit ve yan görünüm" badge="ölçekli">
        <svg viewBox="0 0 400 220" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <radialGradient id="awg-cu" cx=".38" cy=".35" r=".7"><stop offset="0" stop-color="#fed7aa" /><stop offset=".5" stop-color="#f97316" /><stop offset="1" stop-color="#9a3412" /></radialGradient>
                <linearGradient id="awg-side" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fdba74" /><stop offset=".35" stop-color="#fff7ed" /><stop offset=".6" stop-color="#ea580c" /><stop offset="1" stop-color="#7c2d12" /></linearGradient>
                <linearGradient id="awg-ins" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f87171" /><stop offset=".35" stop-color="#fecaca" /><stop offset=".6" stop-color="#dc2626" /><stop offset="1" stop-color="#7f1d1d" /></linearGradient>
            </defs>
            {{-- Kesit (ölçekli) --}}
            <g transform="translate(95 105)">
                <circle :r="result ? Math.min(88, result.d / 2 * pxPerMm + 12) : 30" fill="#991b1b" stroke="#ef4444" stroke-width="2" />
                <circle :r="result ? Math.min(76, result.d / 2 * pxPerMm) : 18" fill="url(#awg-cu)" />
                <line :x1="result ? -Math.min(76, result.d / 2 * pxPerMm) : -18" :x2="result ? Math.min(76, result.d / 2 * pxPerMm) : 18" y1="0" y2="0" stroke="#0f172a" stroke-width="1.5" stroke-dasharray="3 2" />
                <text y="-4" text-anchor="middle" fill="#0f172a" {!! $mono !!} font-size="11" font-weight="900" x-text="result ? $num(result.d, 3) + ' mm' : ''"></text>
            </g>
            {{-- Ölçek çubuğu --}}
            <g transform="translate(20 206)">
                <line x1="0" x2="0" y1="-5" y2="5" stroke="#94a3b8" /><line :x2="pxPerMm" x1="0" y1="0" y2="0" stroke="#94a3b8" stroke-width="2" /><line :x1="pxPerMm" :x2="pxPerMm" y1="-5" y2="5" stroke="#94a3b8" />
                <text :x="pxPerMm + 6" y="4" fill="#94a3b8" {!! $mono !!} font-size="10">1 mm</text>
            </g>
            {{-- Yan görünüm: soyulmuş uç (3D silindir) --}}
            <g transform="translate(210 105)">
                <rect x="0" y="-20" width="120" height="40" rx="8" fill="url(#awg-ins)" />
                <ellipse cx="120" cy="0" rx="8" ry="20" fill="#b91c1c" />
                <rect x="118" y="-10" width="56" height="20" rx="3" fill="url(#awg-side)" />
                <ellipse cx="174" cy="0" rx="4" ry="10" fill="#fdba74" stroke="#9a3412" />
                <text x="60" y="44" text-anchor="middle" fill="#cbd5e1" {!! $mono !!} font-size="11" font-weight="800" x-text="result ? 'AWG ' + (mode === 'mm2' ? result.nearest : awg) : ''"></text>
                <text x="60" y="60" text-anchor="middle" fill="#64748b" {!! $mono !!} font-size="10" x-text="result ? $num(result.area, 3) + ' mm²' : ''"></text>
            </g>
        </svg>
        <template x-if="ampacity">
            <div class="grid grid-cols-2 gap-2 text-center">
                <div class="rounded-lg bg-white/5 px-2 py-2 ring-1 ring-white/10"><p class="text-[10px] uppercase tracking-wider text-slate-400">güvenli (~4 A/mm²)</p><p class="font-mono text-sm font-black text-emerald-300" x-text="$si(ampacity.safe, 'A', 3)"></p></div>
                <div class="rounded-lg bg-white/5 px-2 py-2 ring-1 ring-white/10"><p class="text-[10px] uppercase tracking-wider text-slate-400">kısa jumper (~8 A/mm²)</p><p class="font-mono text-sm font-black text-amber-300" x-text="$si(ampacity.short, 'A', 3)"></p></div>
            </div>
        </template>
    </x-calc.stage>
</div>
