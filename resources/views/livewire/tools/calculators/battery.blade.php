<div x-data="batteryCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Kapasite" model="capacity" unit="mAh" hint="Telefon: genelde 3000–5000 mAh" />
            <x-calc.input label="Nominal gerilim" model="voltage" unit="V" hint="Li-ion: 3,7 / 3,85 V" />
            <x-calc.input label="Ortalama tüketim" model="load" unit="A" hint="örn. 350m = 350 mA" />
            <x-calc.input label="Şarj akımı" model="charge" unit="A" />
            <label class="block sm:col-span-2">
                <span class="flex justify-between text-sm font-medium text-slate-700 dark:text-slate-200">Verim <span class="font-mono" x-text="efficiency + ' %'"></span></span>
                <input type="range" min="50" max="100" x-model.number="efficiency" class="mt-3 w-full accent-indigo-600">
                <span class="mt-1 block text-xs text-slate-500">Dönüşüm kayıpları, sıcaklık ve pil yaşlanması için.</span>
            </label>
        </div>
        <template x-if="result">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Enerji" value="$num(result.wh, 3) + ' Wh'" hint="Uçuş sınırı: 100 Wh" />
                <x-calc.result label="Çalışma süresi" value="hours(result.runtime)" accent />
                <x-calc.result label="Şarj süresi (%0→100)" value="hours(result.chargeTime)" hint="Sabit akım varsayımı; son %20 daha yavaştır" />
                <x-calc.result label="Şarj hızı" value="$num(result.cRate, 3) + ' C'" />
            </div>
        </template>
        <x-calc.formula>Wh = Ah × V &nbsp;·&nbsp; süre = Ah × verim / akım &nbsp;·&nbsp; C = şarj akımı / kapasite</x-calc.formula>
    </div>

    <x-calc.stage label="3D model · Li-ion hücre" badge="şarj animasyonu">
        <x-calc.scene :w="360" :h="200" :rx="42" :rz="-18">
            {{-- Hücre gövdesi --}}
            <x-pcb-box :x="40" :y="40" :w="260" :h="120" depth="18" top="linear-gradient(135deg,#334155,#0f172a)" side="#1e293b" side2="#020617" :radius="10">
                <div class="flex h-full flex-col justify-between p-3">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[11px] font-black tracking-widest text-sky-300">Li-ion</span>
                        <span class="font-mono text-[10px] text-slate-400" x-text="voltage + ' V'"></span>
                    </div>
                    <div>
                        <p class="font-mono text-2xl font-black text-white" x-text="capacity + ' mAh'"></p>
                        <p class="font-mono text-[10px] text-slate-400" x-text="result ? $num(result.wh, 3) + ' Wh' : ''"></p>
                    </div>
                    <div class="relative h-3 overflow-hidden rounded-full bg-white/10 ring-1 ring-white/10">
                        <div class="calc-charge absolute inset-0 rounded-full bg-linear-to-r from-emerald-500 via-lime-400 to-emerald-300"
                            :style="{ animationDuration: (result && result.cRate > 0 ? Math.max(0.8, 3.2 / result.cRate) : 3) + 's' }"></div>
                    </div>
                </div>
            </x-pcb-box>
            {{-- Koruma kartı (PCM) ve uçlar --}}
            <x-pcb-box :x="300" :y="62" :w="34" :h="76" depth="8" top="linear-gradient(180deg,#15803d,#064e3b)" side="#14532d" side2="#052e16" />
            <x-pcb-box :x="334" :y="74" :w="14" :h="16" depth="3" top="#fcd34d" side="#b45309" side2="#78350f" />
            <x-pcb-box :x="334" :y="110" :w="14" :h="16" depth="3" top="#fcd34d" side="#b45309" side2="#78350f" />
            <div class="absolute font-mono text-[9px] font-black text-rose-400" style="left:350px; top:76px;">+</div>
            <div class="absolute font-mono text-[9px] font-black text-sky-300" style="left:350px; top:112px;">−</div>
        </x-calc.scene>

        <div class="mt-2 space-y-2">
            @foreach (['run' => ['Çalışma', 'from-sky-500 to-cyan-300', 'runtime'], 'charge' => ['Şarj', 'from-emerald-500 to-lime-300', 'chargeTime']] as $k => [$label, $grad, $field])
                <div class="grid grid-cols-[56px_1fr_auto] items-center gap-2 text-xs">
                    <span class="text-slate-400">{{ $label }}</span>
                    <span class="relative h-2 overflow-hidden rounded-full bg-white/10">
                        <span class="absolute inset-y-0 left-0 rounded-full bg-linear-to-r {{ $grad }} transition-all duration-500" :style="{ width: bars.{{ $k }} + '%' }"></span>
                    </span>
                    <span class="font-mono font-bold text-white" x-text="result ? hours(result.{{ $field }}) : '—'"></span>
                </div>
            @endforeach
            <p class="flex items-center gap-2 text-xs text-slate-400">
                <span class="h-2.5 w-2.5 rounded-full" :style="{ background: cTone, boxShadow: `0 0 8px ${cTone}` }"></span>
                <span x-text="result && Number.isFinite(result.cRate) ? $num(result.cRate, 2) + ' C — ' + (result.cRate <= 1 ? 'pil dostu şarj' : result.cRate <= 2 ? 'hızlı şarj, ısınma olabilir' : 'çok yüksek: hücreyi zorlar') : 'Şarj akımı girin'"></span>
            </p>
        </div>
    </x-calc.stage>
</div>
