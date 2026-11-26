<div x-data="multilayerCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        @include('livewire.tools.coils.partials.mode')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="İç çap (makara)" model="di" unit="mm" />
            <x-calc.input label="Dış çap (sarım dahil)" model="dout" unit="mm" />
            <x-calc.input label="Sarım uzunluğu" model="len" unit="mm" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="100000" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" hint="örn. 100µ, 2m" /></template>
            <x-calc.input label="Tel çapı" model="wire" unit="mm" hint="Katman sayısı ve DC direnç için" />
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" />
        </div>

        <p x-show="invalid" x-cloak class="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 ring-1 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/30">Dış çap iç çaptan büyük olmalı.</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı" value="Number.isFinite(N) ? $num(N, 4) : '—'" />
            <x-calc.result label="Katman (tahmini)" value="Number.isFinite(layers) ? layers + ' kat · ' + $num(perLayer, 3) + ' sarım/kat' : '—'" />
            <x-calc.result label="Tel uzunluğu / DC direnç" value="$si(wireLen, 'm') + ' · ' + $si(rdc, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>

        <x-calc.formula>L[µH] = 0,8·a²·N² / (6a + 9b + 10c) &nbsp;·&nbsp; a: ortalama yarıçap, b: uzunluk, c: sarım derinliği (inç, Wheeler)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="katmanlar içten dışa">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="mul-cu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".4" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
                <linearGradient id="mul-bobbin" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#475569" /><stop offset=".5" stop-color="#1e293b" /><stop offset="1" stop-color="#020617" /></linearGradient>
            </defs>
            {{-- Makara flanşları (arka) --}}
            <ellipse :cx="200 - geo.len / 2 * Math.cos(0.45) - 6" cy="120" :rx="geo.ex + 4" :ry="geo.ro + 14" fill="url(#mul-bobbin)" stroke="#64748b" stroke-opacity=".5" />
            @foreach (range(0, 3) as $i)<path x-show="geo.layers[{{ $i }}]" :d="geo.layers[{{ $i }}]?.back" stroke="#7c2d12" stroke-width="2.4" stroke-linecap="round" opacity="{{ 0.45 + $i * 0.12 }}" />@endforeach
            <rect :x="200 - geo.len / 2 * Math.cos(0.45)" :y="120 - geo.ri * 0.9" :width="geo.len * Math.cos(0.45)" :height="geo.ri * 1.8" fill="#334155" opacity=".85" />
            @foreach (range(0, 3) as $i)<path x-show="geo.layers[{{ $i }}]" :d="geo.layers[{{ $i }}]?.front" stroke="url(#mul-cu)" stroke-width="2.8" stroke-linecap="round" opacity="{{ 0.55 + $i * 0.15 }}" />@endforeach
            {{-- Makara flanşı (ön) --}}
            <ellipse :cx="200 + geo.len / 2 * Math.cos(0.45) + 6" cy="120" :rx="geo.ex + 4" :ry="geo.ro + 14" fill="url(#mul-bobbin)" stroke="#94a3b8" stroke-opacity=".5" />
            <ellipse :cx="200 + geo.len / 2 * Math.cos(0.45) + 6" cy="120" :rx="geo.ex * (geo.ri / geo.ro) * 0.8" :ry="geo.ri * 0.8" fill="#020617" />
            <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
                <text x="16" y="24" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 4) : 'N ?'"></text>
                <text x="384" y="24" text-anchor="end" fill="#a5b4fc" x-text="Number.isFinite(layers) ? layers + ' katman' : ''"></text>
                <text x="200" y="228" text-anchor="middle" fill="#cbd5e1" x-text="'Ø ' + $num(Di, 3) + ' → ' + $num(Do, 3) + ' mm · l ' + $num(Len, 3) + ' mm'"></text>
            </g>
        </svg>
        <div class="mt-1 flex items-center justify-between gap-2 text-[11px] text-slate-400">
            <span>Çizimde en fazla 4 katman ve kat başına 40 sarım</span>
            <button type="button" @click="spinning = ! spinning" class="rounded-md px-2 py-1 font-semibold text-slate-300 ring-1 ring-white/10 hover:bg-white/10" x-text="spinning ? '⏸ Durdur' : '▶ Döndür'"></button>
        </div>
    </x-calc.stage>
</div>
