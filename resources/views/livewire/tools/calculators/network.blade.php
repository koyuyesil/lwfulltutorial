<div x-data="networkCalc" class="space-y-6">
    <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
        @foreach (['R' => 'Direnç', 'C' => 'Kondansatör', 'L' => 'Bobin'] as $k => $label)
            <button type="button" @click="kind = '{{ $k }}'" :class="kind === '{{ $k }}' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">{{ $label }}</button>
        @endforeach
    </div>

    <label class="block">
        <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Değerler</span>
        <textarea x-model="values" rows="3" spellcheck="false"
            class="mt-1.5 block w-full rounded-lg border-0 bg-white px-3 py-2.5 font-mono text-base text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10"></textarea>
        <span class="mt-1 block text-xs" :class="invalid ? 'text-rose-600' : 'text-slate-500'"
            x-text="invalid ? 'Bazı değerler okunamadı; boşluk ya da noktalı virgülle ayırın.' : 'Boşluk, “; ” ya da “, ” ile ayırın. Örn: 1k 2k2 4k7'"></span>
    </label>

    <template x-if="result">
        <div class="grid gap-3 sm:grid-cols-3">
            <x-calc.result label="Seri bağlı" value="$si(result.series, unit)" accent />
            <x-calc.result label="Paralel bağlı" value="$si(result.parallel, unit)" accent />
            <x-calc.result label="Eleman sayısı" value="String(result.count)" />
        </div>
    </template>

    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300"
        x-text="kind === 'C' ? 'Paralel: C = C1 + C2 + … · Seri: 1/C = 1/C1 + 1/C2 + …' : 'Seri: X = X1 + X2 + … · Paralel: 1/X = 1/X1 + 1/X2 + …'"></p>
</div>
