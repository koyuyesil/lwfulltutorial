<div x-data="powerCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Güç" model="watt" unit="W" hint="Cihaz / adaptör gücü" />
            <x-calc.input label="Günlük kullanım" model="hours" unit="saat" max="24" />
            <x-calc.input label="Aylık gün" model="days" unit="gün" max="31" />
            <x-calc.input label="Birim fiyat" model="price" unit="₺/kWh" />
        </div>
        <template x-if="result">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Aylık maliyet" value="'₺' + $num(result.cost, 4)" accent />
                <x-calc.result label="Yıllık maliyet" value="'₺' + $num(result.yearlyCost, 5)" />
                <x-calc.result label="Günlük" value="$num(result.daily, 3) + ' kWh'" />
                <x-calc.result label="Aylık / yıllık" class="[&_p.font-mono]:text-lg [&_p.font-mono]:mt-2" value="$num(result.monthly, 3) + ' / ' + $num(result.yearly, 4) + ' kWh'" />
            </div>
        </template>
        <x-calc.formula>kWh = W × saat / 1000 &nbsp;·&nbsp; maliyet = kWh × birim fiyat</x-calc.formula>
    </div>

    <x-calc.stage label="Elektrik sayacı" badge="cihaz karşılaştırması · log ölçek">
        {{-- Sayaç --}}
        <div class="mx-auto max-w-xs rounded-2xl bg-linear-to-b from-slate-200 to-slate-400 p-2 shadow-[0_20px_30px_-12px_rgba(0,0,0,.7)]">
            <div class="rounded-xl bg-slate-900 p-3 ring-1 ring-black/40">
                <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-widest text-slate-500"><span>aylık</span><span>kWh</span></div>
                <div class="mt-1 flex justify-end gap-0.5 font-mono text-2xl font-black text-lime-300 [text-shadow:0_0_10px_rgba(190,242,100,.6)]">
                    <template x-for="(ch, i) in (result ? $num(result.monthly, 4) : '—').padStart(7, ' ').split('')" :key="i">
                        <span class="grid w-5 place-items-center rounded bg-black/60" x-text="ch === ' ' ? '0' : ch" :class="ch === ' ' ? 'text-lime-900' : ''"></span>
                    </template>
                </div>
                <div class="mt-2 flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-[10px] text-slate-400"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-rose-500"></span>imp/kWh</span>
                    <span class="font-mono text-sm font-black text-amber-300" x-text="result ? '₺' + $num(result.cost, 4) + ' / ay' : ''"></span>
                </div>
            </div>
        </div>

        {{-- Karşılaştırma --}}
        <div class="mt-4 space-y-1.5">
            <template x-for="d in compare" :key="d.name">
                <div class="grid grid-cols-[22px_96px_1fr_52px] items-center gap-2 text-xs">
                    <span x-text="d.icon"></span>
                    <span class="truncate" :class="d.me ? 'font-bold text-amber-300' : 'text-slate-300'" x-text="d.name"></span>
                    <span class="relative h-2 overflow-hidden rounded-full bg-white/5">
                        <span class="absolute inset-y-0 left-0 rounded-full transition-all duration-500" :class="d.me ? 'bg-linear-to-r from-amber-500 to-yellow-300 shadow-[0_0_10px_rgba(245,158,11,.8)]' : 'bg-slate-500'" :style="{ width: bar(d.w) + '%' }"></span>
                    </span>
                    <span class="text-right font-mono" :class="d.me ? 'font-bold text-amber-300' : 'text-slate-400'" x-text="$si(d.w, 'W', 3)"></span>
                </div>
            </template>
        </div>
    </x-calc.stage>
</div>
