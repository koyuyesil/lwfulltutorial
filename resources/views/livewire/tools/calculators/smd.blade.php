<div x-data="smdCalc" class="space-y-6">
    <div class="grid items-end gap-6 sm:grid-cols-[1fr_auto]">
        <x-calc.input label="SMD kodu" model="code" :si="false" placeholder="103, 4R7, 01C…" />
        <div class="flex h-14 w-28 items-center justify-center rounded-md bg-slate-900 font-mono text-xl font-bold tracking-widest text-white shadow-inner ring-4 ring-slate-300 dark:ring-slate-600" x-text="code.toUpperCase() || '—'"></div>
    </div>

    <div class="flex flex-wrap gap-1.5">
        <span class="text-xs font-semibold text-slate-500">Örnekler:</span>
        <template x-for="ex in examples" :key="ex">
            <button type="button" @click="code = ex" class="rounded-full bg-slate-100 px-2.5 py-1 font-mono text-xs font-semibold text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300" x-text="ex"></button>
        </template>
    </div>

    <template x-if="result && result.error"><p class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-300" x-text="result.error"></p></template>
    <template x-if="result && ! result.error">
        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Direnç" value="$si(result.value, 'Ω')" accent />
            <x-calc.result label="Kod türü" value="result.type" class="[&_p.font-mono]:font-sans [&_p.font-mono]:text-base" />
        </div>
    </template>

    <div class="rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">
        <p><strong>3 hane:</strong> ilk iki rakam × 10<sup>üçüncü</sup> (103 = 10 kΩ) · <strong>4 hane:</strong> ilk üç × 10<sup>dördüncü</sup> (1002 = 10 kΩ)</p>
        <p class="mt-1"><strong>R</strong> ondalık noktadır (4R7 = 4,7 Ω) · <strong>EIA-96:</strong> iki rakam (E96 değeri) + harf çarpan (01C = 10 kΩ)</p>
    </div>
</div>
