<div x-data="ohmCalc" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-calc.input label="Gerilim (V)" model="v" unit="V" placeholder="örn. 5" />
        <x-calc.input label="Akım (I)" model="i" unit="A" placeholder="örn. 20m" />
        <x-calc.input label="Direnç (R)" model="r" unit="Ω" placeholder="örn. 4k7" />
        <x-calc.input label="Güç (P)" model="p" unit="W" placeholder="örn. 0.25" />
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
        <p :class="given.length > 2 ? 'text-rose-600' : 'text-slate-500'"
            x-text="given.length < 2 ? `Herhangi iki değeri girin (${given.length}/2)` : (given.length > 2 ? 'Yalnızca iki değer girin; fazlasını temizleyin.' : 'Hesaplandı ✓')"></p>
        <button type="button" @click="clear()" class="font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Temizle</button>
    </div>

    <template x-if="result">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-calc.result label="Gerilim" value="$si(result.V, 'V')" ::class="given.includes('v') ? '' : 'ring-2 ring-indigo-500'" />
            <x-calc.result label="Akım" value="$si(result.I, 'A')" ::class="given.includes('i') ? '' : 'ring-2 ring-indigo-500'" />
            <x-calc.result label="Direnç" value="$si(result.R, 'Ω')" ::class="given.includes('r') ? '' : 'ring-2 ring-indigo-500'" />
            <x-calc.result label="Güç" value="$si(result.P, 'W')" ::class="given.includes('p') ? '' : 'ring-2 ring-indigo-500'" />
        </div>
    </template>

    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">V = I × R &nbsp;·&nbsp; P = V × I = I² × R = V² / R</p>
</div>
