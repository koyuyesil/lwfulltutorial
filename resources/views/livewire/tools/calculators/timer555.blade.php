<div x-data="timer555Calc" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-3">
        <x-calc.input label="R1" model="r1" unit="Ω" />
        <x-calc.input label="R2" model="r2" unit="Ω" />
        <x-calc.input label="C" model="c" unit="F" />
    </div>
    <template x-if="result">
        <div class="space-y-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <x-calc.result label="Frekans" value="$si(result.f, 'Hz')" accent />
                <x-calc.result label="Görev oranı" value="$num(result.duty, 3) + ' %'" />
                <x-calc.result label="Yüksek süre" value="$si(result.th, 's')" />
                <x-calc.result label="Düşük süre" value="$si(result.tl, 's')" />
            </div>
            {{-- Dalga şekli --}}
            <svg viewBox="0 0 400 70" class="w-full text-indigo-500" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path :d="[0, 1, 2, 3].map((k) => { const p = 100, h = p * result.duty / 100, x = k * p; return `${k ? 'L' : 'M'}${x} 60 V10 H${x + h} V60 H${x + p}`; }).join(' ')" />
            </svg>
        </div>
    </template>
    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">t<sub>H</sub> = 0,693 (R1 + R2) C &nbsp;·&nbsp; t<sub>L</sub> = 0,693 R2 C &nbsp;·&nbsp; f = 1,44 / ((R1 + 2 R2) C)</p>
</div>
