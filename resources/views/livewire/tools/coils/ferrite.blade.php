<div x-data="ferriteToroidCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        @include('livewire.tools.coils.partials.mode')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Dış çap (DÇ)" model="od" unit="mm" />
            <x-calc.input label="İç çap (İÇ)" model="id" unit="mm" />
            <x-calc.input label="Yükseklik" model="h" unit="mm" />
            <x-calc.input label="Başlangıç geçirgenliği (µi)" model="mu" :si="false" step="10" min="1" max="100000" hint="Datasheet: ör. N87 ≈ 2200, 43 malzeme ≈ 800" />
            <x-calc.input label="Köşe yuvarlatma yarıçapı" model="round" unit="mm" hint="Pah / radyus; bilinmiyorsa 0" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="5000" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" /></template>
            <x-calc.input label="Tel çapı" model="wire" unit="mm" />
            <x-calc.input label="Doyma akı yoğunluğu (Bs)" model="bs" unit="T" hint="Ferrit tipik 0,3–0,45 T" />
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" class="sm:col-span-2" />
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı" value="Number.isFinite(N) ? $num(N, 4) + (mode === 'N' ? ' → ' + Math.ceil(N) : '') : '—'" />
            <x-calc.result label="AL değeri" value="base ? $num(base.AL, 4) + ' nH/N²' : '—'" />
            <x-calc.result label="le · Ae" value="base ? $num(base.le, 3) + ' mm · ' + $num(base.Ae, 3) + ' mm²' : '—'" />
            <x-calc.result label="Doyma akımı (yaklaşık)" value="$si(isat, 'A')" />
            <x-calc.result label="Tel uzunluğu / DC direnç" value="$si(wireLen, 'm') + ' · ' + $si(rdc, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>

        <x-calc.formula>L = µ0·µi·N²·h<sub>e</sub>·ln(DÇ/İÇ) / 2π &nbsp;·&nbsp; h<sub>e</sub> = h·(1 − (4−π)·r² / (h·(DÇ−İÇ)/2)) &nbsp;·&nbsp; I<sub>sat</sub> = Bs·le / (µ0·µi·N) (IEC 60205)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="alan çekirdeğin içinde dolaşır">
        @include('livewire.tools.coils.partials.toroid-scene', ['id' => 'fer', 'label' => "'µi ' + \$num(\$parse(mu), 4)"])
        @include('livewire.tools.coils.partials.stage-footer', ['note' => '<span x-text="N > geo.drawn ? \'Çizimde \' + geo.drawn + \' sarım gösteriliyor\' : \'\'"></span>'])
    </x-calc.stage>
</div>
