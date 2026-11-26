<div x-data="spiralCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        @include('livewire.tools.coils.partials.mode')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="İç çap" model="di" unit="mm" hint="Qi alıcı: ~20 mm" />
            <x-calc.input label="Dış çap" model="dout" unit="mm" hint="Qi alıcı: ~40–50 mm" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="200" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" hint="Qi alıcı tipik: 6,3µ – 12µ" /></template>
            <x-calc.input label="Ferrit katsayısı" model="ferrite" :si="false" step="0.1" min="1" max="5" hint="Ferrit plaka yok: 1 · Qi alıcıları: ~1,5–2" />
            <x-calc.input label="Tel / iz genişliği" model="wire" unit="mm" hint="Litz tel ya da PCB iz genişliği" />
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" hint="Qi: ~100n (100 kHz civarı rezonans)" />
        </div>

        <p x-show="invalid" x-cloak class="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 ring-1 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/30">Dış çap iç çaptan büyük olmalı.</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı" value="Number.isFinite(N) ? $num(N, 4) : '—'" />
            <x-calc.result label="Sarım aralığı (adım)" value="Number.isFinite(pitch) ? $num(pitch, 3) + ' mm' : '—'" />
            <x-calc.result label="Tel uzunluğu / DC direnç" value="$si(wireLen, 'm') + ' · ' + $si(rdc, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>
        <p x-show="gap < 0" x-cloak class="rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30">
            ⚠ Tel genişliği sarım adımından büyük; bu çaplar arasına <span x-text="$num(N, 3)"></span> sarım sığmaz.
        </p>

        <x-calc.formula>L[µH] = r²·N² / (8r + 11w) × ferrit &nbsp;·&nbsp; r: ortalama yarıçap, w: sarım genişliği (inç, Wheeler)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="ferrit plaka üzerinde düz bobin">
        <div class="grid place-items-center py-2">
            <div class="relative h-60 w-full max-w-sm [perspective:900px]">
                <div class="absolute inset-0 grid place-items-center [transform-style:preserve-3d]" :style="tiltStyle">
                    <svg viewBox="-110 -110 220 220" class="h-full w-full overflow-visible" fill="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="spi-cu" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".45" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
                            <radialGradient id="spi-ferrite"><stop offset="0" stop-color="#334155" /><stop offset="1" stop-color="#0f172a" /></radialGradient>
                        </defs>
                        {{-- Ferrit plaka --}}
                        <circle r="104" fill="url(#spi-ferrite)" stroke="#475569" stroke-width="1.5" :opacity="k > 1 ? 1 : 0.35" />
                        <path :d="geo.d" stroke="#000" stroke-opacity=".5" stroke-width="5.5" stroke-linecap="round" transform="translate(1.5 2)" />
                        <path :d="geo.d" stroke="url(#spi-cu)" stroke-width="4" stroke-linecap="round" />
                        <path :d="geo.d" stroke="#fff7ed" stroke-width=".8" stroke-linecap="round" opacity=".55" />
                        {{-- Uçlar --}}
                        <path :d="`M${geo.end.x} ${geo.end.y} L${geo.end.x * 1.15} ${geo.end.y * 1.15}`" stroke="url(#spi-cu)" stroke-width="4" stroke-linecap="round" />
                        <path :d="`M${geo.start.x} ${geo.start.y} L104 ${geo.start.y}`" stroke="#b45309" stroke-width="3" stroke-linecap="round" stroke-dasharray="2 3" />
                    </svg>
                </div>
            </div>
            <p class="font-mono text-2xl font-black text-white" x-text="$si(L, 'H')"></p>
            <p class="font-mono text-xs text-slate-400" x-text="(Number.isFinite(N) ? $num(N, 3) + ' sarım · ' : '') + 'Ø ' + $num(Di, 3) + '–' + $num(Do, 3) + ' mm'"></p>
        </div>
        <div class="mt-1 flex justify-end">
            <button type="button" @click="spinning = ! spinning" class="rounded-md px-2 py-1 text-[11px] font-semibold text-slate-300 ring-1 ring-white/10 hover:bg-white/10" x-text="spinning ? '⏸ Durdur' : '▶ Döndür'"></button>
        </div>
    </x-calc.stage>
</div>
