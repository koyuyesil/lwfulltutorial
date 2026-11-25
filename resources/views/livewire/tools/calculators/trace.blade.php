<div x-data="traceCalc" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-calc.input label="Akım" model="current" unit="A" />
        <x-calc.input label="İzin verilen ısınma" model="rise" unit="°C" />
        <x-calc.input label="Bakır kalınlığı" model="copper" unit="oz" hint="1 oz ≈ 35 µm" />
        <x-calc.input label="Yol uzunluğu" model="length" unit="mm" />
    </div>
    <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
        <button type="button" @click="layer = 'external'" :class="layer === 'external' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">Dış katman</button>
        <button type="button" @click="layer = 'internal'" :class="layer === 'internal' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">İç katman</button>
    </div>
    <template x-if="result">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-calc.result label="Minimum genişlik" value="$num(result.widthMm, 3) + ' mm'" accent />
            <x-calc.result label="Genişlik (mil)" value="$num(result.widthMils, 4) + ' mil'" />
            <x-calc.result label="Yol direnci" value="$si(result.R, 'Ω')" />
            <x-calc.result label="Gerilim düşümü / kayıp" value="$si(result.drop, 'V') + ' · ' + $si(result.loss, 'W')" />
        </div>
    </template>
    <p class="rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">
        IPC-2221: I = k × ΔT<sup>0,44</sup> × A<sup>0,725</sup> (k: dış 0,048 · iç 0,024; A: mil²). Jumper teli ya da onarım yolu çekerken bu genişliğin altına inmeyin.
    </p>
</div>
