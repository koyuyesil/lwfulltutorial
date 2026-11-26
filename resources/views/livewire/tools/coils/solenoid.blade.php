<div x-data="solenoidCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        @include('livewire.tools.coils.partials.mode')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Bobin çapı (D)" model="d" unit="mm" hint="Tel merkezinden ölçülen çap" />
            <x-calc.input label="Sarım uzunluğu (l)" model="len" unit="mm" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="2000" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" hint="örn. 1µ, 470n" /></template>
            <x-calc.input label="Tel çapı" model="wire" unit="mm" hint="Emaye tel; DC direnç ve sığma kontrolü için" />
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" hint="Rezonans frekansı için, örn. 100p" />
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı" value="Number.isFinite(N) ? $num(N, 4) : '—'" />
            <x-calc.result label="Tel uzunluğu" value="$si(wireLen, 'm')" />
            <x-calc.result label="DC direnç" value="$si(rdc, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>

        <div class="space-y-2 text-sm">
            <p x-show="tooShort" x-cloak class="rounded-lg bg-amber-50 px-3 py-2 font-medium text-amber-800 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30">
                ⚠ <span x-text="$num(N, 3)"></span> sarım <span x-text="$num(minLen, 3)"></span> mm uzunluk ister; tek katmana sığmaz.
            </p>
            <p x-show="ratio < 0.4" x-cloak class="rounded-lg bg-slate-50 px-3 py-2 text-slate-600 ring-1 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10">
                Uzunluk / çap oranı 0,4'ün altında; Wheeler formülü kısa bobinlerde %5'ten fazla sapabilir.
            </p>
        </div>

        <x-calc.formula>L[µH] = D²·N² / (18·D + 40·l) &nbsp;·&nbsp; D, l inç (Wheeler, l &gt; 0,4·D için ~%1)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="canlı dönüş · alan çizgileri">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="sol-cu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".35" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
                <linearGradient id="sol-core" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#94a3b8" stop-opacity=".35" /><stop offset=".5" stop-color="#e2e8f0" stop-opacity=".12" /><stop offset="1" stop-color="#0f172a" stop-opacity=".5" /></linearGradient>
            </defs>
            {{-- Manyetik alan çizgileri --}}
            @foreach ([1, 2, 3] as $i)
                <ellipse cx="200" cy="120" :rx="geo.len / 2 + 22 + {{ $i }} * 26" :ry="geo.r + 12 + {{ $i }} * 22" stroke="#38bdf8" stroke-opacity="{{ 0.45 - $i * 0.1 }}" stroke-width="1.5" class="calc-flow" style="animation-duration: {{ 1.2 + $i * 0.4 }}s" />
            @endforeach
            {{-- Arka sarımlar, çekirdek, ön sarımlar --}}
            <path :d="geo.back" stroke="#7c2d12" stroke-width="3.2" stroke-linecap="round" opacity=".8" />
            <rect :x="200 - geo.len / 2 * Math.cos(0.45) - 4" :y="120 - geo.r * 0.78" :width="geo.len * Math.cos(0.45) + 8" :height="geo.r * 1.56" rx="3" fill="url(#sol-core)" stroke="#94a3b8" stroke-opacity=".25" />
            <path :d="geo.front" stroke="url(#sol-cu)" stroke-width="3.6" stroke-linecap="round" />
            <path :d="geo.front" stroke="#fff7ed" stroke-width=".9" stroke-linecap="round" opacity=".55" />
            {{-- Bağlantı uçları --}}
            <path :d="`M${geo.start.x} ${geo.start.y} L${geo.start.x - 34} ${geo.start.y}`" stroke="url(#sol-cu)" stroke-width="3.2" stroke-linecap="round" />
            <path :d="`M${geo.end.x} ${geo.end.y} L${geo.end.x + 34} ${geo.end.y}`" stroke="url(#sol-cu)" stroke-width="3.2" stroke-linecap="round" />
            {{-- Ölçüler --}}
            <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
                <path :d="`M${200 - geo.len / 2 * Math.cos(0.45)} 214 H${200 + geo.len / 2 * Math.cos(0.45)}`" stroke="#64748b" stroke-width="1" />
                <text x="200" y="230" text-anchor="middle" fill="#cbd5e1" x-text="'l = ' + $num(Len, 3) + ' mm'"></text>
                <text x="16" y="24" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 3) : 'N ?'"></text>
                <text x="384" y="24" text-anchor="end" fill="#a5b4fc" x-text="'Ø ' + $num(D, 3) + ' mm'"></text>
            </g>
        </svg>
        <div class="mt-1 flex items-center justify-between gap-2 text-[11px] text-slate-400">
            <span x-text="N > 80 ? 'Çizimde 80 sarım gösteriliyor' : ''"></span>
            <button type="button" @click="spinning = ! spinning" class="rounded-md px-2 py-1 font-semibold text-slate-300 ring-1 ring-white/10 hover:bg-white/10" x-text="spinning ? '⏸ Durdur' : '▶ Döndür'"></button>
        </div>
    </x-calc.stage>
</div>
