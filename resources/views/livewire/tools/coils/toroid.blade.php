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

    <x-calc.stage label="3D model" badge="alan çekirdeğin içinde dolaşır">
        @include('livewire.tools.coils.partials.toroid-scene', ['id' => 'tor', 'label' => "'AL ' + \$si(AL * 1e-9, 'H')"])
        @include('livewire.tools.coils.partials.stage-footer', ['note' => '<span x-text="N > geo.drawn ? \'Çizimde \' + geo.drawn + \' sarım gösteriliyor\' : \'\'"></span>'])
    </x-calc.stage>
</div>