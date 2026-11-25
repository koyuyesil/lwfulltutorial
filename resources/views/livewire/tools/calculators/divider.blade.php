@php $mono = 'font-family="ui-monospace,monospace"'; @endphp
<div x-data="dividerCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
            <button type="button" @click="mode = 'vout'" :class="mode === 'vout' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">Çıkış gerilimi</button>
            <button type="button" @click="mode = 'r2'" :class="mode === 'r2' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">Hedef gerilim için R2</button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-calc.input label="Giriş (Vin)" model="vin" unit="V" />
            <x-calc.input label="R1 (üst)" model="r1" unit="Ω" />
            <template x-if="mode === 'vout'"><x-calc.input label="R2 (alt)" model="r2" unit="Ω" /></template>
            <template x-if="mode === 'r2'"><x-calc.input label="Hedef Vout" model="vout" unit="V" /></template>
        </div>

        <template x-if="mode === 'vout' && out">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Vout" value="$si(out.Vout, 'V')" accent />
                <x-calc.result label="Akım" value="$si(out.I, 'A')" />
                <x-calc.result label="R1 gücü" value="$si(out.P1, 'W')" />
                <x-calc.result label="R2 gücü" value="$si(out.P2, 'W')" />
            </div>
        </template>

        <template x-if="mode === 'r2'">
            <div class="space-y-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">Standart seri
                    <select x-model="series" class="rounded-lg border-0 bg-slate-100 py-1 pl-2 pr-8 text-sm font-semibold dark:bg-white/5">
                        <option>E12</option><option>E24</option><option>E96</option>
                    </select>
                </label>
                <template x-if="solve">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <x-calc.result label="İdeal R2" value="$si(solve.R2, 'Ω')" />
                        <x-calc.result label="Standart R2" value="$si(solve.std, 'Ω')" accent />
                        <x-calc.result label="Gerçek Vout" value="$si(solve.actual, 'V')" />
                    </div>
                </template>
                <template x-if="solve">
                    <p class="text-sm text-slate-500">Standart değerle hata: <strong :class="Math.abs(solve.error) > 2 ? 'text-amber-600' : 'text-emerald-600'" x-text="$num(solve.error, 3) + ' %'"></strong></p>
                </template>
                <p x-show="! solve" class="text-sm text-amber-600">Hedef gerilim 0 ile Vin arasında olmalı.</p>
            </div>
        </template>

        <x-calc.formula>Vout = Vin × R2 / (R1 + R2) &nbsp;·&nbsp; R2 = R1 × Vout / (Vin − Vout) &nbsp;·&nbsp; R1 = R2 × (Vin − Vout) / Vout</x-calc.formula>
    </div>

    <x-calc.stage label="Canlı şema" badge="Board ID devresi de böyledir">
        <div class="grid grid-cols-[1fr_auto] items-stretch gap-4">
            <svg viewBox="0 0 260 280" class="w-full" fill="none" aria-hidden="true">
                <defs>
                    <linearGradient id="div-res" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#1e293b" /><stop offset=".5" stop-color="#475569" /><stop offset="1" stop-color="#0f172a" /></linearGradient>
                </defs>
                {{-- Vin --}}
                <circle cx="90" cy="24" r="10" fill="#fde68a" /><circle cx="90" cy="24" r="4" fill="#0b1220" />
                <text x="110" y="28" fill="#fde68a" {!! $mono !!} font-size="13" font-weight="800" x-text="'Vin ' + (view ? $si(view.Vin, 'V', 3) : vin + ' V')"></text>
                {{-- tel --}}
                <path d="M90 34 V250" stroke="#475569" stroke-width="5" />
                <path d="M90 34 V250" stroke="#fbbf24" stroke-width="5" stroke-linecap="round" class="calc-flow" x-show="flow > 0" :style="{ animationDuration: flow + 's' }" />
                {{-- R1 --}}
                <rect x="72" y="56" width="36" height="70" rx="6" fill="url(#div-res)" stroke="#38bdf8" stroke-width="2" />
                @foreach ([70, 84, 98] as $by)<rect x="72" y="{{ $by }}" width="36" height="5" fill="#38bdf8" opacity=".35" />@endforeach
                <text x="118" y="88" fill="#f8fafc" {!! $mono !!} font-size="13" font-weight="800">R1</text>
                <text x="118" y="104" fill="#7dd3fc" {!! $mono !!} font-size="11" x-text="view ? $si(view.R1, 'Ω', 3) : r1"></text>
                {{-- Orta nokta --}}
                <circle cx="90" cy="145" r="7" fill="#f59e0b" stroke="#fff7ed" stroke-width="2" />
                <path d="M97 145 H150" stroke="#f59e0b" stroke-width="2.5" stroke-dasharray="5 4" />
                <rect x="150" y="131" width="100" height="28" rx="8" fill="#0b1220" stroke="#f59e0b" />
                <text x="200" y="150" text-anchor="middle" fill="#fde68a" {!! $mono !!} font-size="13" font-weight="900" x-text="view ? $si(view.Vout, 'V', 3) : 'Vout ?'"></text>
                {{-- R2 --}}
                <rect x="72" y="164" width="36" height="70" rx="6" fill="url(#div-res)" :stroke="mode === 'r2' ? '#34d399' : '#38bdf8'" stroke-width="2" />
                @foreach ([178, 192, 206] as $by)<rect x="72" y="{{ $by }}" width="36" height="5" :fill="mode === 'r2' ? '#34d399' : '#38bdf8'" opacity=".35" />@endforeach
                <text x="118" y="196" fill="#f8fafc" {!! $mono !!} font-size="13" font-weight="800">R2</text>
                <text x="118" y="212" :fill="mode === 'r2' ? '#6ee7b7' : '#7dd3fc'" {!! $mono !!} font-size="11" x-text="view ? $si(view.R2, 'Ω', 3) + (mode === 'r2' ? ' (std)' : '') : '?'"></text>
                {{-- GND --}}
                <path d="M68 250 H112 M76 258 H104 M84 266 H96" stroke="#94a3b8" stroke-width="3" stroke-linecap="round" />
                <text x="118" y="262" fill="#94a3b8" {!! $mono !!} font-size="11">GND</text>
                <text x="18" y="148" fill="#94a3b8" {!! $mono !!} font-size="10" x-show="view" x-text="view ? $si(view.I, 'A', 3) : ''"></text>
                <text x="18" y="160" fill="#64748b" {!! $mono !!} font-size="9" x-show="view">akım</text>
            </svg>

            {{-- Gerilim seviyesi göstergesi --}}
            <div class="flex w-14 flex-col items-center gap-2">
                <span class="font-mono text-[10px] text-slate-400">Vin</span>
                <div class="relative w-6 flex-1 overflow-hidden rounded-full bg-slate-800 ring-1 ring-white/10">
                    <div class="absolute inset-x-0 bottom-0 rounded-full bg-linear-to-t from-amber-500 via-amber-300 to-yellow-100 shadow-[0_0_20px_rgba(245,158,11,.6)] transition-all duration-500"
                        :style="{ height: (view ? Math.max(2, view.ratio * 100) : 0) + '%' }"></div>
                    @foreach ([25, 50, 75] as $tick)<span class="absolute inset-x-0 h-px bg-white/20" style="bottom: {{ $tick }}%"></span>@endforeach
                </div>
                <span class="font-mono text-xs font-black text-amber-300" x-text="view ? $num(view.ratio * 100, 3) + '%' : '—'"></span>
                <span class="font-mono text-[10px] text-slate-400">GND</span>
            </div>
        </div>
    </x-calc.stage>
</div>
