<div x-data="powerCalc" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-calc.input label="Güç" model="watt" unit="W" hint="Cihaz / adaptör gücü" />
        <x-calc.input label="Günlük kullanım" model="hours" unit="saat" />
        <x-calc.input label="Aylık gün" model="days" unit="gün" />
        <x-calc.input label="Birim fiyat" model="price" unit="₺/kWh" />
    </div>
    <template x-if="result">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <x-calc.result label="Günlük" value="$num(result.daily, 3) + ' kWh'" />
            <x-calc.result label="Aylık" value="$num(result.monthly, 3) + ' kWh'" />
            <x-calc.result label="Yıllık" value="$num(result.yearly, 4) + ' kWh'" />
            <x-calc.result label="Aylık maliyet" value="'₺' + $num(result.cost, 4)" accent />
            <x-calc.result label="Yıllık maliyet" value="'₺' + $num(result.yearlyCost, 5)" />
        </div>
    </template>
    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">kWh = W × saat / 1000 &nbsp;·&nbsp; maliyet = kWh × birim fiyat</p>
</div>
