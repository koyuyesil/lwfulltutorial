@php $mono = 'font-family="ui-monospace,monospace"'; @endphp
<div x-data="timer555Calc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="grid gap-4 sm:grid-cols-3">
            <x-calc.input label="R1" model="r1" unit="Ω" />
            <x-calc.input label="R2" model="r2" unit="Ω" />
            <x-calc.input label="C" model="c" unit="F" />
        </div>
        <template x-if="result">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Frekans" value="$si(result.f, 'Hz')" accent />
                <x-calc.result label="Görev oranı" value="$num(result.duty, 3) + ' %'" />
                <x-calc.result label="Yüksek süre tH" value="$si(result.th, 's')" />
                <x-calc.result label="Düşük süre tL" value="$si(result.tl, 's')" />
            </div>
        </template>
        <x-calc.formula>t<sub>H</sub> = 0,693 (R1 + R2) C &nbsp;·&nbsp; t<sub>L</sub> = 0,693 R2 C &nbsp;·&nbsp; f = 1,44 / ((R1 + 2 R2) C)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model · DIP-8" badge="çıkış LED'i gerçek hızda">
        <x-calc.scene :w="360" :h="190" :rx="52" :rz="-24">
            <div class="absolute rounded-xl" style="left:10px; top:10px; width:340px; height:170px; background: linear-gradient(135deg,#1d4ed8,#1e3a8a); box-shadow: inset 0 0 0 2px #172554;"></div>
            <div class="absolute opacity-20" style="left:10px; top:10px; width:340px; height:170px; background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 14px 14px;"></div>
            {{-- Bacaklar --}}
            @foreach (range(0, 3) as $i)
                <x-pcb-box :x="72 + $i * 34" :y="38" :w="12" :h="18" depth="5" top="linear-gradient(180deg,#f8fafc,#94a3b8)" side="#94a3b8" side2="#475569" />
                <x-pcb-box :x="72 + $i * 34" :y="134" :w="12" :h="18" depth="5" top="linear-gradient(180deg,#94a3b8,#f8fafc)" side="#94a3b8" side2="#475569" />
            @endforeach
            {{-- Gövde --}}
            <x-pcb-box :x="56" :y="52" :w="148" :h="86" depth="20" top="linear-gradient(135deg,#27272a,#09090b)" side="#18181b" side2="#020202" :radius="4">
                <div class="absolute left-0 top-1/2 h-6 w-3 -translate-y-1/2 rounded-r-full bg-black/70"></div>
                <div class="absolute left-4 top-3 h-2 w-2 rounded-full bg-zinc-600"></div>
                <div class="flex h-full flex-col items-center justify-center">
                    <span class="font-mono text-xl font-black tracking-[.15em] text-zinc-200">NE555</span>
                    <span class="font-mono text-[9px] text-zinc-500">TIMER · 2342</span>
                </div>
            </x-pcb-box>
            {{-- Çıkış LED'i --}}
            <div class="absolute rounded-full" style="left:262px; top:70px; width:56px; height:56px; background: radial-gradient(circle, rgba(239,68,68,.75), transparent 70%); filter: blur(4px);"
                :class="blink ? 'calc-blink' : ''" :style="{ animationDuration: blink + 's', opacity: result ? 1 : 0.15 }"></div>
            <x-pcb-box :x="276" :y="84" :w="28" :h="28" depth="16" top="radial-gradient(circle at 35% 30%,#fecaca,#ef4444 45%,#7f1d1d)" side="#b91c1c" side2="#7f1d1d" :radius="14" />
            <div class="absolute font-mono text-[10px] font-bold text-white/70" style="left:268px; top:122px;">LED1</div>
            <div class="absolute font-mono text-[10px] font-bold text-white/70" style="left:20px; top:18px;">U1</div>
        </x-calc.scene>

        {{-- Dalga şekli --}}
        <svg viewBox="0 0 360 80" class="mt-1 w-full" fill="none" aria-hidden="true">
            <line x1="10" y1="62" x2="350" y2="62" stroke="#334155" />
            <path :d="wave" stroke="#818cf8" stroke-width="2.5" stroke-linejoin="round" />
            <text x="12" y="10" fill="#a5b4fc" {!! $mono !!} font-size="9" x-text="result ? 'tH ' + $si(result.th, 's', 3) + ' · tL ' + $si(result.tl, 's', 3) : ''"></text>
            <text x="350" y="10" text-anchor="end" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="800" x-text="result ? $si(result.f, 'Hz', 3) + ' · %' + $num(result.duty, 3) : ''"></text>
            <text x="350" y="76" text-anchor="end" fill="#64748b" {!! $mono !!} font-size="9" x-text="result && ! blink ? '≥ 25 Hz: göz sürekli yanıyor görür' : ''"></text>
        </svg>
    </x-calc.stage>
</div>
