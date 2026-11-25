<div x-data="awgCalc" class="space-y-6">
    <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
        <button type="button" @click="mode = 'awg'" :class="mode === 'awg' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">AWG → mm²</button>
        <button type="button" @click="mode = 'mm2'" :class="mode === 'mm2' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">mm² → AWG</button>
    </div>
    <div class="max-w-xs">
        <template x-if="mode === 'awg'"><x-calc.input label="AWG" model="awg" :si="false" hint="Örn. 22, 30 ya da 2/0" /></template>
        <template x-if="mode === 'mm2'"><x-calc.input label="Kesit" model="mm2" unit="mm²" /></template>
    </div>
    <template x-if="result">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-calc.result label="AWG" value="mode === 'mm2' ? $num(result.awg, 3) + ' (≈ ' + result.nearest + ')' : String(awg)" />
            <x-calc.result label="Kesit" value="$num(result.area, 4) + ' mm²'" accent />
            <x-calc.result label="Çap" value="$num(result.d, 4) + ' mm'" />
            <x-calc.result label="Direnç (bakır)" value="$si(result.rPerM, 'Ω') + '/m'" />
        </div>
    </template>
    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">d = 0,127 mm × 92<sup>(36 − AWG) / 39</sup> &nbsp;·&nbsp; A = π d² / 4 &nbsp;·&nbsp; ρ<sub>Cu</sub> = 0,0172 Ω·mm²/m</p>
</div>
