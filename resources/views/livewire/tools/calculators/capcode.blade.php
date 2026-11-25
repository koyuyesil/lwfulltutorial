<div x-data="capCodeCalc" class="space-y-6">
    <x-calc.input label="Kondansatör kodu" model="code" :si="false" placeholder="104, 473K, 4n7…" />

    <div class="flex flex-wrap gap-1.5">
        <span class="text-xs font-semibold text-slate-500">Örnekler:</span>
        <template x-for="ex in examples" :key="ex">
            <button type="button" @click="code = ex" class="rounded-full bg-slate-100 px-2.5 py-1 font-mono text-xs font-semibold text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300" x-text="ex"></button>
        </template>
    </div>

    <template x-if="result && result.error"><p class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-300" x-text="result.error"></p></template>
    <template x-if="result && ! result.error">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-calc.result label="pF" value="$num(result.farad * 1e12) + ' pF'" />
            <x-calc.result label="nF" value="$num(result.farad * 1e9) + ' nF'" accent />
            <x-calc.result label="µF" value="$num(result.farad * 1e6) + ' µF'" />
            <x-calc.result label="Tolerans" value="result.tol ?? '—'" :hint="null" />
        </div>
    </template>
    <template x-if="result && ! result.error"><p class="text-sm text-slate-500" x-text="'Kod türü: ' + result.type"></p></template>

    <p class="rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">
        3 haneli kod pikofarad cinsindendir: ilk iki rakam × 10<sup>üçüncü</sup>. 104 = 10 × 10⁴ pF = 100 nF. Sondaki harf toleranstır (J = %5, K = %10, M = %20).
    </p>
</div>
