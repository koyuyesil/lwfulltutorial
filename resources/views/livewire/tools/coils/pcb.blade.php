<div x-data="pcbCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5" role="radiogroup" aria-label="Bobin biçimi">
                <template x-for="(sh, key) in shapes" :key="key">
                    <button type="button" role="radio" @click="shape = key" :aria-checked="shape === key ? 'true' : 'false'"
                        :class="shape === key ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold" x-text="sh.label"></button>
                </template>
            </div>
            @include('livewire.tools.coils.partials.mode')
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="İç çap (boşluk)" model="din" unit="mm" />
            <x-calc.input label="İz genişliği (w)" model="w" unit="mm" />
            <x-calc.input label="İz aralığı (s)" model="s" unit="mm" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="200" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" /></template>
            <x-calc.input label="Bakır kalınlığı" model="oz" unit="oz" :si="false" step="0.5" min="0.5" max="6" hint="1 oz ≈ 35 µm" />
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" />
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı" value="Number.isFinite(N) ? N : '—'" />
            <x-calc.result label="Dış çap · doluluk (ρ)" value="r ? $num(r.dout, 4) + ' mm · ' + $num(r.rho, 3) : '—'" />
            <x-calc.result label="İz uzunluğu / DC direnç" value="r ? $num(r.lengthMm, 4) + ' mm · ' + $si(rdc, 'Ω') : '—'" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>

        <x-calc.formula>L = µ0·N²·d<sub>ort</sub>·c1/2 · (ln(c2/ρ) + c3·ρ + c4·ρ²) &nbsp;·&nbsp; ρ = (D<sub>dış</sub> − D<sub>iç</sub>) / (D<sub>dış</sub> + D<sub>iç</sub>) (Mohan, 1999; tek katman, ~%2–3)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="FR-4 kart üzerinde bakır iz">
        <div class="grid place-items-center py-2">
            <div class="relative h-60 w-full max-w-sm [perspective:900px]">
                <div class="absolute inset-0 grid place-items-center [transform-style:preserve-3d]" :style="tiltStyle">
                    <svg viewBox="-110 -110 220 220" class="h-full w-full overflow-visible" fill="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="pcb-cu" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".5" stop-color="#f59e0b" /><stop offset="1" stop-color="#b45309" /></linearGradient>
                        </defs>
                        <rect x="-70" y="-70" width="140" height="140" rx="7" fill="#14532d" stroke="#22c55e" stroke-opacity=".35" />
                        <path d="M-70 -55 H70 M-70 55 H70" stroke="#166534" stroke-width=".6" />
                        <path :d="geo.d" stroke="#052e16" :stroke-width="geo.w + 1.5" stroke-linejoin="round" stroke-linecap="round" transform="translate(1 1.5)" />
                        <path :d="geo.d" stroke="url(#pcb-cu)" :stroke-width="geo.w" stroke-linejoin="round" stroke-linecap="round" />
                        <circle :cx="geo.start?.x" :cy="geo.start?.y" :r="geo.w * 0.9 + 2" fill="#fbbf24" stroke="#78350f" /><circle :cx="geo.start?.x" :cy="geo.start?.y" r="1.6" fill="#0f172a" />
                        <circle :cx="geo.end?.x" :cy="geo.end?.y" :r="geo.w * 0.9 + 2" fill="#fbbf24" stroke="#78350f" /><circle :cx="geo.end?.x" :cy="geo.end?.y" r="1.6" fill="#0f172a" />
                    </svg>
                </div>
                @include('livewire.tools.coils.partials.flat-field')
            </div>
            <p class="font-mono text-2xl font-black text-white" x-text="$si(L, 'H')"></p>
            <p class="font-mono text-xs text-slate-400" x-text="(Number.isFinite(N) ? N + ' sarım · ' : '') + shapes[shape].label + ' · ' + (r ? $num(r.dout, 3) + ' mm' : '')"></p>
        </div>
        @include('livewire.tools.coils.partials.stage-footer', ['note' => 'Orta delik ucu alt katmandan (via) çıkar'])
    </x-calc.stage>
</div>
