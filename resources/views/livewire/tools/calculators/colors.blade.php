<div x-data="colorCalc" class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
            @foreach ([4, 5, 6] as $n)
                <button type="button" @click="bands = {{ $n }}; normalize()" :class="bands === {{ $n }} ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">{{ $n }} bant</button>
            @endforeach
        </div>
        <form @submit.prevent="applyReverse()" class="flex items-center gap-2">
            <label for="rev" class="text-sm text-slate-500">Değerden renge</label>
            <input id="rev" x-model="reverse" class="w-28 rounded-lg border-0 bg-white px-3 py-1.5 font-mono text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10" placeholder="4k7">
            <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">Uygula</button>
        </form>
    </div>

    {{-- Direnç çizimi --}}
    <div class="flex justify-center rounded-xl bg-slate-50 py-6 dark:bg-white/5">
        <svg viewBox="0 0 360 90" class="w-full max-w-lg" aria-hidden="true">
            <line x1="0" y1="45" x2="360" y2="45" stroke="#94a3b8" stroke-width="6" stroke-linecap="round" />
            <rect x="70" y="15" width="220" height="60" rx="28" fill="#e8d3a6" stroke="#b8a074" stroke-width="2" />
            @foreach (range(0, 5) as $i)
                <rect x-show="{{ $i }} < layout.length" :x="({{ $i }} === layout.length - 1 ? 248 : {{ 100 + $i * 26 }})" y="15" width="14" height="60" :fill="colors[b[{{ $i }}]]?.hex" stroke="#00000022" />
            @endforeach
        </svg>
    </div>

    <div class="grid gap-3" :class="{ 'sm:grid-cols-4': bands === 4, 'sm:grid-cols-5': bands === 5, 'sm:grid-cols-6': bands === 6 }">
        <template x-for="(role, i) in layout" :key="bands + '-sel-' + i">
            <label class="block">
                <span class="text-xs font-semibold text-slate-500" x-text="({ d: `${i + 1}. rakam`, m: 'Çarpan', t: 'Tolerans', c: 'Sıcaklık kats.' })[role]"></span>
                <div class="relative mt-1">
                    <span class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 rounded-full ring-1 ring-black/20" :style="`background:${colors[b[i]]?.hex}`"></span>
                    <select x-model.number="b[i]" class="block w-full rounded-lg border-0 bg-white py-2 pl-8 pr-8 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                        <template x-for="c in optionsFor(role)" :key="c.name">
                            <option :value="colors.indexOf(c)" :selected="colors.indexOf(c) === b[i]"
                                x-text="c.name + (role === 'd' ? ` (${c.digit})` : role === 'm' ? ` (×${$num(c.mult)})` : role === 't' ? ` (±${$num(c.tol)} %)` : ` (${c.tc} ppm)`)"></option>
                        </template>
                    </select>
                </div>
            </label>
        </template>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <x-calc.result label="Direnç" value="$si(value.R, 'Ω')" accent />
        <x-calc.result label="Tolerans" value="value.tol ? '±' + $num(value.tol) + ' %' : '—'" />
        <x-calc.result label="Aralık" value="value.min !== null ? $si(value.min, 'Ω', 3) + ' – ' + $si(value.max, 'Ω', 3) : '—'" />
    </div>
    <p x-show="bands === 6" class="text-sm text-slate-500">Sıcaklık katsayısı: <strong x-text="value.tc ? value.tc + ' ppm/K' : '—'"></strong></p>
</div>
