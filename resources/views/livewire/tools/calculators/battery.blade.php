<div x-data="batteryCalc" class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-calc.input label="Kapasite" model="capacity" unit="mAh" hint="Telefon: genelde 3000–5000 mAh" />
        <x-calc.input label="Nominal gerilim" model="voltage" unit="V" hint="Li-ion: 3,7 / 3,85 V" />
        <x-calc.input label="Ortalama tüketim" model="load" unit="A" hint="Örn. 350m = 350 mA" />
        <x-calc.input label="Şarj akımı" model="charge" unit="A" />
        <label class="block sm:col-span-2">
            <span class="flex justify-between text-sm font-medium text-slate-700 dark:text-slate-200">Verim <span class="font-mono" x-text="efficiency + ' %'"></span></span>
            <input type="range" min="50" max="100" x-model.number="efficiency" class="mt-3 w-full accent-indigo-600">
            <span class="mt-1 block text-xs text-slate-500">Dönüşüm kayıpları, sıcaklık ve pil yaşlanması için.</span>
        </label>
    </div>
    <template x-if="result">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-calc.result label="Enerji" value="$num(result.wh, 3) + ' Wh'" hint="Uçuş sınırı: 100 Wh" />
            <x-calc.result label="Çalışma süresi" value="hours(result.runtime)" accent />
            <x-calc.result label="Şarj süresi (%0→100)" value="hours(result.chargeTime)" hint="Sabit akım varsayımı; son %20 daha yavaştır" />
            <x-calc.result label="Şarj hızı" value="$num(result.cRate, 3) + ' C'" />
        </div>
    </template>
    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">Wh = Ah × V &nbsp;·&nbsp; süre = Ah × verim / akım &nbsp;·&nbsp; C = şarj akımı / kapasite</p>
</div>
