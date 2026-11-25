<div x-data="ohmCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Gerilim (V)" model="v" unit="V" placeholder="örn. 5" />
            <x-calc.input label="Akım (I)" model="i" unit="A" placeholder="örn. 20m" />
            <x-calc.input label="Direnç (R)" model="r" unit="Ω" placeholder="örn. 4k7" />
            <x-calc.input label="Güç (P)" model="p" unit="W" placeholder="örn. 0.25" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <div class="flex items-center gap-2">
                @foreach (['v' => 'V', 'i' => 'I', 'r' => 'R', 'p' => 'P'] as $k => $l)
                    <span :class="given.includes('{{ $k }}') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : (result ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200' : 'bg-slate-100 text-slate-400 dark:bg-white/5')"
                        class="grid h-7 w-7 place-items-center rounded-lg font-mono text-xs font-black transition">{{ $l }}</span>
                @endforeach
                <span :class="given.length > 2 ? 'text-rose-600' : 'text-slate-500'" class="ml-1 text-xs"
                    x-text="given.length < 2 ? `İki değer girin (${given.length}/2)` : (given.length > 2 ? 'Yalnızca iki değer girin' : 'Koyu: girilen · mavi: hesaplanan')"></span>
            </div>
            <button type="button" @click="clear()" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Temizle</button>
        </div>

        <template x-if="result">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Gerilim" value="$si(result.V, 'V')" ::class="given.includes('v') ? '' : 'ring-2 ring-indigo-500'" />
                <x-calc.result label="Akım" value="$si(result.I, 'A')" ::class="given.includes('i') ? '' : 'ring-2 ring-indigo-500'" />
                <x-calc.result label="Direnç" value="$si(result.R, 'Ω')" ::class="given.includes('r') ? '' : 'ring-2 ring-indigo-500'" />
                <x-calc.result label="Güç" value="$si(result.P, 'W')" ::class="given.includes('p') ? '' : 'ring-2 ring-indigo-500'" />
            </div>
        </template>

        <x-calc.formula>V = I × R &nbsp;·&nbsp; P = V × I = I² × R = V² / R</x-calc.formula>
    </div>

    <x-calc.stage label="Canlı devre" badge="akım akışı ve ısınma">
        <svg viewBox="0 0 400 250" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <radialGradient id="ohm-heat"><stop offset="0" :stop-color="heatColor" stop-opacity=".9" /><stop offset="1" :stop-color="heatColor" stop-opacity="0" /></radialGradient>
                <linearGradient id="ohm-bat" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#334155" /><stop offset=".45" stop-color="#94a3b8" /><stop offset="1" stop-color="#1e293b" /></linearGradient>
            </defs>
            {{-- Kablolar --}}
            <path d="M60 95 V40 H340 V210 H60 V155" stroke="#475569" stroke-width="5" stroke-linejoin="round" />
            <path d="M60 95 V40 H340 V210 H60 V155" stroke="#fbbf24" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"
                class="calc-flow" x-show="flow > 0" :style="{ animationDuration: flow + 's' }" />

            {{-- Batarya (silindir) --}}
            <rect x="38" y="95" width="44" height="60" rx="6" fill="url(#ohm-bat)" stroke="#0f172a" />
            <rect x="50" y="89" width="20" height="7" rx="2" fill="#cbd5e1" />
            <text x="60" y="122" text-anchor="middle" fill="#0f172a" font-family="ui-monospace,monospace" font-size="15" font-weight="900">+</text>
            <text x="60" y="146" text-anchor="middle" fill="#0f172a" font-family="ui-monospace,monospace" font-size="15" font-weight="900">−</text>
            <text x="92" y="118" fill="#fde68a" font-family="ui-monospace,monospace" font-size="14" font-weight="800" x-text="result ? $si(result.V, 'V', 3) : 'V ?'"></text>

            {{-- Direnç (ısındıkça kızarır) --}}
            <ellipse cx="200" cy="40" rx="70" ry="34" fill="url(#ohm-heat)" :opacity="heat" />
            <rect x="160" y="28" width="80" height="24" rx="6" :fill="heatColor" stroke="#0f172a" stroke-width="2" />
            <text x="200" y="45" text-anchor="middle" fill="#0f172a" font-family="ui-monospace,monospace" font-size="12" font-weight="900" x-text="result ? $si(result.R, 'Ω', 3) : 'R ?'"></text>
            <text x="200" y="78" text-anchor="middle" fill="#fca5a5" font-family="ui-monospace,monospace" font-size="11" font-weight="700" x-show="result" x-text="result ? 'P = ' + $si(result.P, 'W', 3) : ''"></text>

            {{-- Ampermetre --}}
            <circle cx="340" cy="125" r="30" fill="#0f172a" stroke="#38bdf8" stroke-width="3" />
            <text x="340" y="118" text-anchor="middle" fill="#38bdf8" font-family="ui-sans-serif" font-size="13" font-weight="900">A</text>
            <text x="340" y="136" text-anchor="middle" fill="#e2e8f0" font-family="ui-monospace,monospace" font-size="10" font-weight="700" x-text="result ? $si(result.I, 'A', 3) : '—'"></text>

            {{-- VIR üçgeni --}}
            <g transform="translate(200 165)">
                <path d="M0 -42 L48 34 H-48 Z" fill="#0f172a" stroke="#334155" stroke-width="2" />
                <line x1="-28" y1="2" x2="28" y2="2" stroke="#334155" stroke-width="2" />
                <line x1="0" y1="2" x2="0" y2="34" stroke="#334155" stroke-width="2" />
                <text y="-8" text-anchor="middle" font-family="ui-sans-serif" font-size="16" font-weight="900" :fill="given.includes('v') ? '#f8fafc' : (result ? '#818cf8' : '#475569')">V</text>
                <text x="-20" y="26" text-anchor="middle" font-family="ui-sans-serif" font-size="16" font-weight="900" :fill="given.includes('i') ? '#f8fafc' : (result ? '#818cf8' : '#475569')">I</text>
                <text x="20" y="26" text-anchor="middle" font-family="ui-sans-serif" font-size="16" font-weight="900" :fill="given.includes('r') ? '#f8fafc' : (result ? '#818cf8' : '#475569')">R</text>
            </g>
        </svg>
        <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-400">
            <span>Isınma</span>
            <span class="relative h-1.5 flex-1 overflow-hidden rounded-full bg-[linear-gradient(90deg,#64748b,#f59e0b,#ef4444)]">
                <span class="absolute inset-y-0 right-0 bg-slate-800/90 transition-all duration-500" :style="{ width: (100 - heat * 100) + '%' }"></span>
            </span>
            <span class="font-mono" x-text="heat >= 1 ? '≥ ½ W: büyük direnç' : (heat > 0.5 ? '½ W direnç' : '¼ W yeterli')"></span>
        </div>
    </x-calc.stage>
</div>
