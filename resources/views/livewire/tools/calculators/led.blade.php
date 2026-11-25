@php $mono = 'font-family="ui-monospace,monospace"'; @endphp
<div x-data="ledCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Besleme (Vs)" model="vs" unit="V" />
            <x-calc.input label="LED gerilimi (Vf)" model="vf" unit="V" />
            <x-calc.input label="LED akımı (If)" model="iF" unit="A" hint="Standart LED: 20m" />
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Seri LED sayısı</span>
                <input type="number" min="1" max="50" x-model.number="count" class="mt-1.5 block w-full rounded-lg border-0 bg-white py-2.5 px-3 font-mono text-base shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">LED rengi:</span>
            <template x-for="p in presets" :key="p[0]">
                <button type="button" @click="pick(p)" :class="color === p[2] ? 'ring-2 ring-indigo-500 bg-white dark:bg-slate-700' : 'bg-slate-100 hover:bg-slate-200 dark:bg-white/10'"
                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                    <span class="h-2.5 w-2.5 rounded-full ring-1 ring-black/20" :style="{ background: p[2], boxShadow: `0 0 8px ${p[2]}` }"></span>
                    <span x-text="`${p[0]} ${String(p[1]).replace('.', ',')} V`"></span>
                </button>
            </template>
            <label class="ms-auto inline-flex items-center gap-2 text-xs text-slate-500">Seri
                <select x-model="series" class="rounded-lg border-0 bg-slate-100 py-1 pl-2 pr-7 text-xs font-semibold dark:bg-white/5"><option>E12</option><option>E24</option><option>E96</option></select>
            </label>
        </div>

        <template x-if="result && result.error"><p class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-300" x-text="result.error"></p></template>
        <template x-if="result && ! result.error">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Hesaplanan direnç" value="$si(result.R, 'Ω')" />
                <x-calc.result label="Kullanılacak direnç" value="$si(result.std, 'Ω')" accent hint="Bir üst standart değer" />
                <x-calc.result label="Gerçek akım" value="$si(result.Ireal, 'A')" />
                <x-calc.result label="Direnç gücü" value="$si(result.P, 'W')" hint="Önerilen: 2× güvenlik payıyla seçin" />
            </div>
        </template>

        <template x-if="result && ! result.error">
            <p class="text-sm text-slate-600 dark:text-slate-300">Önerilen direnç gücü: <strong x-text="Number.isFinite(result.rating) ? $num(result.rating) + ' W' : 'özel (yüksek güç)'"></strong></p>
        </template>

        <x-calc.formula>R = (Vs − n × Vf) / If &nbsp;·&nbsp; P = If² × R</x-calc.formula>
    </div>

    <x-calc.stage label="Canlı devre" badge="parlaklık ∝ akım">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <radialGradient id="led-glow"><stop offset="0" :stop-color="color" stop-opacity=".95" /><stop offset=".35" :stop-color="color" stop-opacity=".45" /><stop offset="1" :stop-color="color" stop-opacity="0" /></radialGradient>
                <radialGradient id="led-dome" cx=".35" cy=".3" r=".8"><stop offset="0" stop-color="#ffffff" stop-opacity=".95" /><stop offset=".25" :stop-color="color" stop-opacity=".95" /><stop offset="1" :stop-color="color" stop-opacity=".55" /></radialGradient>
                <linearGradient id="led-res" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fef3c7" /><stop offset=".5" stop-color="#d6b98c" /><stop offset="1" stop-color="#8b7355" /></linearGradient>
            </defs>
            <path d="M40 60 H360 V200 H40 Z" stroke="#475569" stroke-width="4" stroke-linejoin="round" />
            <path d="M40 60 H360 V200 H40 Z" stroke="#fbbf24" stroke-width="4" stroke-linejoin="round" class="calc-flow" x-show="flow > 0" :style="{ animationDuration: flow + 's' }" />
            {{-- Besleme --}}
            <rect x="22" y="100" width="36" height="60" rx="5" fill="#1e293b" stroke="#64748b" />
            <text x="40" y="126" text-anchor="middle" fill="#fde68a" {!! $mono !!} font-size="11" font-weight="800">Vs</text>
            <text x="40" y="142" text-anchor="middle" fill="#fde68a" {!! $mono !!} font-size="10" x-text="vs + ' V'"></text>
            {{-- Direnç (3D silindir) --}}
            <rect x="120" y="48" width="80" height="24" rx="10" fill="url(#led-res)" stroke="#78350f" stroke-opacity=".4" />
            @foreach ([138, 152, 166, 186] as $bx)<rect x="{{ $bx }}" y="48" width="6" height="24" fill="#7c2d12" opacity=".7" />@endforeach
            <text x="160" y="96" text-anchor="middle" fill="#e2e8f0" {!! $mono !!} font-size="12" font-weight="800" x-text="result && ! result.error ? $si(result.std, 'Ω', 3) : 'R ?'"></text>
            {{-- LED'ler (en fazla 4 çizilir) --}}
            @foreach (range(0, 3) as $i)
                @php $cx = 252 + $i * 30; @endphp
                <g x-show="n > {{ $i }}" transform="translate({{ $i === 0 ? 0 : 0 }} 0)">
                    <circle cx="{{ $cx }}" cy="130" r="34" fill="url(#led-glow)" :opacity="glow" />
                    <path d="M{{ $cx - 10 }} 150 V115 a10 10 0 0 1 20 0 V150 Z" fill="url(#led-dome)" stroke="#ffffff" stroke-opacity=".35" />
                    <rect x="{{ $cx - 12 }}" y="148" width="24" height="5" rx="1" :fill="color" opacity=".8" />
                    <line x1="{{ $cx - 5 }}" y1="153" x2="{{ $cx - 5 }}" y2="200" stroke="#cbd5e1" stroke-width="2" />
                    <line x1="{{ $cx + 5 }}" y1="153" x2="{{ $cx + 5 }}" y2="194" stroke="#cbd5e1" stroke-width="2" />
                </g>
            @endforeach
            <path d="M252 60 V105" stroke="#475569" stroke-width="3" />
            <text x="300" y="228" text-anchor="middle" fill="#94a3b8" {!! $mono !!} font-size="10" x-text="n > 4 ? n + ' LED seri (4 gösteriliyor)' : n + ' LED · Vf ' + vf + ' V'"></text>
            <text x="200" y="228" text-anchor="end" fill="#fbbf24" {!! $mono !!} font-size="10" x-show="result && ! result.error" x-text="result && ! result.error ? $si(result.Ireal, 'A', 3) : ''"></text>
        </svg>
        <div class="mt-1 flex items-center gap-2 text-[11px] text-slate-400">
            <span>Verim</span>
            <span class="relative h-1.5 flex-1 overflow-hidden rounded-full bg-slate-800">
                <span class="absolute inset-y-0 left-0 rounded-full bg-linear-to-r from-emerald-500 to-lime-300 transition-all duration-500" :style="{ width: (result && ! result.error ? result.efficiency : 0) + '%' }"></span>
            </span>
            <span class="font-mono" x-text="result && ! result.error ? $num(result.efficiency, 3) + ' %' : '—'"></span>
        </div>
    </x-calc.stage>
</div>
