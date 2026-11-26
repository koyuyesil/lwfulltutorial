<div x-data="toroidCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        @include('livewire.tools.coils.partials.mode')

        <div>
            <p class="text-xs font-semibold text-slate-500">Hazır çekirdekler (AL)</p>
            <div class="mt-1.5 flex flex-wrap gap-1.5">
                <template x-for="p in presets" :key="p[0]">
                    <button type="button" @click="pick(p)" :class="al === p[1] ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300'"
                        class="rounded-full px-2.5 py-1 text-xs font-semibold transition"><span x-text="p[0]"></span> <span class="opacity-60" x-text="p[1] + 'H'"></span></button>
                </template>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="AL değeri" model="al" unit="H/N²" hint="Datasheet: nH/sarım² · örn. 100n" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="5000" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" hint="örn. 47µ" /></template>
            <x-calc.input label="Dış çap (DÇ)" model="od" unit="mm" />
            <x-calc.input label="İç çap (İÇ)" model="id" unit="mm" />
            <x-calc.input label="Yükseklik" model="h" unit="mm" />
            <x-calc.input label="Tel çapı" model="wire" unit="mm" />
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" class="sm:col-span-2" />
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı" value="Number.isFinite(N) ? $num(N, 4) + (mode === 'N' ? ' → ' + Math.ceil(N) : '') : '—'" />
            <x-calc.result label="Tel uzunluğu" value="$si(wireLen, 'm')" />
            <x-calc.result label="DC direnç" value="$si(rdc, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>
        <p x-show="Number.isFinite(fit) && N > fit" x-cloak class="rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30">
            ⚠ İç çevreye tek katta yaklaşık <span x-text="fit"></span> sarım sığar; <span x-text="$num(N, 3)"></span> sarım için ikinci kat ya da ince tel gerekir.
        </p>

        <x-calc.formula>L = AL · N² &nbsp;·&nbsp; N = √(L / AL) &nbsp;·&nbsp; sarım başına tel ≈ 2·(h + (DÇ − İÇ)/2)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="halka üzerinde sarımlar">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="tor-cu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".4" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
                <linearGradient id="tor-core" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#64748b" /><stop offset=".45" stop-color="#334155" /><stop offset="1" stop-color="#0f172a" /></linearGradient>
            </defs>
            <ellipse cx="200" cy="200" :rx="geo.R + geo.r" ry="12" fill="#000" opacity=".35" />
            <path :d="geo.back" stroke="#7c2d12" stroke-width="2.2" stroke-linecap="round" opacity=".7" />
            {{-- Çekirdek: yan duvar + üst yüzey --}}
            <ellipse cx="200" :cy="120 + geo.wall * 0.5" :rx="geo.R" :ry="geo.ry" stroke="#0f172a" :stroke-width="geo.r * 2" />
            <ellipse cx="200" cy="120" :rx="geo.R" :ry="geo.ry" stroke="url(#tor-core)" :stroke-width="geo.r * 2" />
            <ellipse cx="200" cy="120" :rx="geo.R + geo.r - 1" :ry="geo.ry + geo.r * 0.5" stroke="#94a3b8" stroke-opacity=".25" />
            <path :d="geo.front" stroke="url(#tor-cu)" stroke-width="2.6" stroke-linecap="round" />
            <path :d="geo.front" stroke="#fff7ed" stroke-width=".7" stroke-linecap="round" opacity=".5" />
            <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
                <text x="16" y="24" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 3) : 'N ?'"></text>
                <text x="384" y="24" text-anchor="end" fill="#a5b4fc" x-text="'AL ' + $si(AL * 1e-9, 'H')"></text>
                <text x="200" y="232" text-anchor="middle" fill="#cbd5e1" x-text="N > geo.drawn ? 'Çizimde ' + geo.drawn + ' sarım gösteriliyor' : ''"></text>
            </g>
        </svg>
        <div class="mt-1 flex justify-end">
            <button type="button" @click="spinning = ! spinning" class="rounded-md px-2 py-1 text-[11px] font-semibold text-slate-300 ring-1 ring-white/10 hover:bg-white/10" x-text="spinning ? '⏸ Durdur' : '▶ Döndür'"></button>
        </div>
    </x-calc.stage>
</div>
