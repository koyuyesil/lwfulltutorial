<div x-data="ledCalc" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-calc.input label="Besleme (Vs)" model="vs" unit="V" />
        <x-calc.input label="LED gerilimi (Vf)" model="vf" unit="V" />
        <x-calc.input label="LED akımı (If)" model="iF" unit="A" hint="Standart LED: 20m" />
        <label class="block">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Seri LED sayısı</span>
            <input type="number" min="1" max="50" x-model.number="count" class="mt-1.5 block w-full rounded-lg border-0 bg-white py-2.5 px-3 font-mono text-base shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
        </label>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs font-semibold text-slate-500">Tipik Vf:</span>
        <template x-for="[name, v] in presets" :key="name">
            <button type="button" @click="vf = String(v)" :class="vf == v ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300'"
                class="rounded-full px-2.5 py-1 text-xs font-semibold" x-text="`${name} ${String(v).replace('.', ',')} V`"></button>
        </template>
        <label class="ms-auto inline-flex items-center gap-2 text-xs text-slate-500">Seri
            <select x-model="series" class="rounded-lg border-0 bg-slate-100 py-1 pl-2 pr-7 text-xs font-semibold dark:bg-white/5"><option>E12</option><option>E24</option><option>E96</option></select>
        </label>
    </div>

    <template x-if="result && result.error"><p class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-300" x-text="result.error"></p></template>
    <template x-if="result && ! result.error">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-calc.result label="Hesaplanan direnç" value="$si(result.R, 'Ω')" />
            <x-calc.result label="Kullanılacak direnç" value="$si(result.std, 'Ω')" accent hint="Bir üst standart değer" />
            <x-calc.result label="Gerçek akım" value="$si(result.Ireal, 'A')" />
            <x-calc.result label="Direnç gücü" value="$si(result.P, 'W')" ::class="''" />
        </div>
    </template>
    <template x-if="result && ! result.error">
        <p class="text-sm text-slate-600 dark:text-slate-300">
            Önerilen direnç gücü: <strong x-text="Number.isFinite(result.rating) ? $num(result.rating) + ' W' : 'özel'"></strong> (2× güvenlik payı) ·
            Verim: <strong x-text="$num(result.efficiency, 3) + ' %'"></strong>
        </p>
    </template>

    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">R = (Vs − n × Vf) / If &nbsp;·&nbsp; P = If² × R</p>
</div>
