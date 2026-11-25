<div x-data="dividerCalc" class="space-y-6">
    <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
        <button type="button" @click="mode = 'vout'" :class="mode === 'vout' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">Çıkış gerilimi</button>
        <button type="button" @click="mode = 'r2'" :class="mode === 'r2' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">Hedef gerilim için R2</button>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_220px]">
        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-calc.input label="Giriş (Vin)" model="vin" unit="V" />
                <x-calc.input label="R1 (üst)" model="r1" unit="Ω" />
                <template x-if="mode === 'vout'"><x-calc.input label="R2 (alt)" model="r2" unit="Ω" /></template>
                <template x-if="mode === 'r2'"><x-calc.input label="Hedef Vout" model="vout" unit="V" /></template>
            </div>

            <template x-if="mode === 'vout' && out">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <x-calc.result label="Vout" value="$si(out.Vout, 'V')" accent />
                    <x-calc.result label="Akım" value="$si(out.I, 'A')" />
                    <x-calc.result label="R1 gücü" value="$si(out.P1, 'W')" />
                    <x-calc.result label="R2 gücü" value="$si(out.P2, 'W')" />
                </div>
            </template>

            <template x-if="mode === 'r2'">
                <div class="space-y-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">Standart seri
                        <select x-model="series" class="rounded-lg border-0 bg-slate-100 py-1 pl-2 pr-8 text-sm font-semibold dark:bg-white/5">
                            <option>E12</option><option>E24</option><option>E96</option>
                        </select>
                    </label>
                    <template x-if="solve">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <x-calc.result label="İdeal R2" value="$si(solve.R2, 'Ω')" />
                            <x-calc.result label="Standart R2" value="$si(solve.std, 'Ω')" accent />
                            <x-calc.result label="Gerçek Vout" value="$si(solve.actual, 'V')" />
                        </div>
                    </template>
                    <template x-if="solve">
                        <p class="text-sm text-slate-500">Standart değerle hata: <strong x-text="$num(solve.error, 3) + ' %'"></strong></p>
                    </template>
                    <p x-show="! solve" class="text-sm text-amber-600">Hedef gerilim 0 ile Vin arasında olmalı.</p>
                </div>
            </template>
        </div>

        {{-- Şema --}}
        <svg viewBox="0 0 160 220" class="mx-auto h-56 text-slate-700 dark:text-slate-200" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <text x="80" y="14" text-anchor="middle" font-size="12" fill="currentColor" stroke="none" x-text="'Vin ' + vin + ' V'"></text>
            <line x1="80" y1="20" x2="80" y2="40" />
            <rect x="68" y="40" width="24" height="50" rx="3" class="fill-white dark:fill-slate-800" />
            <text x="104" y="70" font-size="12" fill="currentColor" stroke="none" x-text="'R1 ' + r1"></text>
            <line x1="80" y1="90" x2="80" y2="120" />
            <circle cx="80" cy="110" r="3" fill="currentColor" />
            <line x1="80" y1="110" x2="130" y2="110" stroke-dasharray="4 3" />
            <text x="132" y="106" font-size="11" fill="currentColor" stroke="none">Vout</text>
            <rect x="68" y="120" width="24" height="50" rx="3" class="fill-white dark:fill-slate-800" />
            <text x="104" y="150" font-size="12" fill="currentColor" stroke="none" x-text="'R2 ' + (mode === 'vout' ? r2 : '?')"></text>
            <line x1="80" y1="170" x2="80" y2="195" />
            <line x1="64" y1="195" x2="96" y2="195" /><line x1="70" y1="201" x2="90" y2="201" /><line x1="76" y1="207" x2="84" y2="207" />
        </svg>
    </div>

    <p class="rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">Vout = Vin × R2 / (R1 + R2) &nbsp;·&nbsp; R2 = R1 × Vout / (Vin − Vout)</p>
</div>
