<div x-data="traceCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Akım" model="current" unit="A" />
            <x-calc.input label="İzin verilen ısınma" model="rise" unit="°C" />
            <x-calc.input label="Bakır kalınlığı" model="copper" unit="oz" hint="1 oz ≈ 35 µm" />
            <x-calc.input label="Yol uzunluğu" model="length" unit="mm" />
        </div>
        <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
            <button type="button" @click="layer = 'external'" :class="layer === 'external' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">Dış katman</button>
            <button type="button" @click="layer = 'internal'" :class="layer === 'internal' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">İç katman</button>
        </div>
        <template x-if="result">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Minimum genişlik" value="$num(result.widthMm, 3) + ' mm'" accent />
                <x-calc.result label="Genişlik (mil)" value="$num(result.widthMils, 4) + ' mil'" />
                <x-calc.result label="Yol direnci" value="$si(result.R, 'Ω')" />
                <x-calc.result label="Düşüm / kayıp" class="[&_p.font-mono]:text-lg [&_p.font-mono]:mt-2" value="$si(result.drop, 'V', 3) + ' · ' + $si(result.loss, 'W', 3)" />
            </div>
        </template>
        <x-calc.formula>IPC-2221: I = k × ΔT<sup>0,44</sup> × A<sup>0,725</sup> (k: dış 0,048 · iç 0,024; A: mil²). Jumper teli ya da onarım yolu çekerken bu genişliğin altına inmeyin.</x-calc.formula>
    </div>

    <x-calc.stage label="3D kesit" badge="genişlik log ölçekte">
        <x-calc.scene :w="360" :h="220" :rx="60" :rz="-32">
            {{-- FR-4 levha --}}
            <x-pcb-box :x="20" :y="20" :w="320" :h="180" depth="16" top="linear-gradient(135deg,#16a34a,#065f46)" side="#14532d" side2="#052e16" :radius="6" />
            {{-- Bakır yol: genişlik ve kalınlık hesaba göre --}}
            <div class="absolute transition-all duration-500" :style="{ left: '20px', top: (110 - tracePx / 2) + 'px', width: '320px', height: tracePx + 'px', transformStyle: 'preserve-3d', transform: `translateZ(${layer === 'external' ? 16 : 8}px)` }">
                <div class="absolute inset-0" :style="{ transformStyle: 'preserve-3d' }">
                    {{-- üst yüz --}}
                    <div class="absolute inset-0 rounded-[2px] bg-linear-to-b from-amber-200 via-orange-400 to-amber-600 shadow-[0_0_18px_rgba(251,146,60,.55)]"
                        :style="{ transform: `translateZ(${copperPx}px)`, opacity: layer === 'external' ? 1 : .55 }">
                        <svg class="absolute inset-0 h-full w-full" preserveAspectRatio="none" viewBox="0 0 320 10" aria-hidden="true">
                            <path d="M0 5 H320" stroke="#fff7ed" stroke-width="1.2" class="calc-flow" style="animation-duration: .7s" />
                        </svg>
                    </div>
                    {{-- yan yüz (kalınlık) --}}
                    <div class="absolute left-0 w-full bg-amber-700" :style="{ top: tracePx + 'px', height: copperPx + 'px', transformOrigin: 'top', transform: 'rotateX(90deg)' }"></div>
                    <div class="absolute left-0 top-0 w-full bg-amber-900" :style="{ height: copperPx + 'px', transformOrigin: 'top', transform: 'rotateX(90deg)' }"></div>
                </div>
            </div>
            {{-- İç katmanda üst prepreg (yarı saydam) --}}
            <div x-show="layer === 'internal'" class="absolute rounded-md bg-emerald-700/45" style="left:20px; top:20px; width:320px; height:180px; transform: translateZ(28px);"></div>
        </x-calc.scene>
        <div class="mt-1 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-lg bg-white/5 px-2 py-2 ring-1 ring-white/10"><p class="text-[10px] uppercase tracking-wider text-slate-400">genişlik</p><p class="font-mono text-sm font-black text-amber-300" x-text="result ? $num(result.widthMm, 3) + ' mm' : '—'"></p></div>
            <div class="rounded-lg bg-white/5 px-2 py-2 ring-1 ring-white/10"><p class="text-[10px] uppercase tracking-wider text-slate-400">bakır</p><p class="font-mono text-sm font-black text-amber-300" x-text="result ? result.thicknessUm.toFixed(0) + ' µm' : '—'"></p></div>
            <div class="rounded-lg bg-white/5 px-2 py-2 ring-1 ring-white/10"><p class="text-[10px] uppercase tracking-wider text-slate-400">ısınma</p><p class="font-mono text-sm font-black text-rose-300" x-text="'+' + rise + ' °C'"></p></div>
        </div>
    </x-calc.stage>
</div>
