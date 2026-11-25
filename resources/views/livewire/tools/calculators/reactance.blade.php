<div x-data="reactanceCalc" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-3">
        <x-calc.input label="Frekans (f)" model="f" unit="Hz" />
        <x-calc.input label="Endüktans (L)" model="l" unit="H" />
        <x-calc.input label="Kapasite (C)" model="c" unit="F" />
    </div>
    <div class="grid gap-3 sm:grid-cols-3">
        <x-calc.result label="Bobin reaktansı XL" value="$si(result.XL, 'Ω')" />
        <x-calc.result label="Kondansatör reaktansı XC" value="$si(result.XC, 'Ω')" />
        <x-calc.result label="LC rezonans f₀" value="$si(result.f0, 'Hz')" accent />
    </div>
    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">XL = 2π f L &nbsp;·&nbsp; XC = 1 / (2π f C) &nbsp;·&nbsp; f₀ = 1 / (2π √(L C))</p>
</div>
