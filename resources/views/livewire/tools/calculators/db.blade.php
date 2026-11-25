@php $mono = 'font-family="ui-monospace,monospace"'; @endphp
<div x-data="dbCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-6">
        <section class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h4 class="font-semibold text-slate-900 dark:text-white">Oran → dB</h4>
                <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
                    <button type="button" @click="mode = 'power'" :class="mode === 'power' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1 text-xs font-semibold">Güç (10 log)</button>
                    <button type="button" @click="mode = 'voltage'" :class="mode === 'voltage' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1 text-xs font-semibold">Gerilim (20 log)</button>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-calc.input label="Giriş" model="a" />
                <x-calc.input label="Çıkış" model="b" />
                <x-calc.result label="Kazanç" value="$num(ratioDb, 4) + ' dB'" accent />
            </div>
        </section>
        <section class="space-y-4 border-t border-slate-100 pt-5 dark:border-white/5">
            <h4 class="font-semibold text-slate-900 dark:text-white">dBm ↔ güç</h4>
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.input label="dBm" model="dbm" unit="dBm" />
                <x-calc.result label="Güç" value="$si(fromDbm, 'W')" />
                <x-calc.input label="mW" model="mw" unit="mW" />
                <x-calc.result label="dBm" value="$num(fromMw, 4) + ' dBm'" />
            </div>
        </section>
    </div>

    <x-calc.stage label="Gösterge" badge="−40 … +40 dB">
        <svg viewBox="0 0 300 170" class="mx-auto w-full max-w-sm" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="db-arc" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#ef4444" /><stop offset=".5" stop-color="#94a3b8" /><stop offset="1" stop-color="#22c55e" /></linearGradient>
            </defs>
            <path d="M30 150 A120 120 0 0 1 270 150" stroke="#1e293b" stroke-width="22" stroke-linecap="round" />
            <path d="M30 150 A120 120 0 0 1 270 150" stroke="url(#db-arc)" stroke-width="10" stroke-linecap="round" />
            @foreach ([-40, -20, 0, 20, 40] as $tick)
                @php $a = deg2rad(180 - ($tick + 40) / 80 * 180); @endphp
                <line x1="{{ 150 + cos($a) * 100 }}" y1="{{ 150 - sin($a) * 100 }}" x2="{{ 150 + cos($a) * 88 }}" y2="{{ 150 - sin($a) * 88 }}" stroke="#94a3b8" stroke-width="2" />
                <text x="{{ 150 + cos($a) * 74 }}" y="{{ 154 - sin($a) * 74 }}" text-anchor="middle" fill="#94a3b8" {!! $mono !!} font-size="10">{{ $tick > 0 ? '+' . $tick : $tick }}</text>
            @endforeach
            <g :style="{ transform: `rotate(${needle}deg)`, transformOrigin: '150px 150px', transition: 'transform .6s cubic-bezier(.3,1.6,.5,1)' }">
                <path d="M146 150 L150 44 L154 150 Z" fill="#f8fafc" />
            </g>
            <circle cx="150" cy="150" r="10" fill="#0f172a" stroke="#f8fafc" stroke-width="3" />
            <text x="150" y="168" text-anchor="middle" fill="#64748b" {!! $mono !!} font-size="9" x-text="mode === 'power' ? 'güç oranı' : 'gerilim oranı'"></text>
        </svg>
        <p class="-mt-1 text-center font-mono text-3xl font-black text-white" x-text="Number.isFinite(ratioDb) ? (ratioDb > 0 ? '+' : '') + $num(ratioDb, 3) + ' dB' : '—'"></p>

        {{-- Telefon sinyal gücü (dBm) --}}
        <div class="mt-3 flex items-center gap-4 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
            <div class="flex h-10 items-end gap-1">
                @foreach (range(1, 5) as $bar)
                    <span class="w-2.5 rounded-sm transition-colors duration-300" style="height: {{ 16 + $bar * 16 }}%"
                        :class="signal.bars >= {{ $bar }} ? (signal.bars >= 4 ? 'bg-emerald-400' : signal.bars >= 2 ? 'bg-amber-400' : 'bg-rose-500') : 'bg-white/10'"></span>
                @endforeach
            </div>
            <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-wider text-slate-400">Telefon sinyali · <span x-text="dbm"></span> dBm</p>
                <p class="font-semibold text-white" x-text="signal.label"></p>
            </div>
            <p class="ms-auto text-right font-mono text-[10px] leading-tight text-slate-500">−50 mükemmel<br>−90 zayıf<br>−110 yok</p>
        </div>
    </x-calc.stage>
</div>
