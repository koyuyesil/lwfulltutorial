<div x-data="rcCalc" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <x-calc.input label="Direnç (R)" model="r" unit="Ω" />
        <x-calc.input label="Kapasite (C)" model="c" unit="F" />
    </div>
    <template x-if="result">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <x-calc.result label="Zaman sabiti τ" value="$si(result.tau, 's')" accent />
            <x-calc.result label="Kesim frekansı fc" value="$si(result.fc, 'Hz')" accent hint="−3 dB, birinci derece RC filtre" />
            <x-calc.result label="%63 şarj (1τ)" value="$si(result.t63, 's')" />
            <x-calc.result label="%95 şarj (3τ)" value="$si(result.t95, 's')" />
            <x-calc.result label="%99 şarj (5τ)" value="$si(result.t99, 's')" />
        </div>
    </template>
    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">τ = R × C &nbsp;·&nbsp; fc = 1 / (2π R C) &nbsp;·&nbsp; Vc(t) = V (1 − e<sup>−t/τ</sup>)</p>
</div>
