<div x-data="rodCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        @include('livewire.tools.coils.partials.mode')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Çubuk uzunluğu" model="lr" unit="mm" />
            <x-calc.input label="Çubuk çapı" model="dr" unit="mm" />
            <x-calc.input label="Malzeme geçirgenliği (µi)" model="mu" :si="false" step="10" min="1" max="20000" hint="MW / LW anten çubukları: 400–2000" />
            <x-calc.input label="Bobin gövdesi çapı" model="dc" unit="mm" hint="Çubuk + makara kalınlığı" />
            <x-calc.input label="Tel çapı" model="dw" unit="mm" />
            <x-calc.input label="Sarım adımı" model="p" unit="mm" />
            <x-calc.input label="Merkezden kaydırma" model="offset" unit="mm" :min="null" hint="Bobin merkezinin çubuk ortasına uzaklığı" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="3000" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" hint="MW anten: ~200–400µ" /></template>
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" hint="Ayar kondansatörü, örn. 365p" />
        </div>

        <p x-show="r?.tooLong" x-cloak class="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 ring-1 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/30">Bobin çubuktan uzun; daha az sarım ya da daha uzun çubuk gerekir.</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı · uzunluk" value="Number.isFinite(N) ? $num(N, 4) + ' · ' + $num(r?.lc, 3) + ' mm' : '—'" />
            <x-calc.result label="Ferrit kazancı (L / L hava)" value="r && Number.isFinite(r.ratio) ? '× ' + $num(r.ratio * r.pos, 3) : '—'" />
            <x-calc.result label="Hava çekirdekli değer" value="$si(r?.air, 'H')" />
            <x-calc.result label="Tel uzunluğu / DC direnç" value="$si(wireLen, 'm') + ' · ' + $si(rdc, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" x-show="Number.isFinite(f0)" />
        </div>

        <x-calc.formula>L = L<sub>hava</sub> · (1 + x) / (1/k + x/µ<sub>fe</sub>) · konum (A. Payne) · yaklaşık ±%10–20; konum düzeltmesi: uca kaydırıldıkça ~0,65'e iner</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="ferrit alanı çubuk boyunca toplar">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="rod-cu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".35" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
                <linearGradient id="rod-fe" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#64748b" /><stop offset=".4" stop-color="#334155" /><stop offset="1" stop-color="#020617" /></linearGradient>
            </defs>
            <path :d="geo.coil.back" stroke="#7c2d12" stroke-width="2.6" stroke-linecap="round" opacity=".8" />
            <path :d="geo.rod.body" fill="url(#rod-fe)" stroke="#94a3b8" stroke-opacity=".3" />
            <path :d="geo.rod.cap" fill="#475569" stroke="#94a3b8" stroke-opacity=".4" />
            @include('livewire.tools.coils.partials.field', ['lines' => 'geo.field', 'x1' => 'geo.rod.x1 - geo.rod.rx', 'x2' => 'geo.rod.x2 + geo.rod.rx'])
            <path :d="geo.coil.front" stroke="url(#rod-cu)" stroke-width="2.8" stroke-linecap="round" />
            <path :d="geo.coil.front" stroke="#fff7ed" stroke-width=".7" stroke-linecap="round" opacity=".5" />
            <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
                <text x="16" y="22" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 3) : 'N ?'"></text>
                <text x="384" y="22" text-anchor="end" fill="#a5b4fc" x-text="$num($parse(lr), 3) + ' × Ø' + $num($parse(dr), 3) + ' mm'"></text>
            </g>
        </svg>
        @include('livewire.tools.coils.partials.stage-footer')
    </x-calc.stage>
</div>
