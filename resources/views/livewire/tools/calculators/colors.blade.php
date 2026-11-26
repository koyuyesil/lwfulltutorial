<div x-data="colorCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
                @foreach ([4, 5, 6] as $n)
                    <button type="button" @click="bands = {{ $n }}; normalize(); applyReverse()" :class="bands === {{ $n }} ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">{{ $n }} bant</button>
                @endforeach
            </div>
            <form @submit.prevent="applyReverse()" class="flex items-center gap-2">
                <label for="rev" class="text-sm text-slate-500">Değerden renge</label>
                <input id="rev" x-model="reverse" class="w-24 rounded-lg border-0 bg-white px-3 py-1.5 font-mono text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10" placeholder="4k7">
                <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">Uygula</button>
            </form>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <template x-for="(role, i) in layout" :key="bands + '-sel-' + i">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-500" x-text="({ d: `${i + 1}. rakam`, m: 'Çarpan', t: 'Tolerans', c: 'Sıcaklık kats.' })[role]"></span>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 rounded-full ring-1 ring-black/20" :style="{ background: colors[b[i]]?.hex }"></span>
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

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Direnç" value="$si(value.R, 'Ω')" accent />
            <x-calc.result label="Aralık" class="[&_p.font-mono]:text-lg [&_p.font-mono]:mt-2" value="value.min !== null ? $si(value.min, 'Ω', 3) + ' – ' + $si(value.max, 'Ω', 3) : '—'" />
        </div>
        <p class="text-sm text-slate-500">Tolerans: <strong x-text="value.tol ? '±' + $num(value.tol) + ' %' : '—'"></strong>
            <span x-show="bands === 6"> · Sıcaklık katsayısı: <strong x-text="value.tc ? value.tc + ' ppm/K' : '—'"></strong></span></p>
    </div>

    <x-calc.stage label="3D model" badge="tolerans bandı sağda">
        {{-- Gerçek 3D: silindir gölgelendirmesi + perspektif eğim + süzülme --}}
        <div class="grid place-items-center py-6 [perspective:900px]">
            <div class="calc-float w-full max-w-md">
                <div class="transition-transform duration-700 [transform:rotateX(16deg)_rotateY(-14deg)_rotateZ(-4deg)] [transform-style:preserve-3d] hover:[transform:rotateX(4deg)_rotateY(8deg)_rotateZ(0deg)]">
                    <svg viewBox="0 0 400 150" class="w-full drop-shadow-[0_28px_18px_rgba(0,0,0,.55)]" aria-hidden="true">
                        <defs>
                            <linearGradient id="rc-lead" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f1f5f9" /><stop offset=".5" stop-color="#94a3b8" /><stop offset="1" stop-color="#475569" /></linearGradient>
                            <linearGradient id="rc-shade" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#ffffff" stop-opacity=".55" /><stop offset=".18" stop-color="#ffffff" stop-opacity=".15" />
                                <stop offset=".5" stop-color="#000000" stop-opacity="0" /><stop offset=".85" stop-color="#000000" stop-opacity=".35" /><stop offset="1" stop-color="#000000" stop-opacity=".55" />
                            </linearGradient>
                            <clipPath id="rc-body"><path d="M70 40 C70 22 120 22 124 36 H276 C280 22 330 22 330 40 V110 C330 128 280 128 276 114 H124 C120 128 70 128 70 110 Z" /></clipPath>
                        </defs>
                        <rect x="0" y="70" width="400" height="10" rx="5" fill="url(#rc-lead)" />
                        <ellipse cx="200" cy="140" rx="150" ry="6" fill="#000" opacity=".35" />
                        <g clip-path="url(#rc-body)">
                            <rect x="60" y="20" width="280" height="110" fill="#d9c29a" />
                            @foreach (range(0, 5) as $i)
                                <rect x-show="{{ $i }} < layout.length" :x="bandX[{{ $i }}]" y="20" :width="bandW" height="110" :fill="colors[b[{{ $i }}]]?.hex" />
                            @endforeach
                            <rect x="60" y="20" width="280" height="110" fill="url(#rc-shade)" />
                        </g>
                        <path d="M70 40 C70 22 120 22 124 36 H276 C280 22 330 22 330 40 V110 C330 128 280 128 276 114 H124 C120 128 70 128 70 110 Z" fill="none" stroke="#000" stroke-opacity=".25" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 font-mono text-3xl font-black tracking-tight text-white" x-text="$si(value.R, 'Ω')"></p>
            <p class="font-mono text-xs text-slate-400" x-text="value.tol ? '±' + $num(value.tol) + ' % · ' + layout.length + ' bant' : layout.length + ' bant'"></p>
        </div>
    </x-calc.stage>
</div>
