<div x-data="smdCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <x-calc.input label="SMD kodu" model="code" :si="false" placeholder="103, 4R7, 01C" />

        <div class="flex flex-wrap gap-1.5">
            <span class="text-xs font-semibold text-slate-500">Örnekler:</span>
            <template x-for="ex in examples" :key="ex">
                <button type="button" @click="code = ex" :class="code.toUpperCase() === ex ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300'"
                    class="rounded-full px-2.5 py-1 font-mono text-xs font-semibold" x-text="ex"></button>
            </template>
        </div>

        <template x-if="result && result.error"><p class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-300" x-text="result.error"></p></template>
        <template x-if="result && ! result.error">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Direnç" value="$si(result.value, 'Ω')" accent />
                <x-calc.result label="Kod türü" value="result.type" class="[&_p.font-mono]:font-sans [&_p.font-mono]:text-sm [&_p.font-mono]:whitespace-normal" />
            </div>
        </template>

        <div class="space-y-1 rounded-xl bg-slate-50 px-4 py-3 text-xs text-slate-600 ring-1 ring-slate-200/70 dark:bg-white/5 dark:text-slate-300 dark:ring-white/5">
            <p><strong>3 hane:</strong> ilk iki rakam × 10<sup>üçüncü</sup> (103 = 10 kΩ) · <strong>4 hane:</strong> ilk üç × 10<sup>dördüncü</sup> (1002 = 10 kΩ)</p>
            <p><strong>R</strong> ondalık noktadır (4R7 = 4,7 Ω) · <strong>EIA-96:</strong> iki rakam (E96 değeri) + harf çarpan (01C = 10 kΩ)</p>
        </div>
    </div>

    <x-calc.stage label="3D model · 0805" badge="sürükleyerek döndürün">
        <x-calc.scene :w="360" :h="210" :rx="55" :rz="-28">
            {{-- PCB parçası --}}
            <div class="absolute rounded-xl" style="left:20px; top:20px; width:320px; height:170px; background: linear-gradient(135deg,#15803d,#065f46); box-shadow: inset 0 0 0 2px #064e3b;"></div>
            <div class="absolute opacity-20" style="left:20px; top:20px; width:320px; height:170px; background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 14px 14px;"></div>
            {{-- Bakır yollar ve pedler --}}
            <div class="absolute rounded-sm bg-[#5be39a]/50" style="left:20px; top:98px; width:80px; height:14px;"></div>
            <div class="absolute rounded-sm bg-[#5be39a]/50" style="left:260px; top:98px; width:80px; height:14px;"></div>
            <div class="absolute rounded-sm" style="left:96px; top:72px; width:52px; height:66px; background: linear-gradient(180deg,#f1f5f9,#94a3b8 55%,#e2e8f0);"></div>
            <div class="absolute rounded-sm" style="left:212px; top:72px; width:52px; height:66px; background: linear-gradient(180deg,#f1f5f9,#94a3b8 55%,#e2e8f0);"></div>
            {{-- Direnç gövdesi (3D kutu) --}}
            <x-pcb-box :x="112" :y="78" :w="136" :h="54" depth="22"
                :top="'linear-gradient(90deg,#e2e8f0 0 15%,#111827 15% 85%,#e2e8f0 85%)'"
                :side="'linear-gradient(90deg,#94a3b8 0 15%,#0b0f17 15% 85%,#94a3b8 85%)'"
                :side2="'linear-gradient(90deg,#64748b 0 15%,#000 15% 85%,#64748b 85%)'"
                :ns="'#cbd5e1'" :ns2="'#64748b'" :radius="3">
                <div class="flex h-full items-center justify-center">
                    <span class="font-mono text-2xl font-black tracking-[.2em] text-white [text-shadow:0_0_6px_rgba(255,255,255,.35)]" x-text="(code || '—').toUpperCase().slice(0, 4)"></span>
                </div>
            </x-pcb-box>
            <div class="absolute font-mono text-[11px] font-bold text-white/70" style="left:30px; top:28px;">R12</div>
        </x-calc.scene>
        <p class="text-center font-mono text-2xl font-black text-white" x-text="result && ! result.error ? $si(result.value, 'Ω') : '—'"></p>
    </x-calc.stage>
</div>
