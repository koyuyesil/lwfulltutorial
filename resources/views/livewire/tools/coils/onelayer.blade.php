<div x-data="oneLayerCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        @include('livewire.tools.coils.partials.mode')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Gövde çapı (D)" model="d" unit="mm" hint="Sarımın üzerine sarıldığı çap" />
            <x-calc.input label="Tel çapı (d)" model="dw" unit="mm" hint="Bakır çapı (emaye hariç)" />
            <x-calc.input label="Sarım adımı (p)" model="p" unit="mm" hint="İki sarım merkezi arası · sık sarımda ≈ yalıtımlı tel çapı" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="2000" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" hint="örn. 1µ, 470n" /></template>
            <x-calc.input label="Çalışma frekansı" model="f" unit="Hz" hint="Q ve etkin endüktans için, örn. 14M" />
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" />
        </div>

        <p x-show="pitchError" x-cloak class="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 ring-1 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/30">Adım tel çapından küçük olamaz; hesapta tel çapı kullanıldı.</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı · uzunluk" value="Number.isFinite(N) ? $num(N, 4) + ' · ' + $num(Len, 3) + ' mm' : '—'" />
            <x-calc.result label="Q (çalışma frekansında)" value="Number.isFinite(Q) ? $num(Q, 3) : '—'" />
            <x-calc.result label="Öz rezonans (SRF)" value="$si(srf, 'Hz')" />
            <x-calc.result label="Öz kapasite" value="$si(Cs, 'F')" />
            <x-calc.result label="Etkin endüktans (f'de)" value="Number.isFinite(Leff) ? $si(Leff, 'H') : 'f ≥ SRF'" />
            <x-calc.result label="Tel uzunluğu" value="$si(wireLen, 'm')" />
            <x-calc.result label="Direnç DC / AC" value="$si(rdc, 'Ω') + ' / ' + $si(rac, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Deri kalınlığı <span class="font-mono" x-text="$num(delta * 1000, 3) + ' µm'"></span>. Q yalnız deri etkisini içerir; sık sarımda yakınlık etkisiyle gerçek Q %20–40 daha düşük olabilir.</p>

        <x-calc.formula>Wheeler sürekli formülü (Weaver) − µ0·a·N·(Ks + Km) Rosa düzeltmesi &nbsp;·&nbsp; C<sub>öz</sub>[pF] = D[cm]·(0,1126·l/D + 0,08 + 0,27/√(l/D)) (Medhurst)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="tel ve adım ölçekli · alan çizgileri">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="one-cu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".35" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
                <linearGradient id="one-core" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#e2e8f0" stop-opacity=".5" /><stop offset=".5" stop-color="#cbd5e1" stop-opacity=".18" /><stop offset="1" stop-color="#0f172a" stop-opacity=".55" /></linearGradient>
            </defs>
            <path :d="geo.back" stroke="#7c2d12" :stroke-width="wirePx * 0.9" stroke-linecap="round" opacity=".8" />
            <path :d="geo.body.body" fill="url(#one-core)" stroke="#94a3b8" stroke-opacity=".3" />
            @include('livewire.tools.coils.partials.field', ['lines' => 'geo.field', 'x1' => 'geo.x1 - geo.body.rx', 'x2' => 'geo.x2 + geo.body.rx'])
            <path :d="geo.front" stroke="url(#one-cu)" :stroke-width="wirePx" stroke-linecap="round" />
            <path :d="geo.front" stroke="#fff7ed" :stroke-width="wirePx * 0.25" stroke-linecap="round" opacity=".55" />
            <path :d="`M${geo.start.x} ${geo.start.y} l-30 0`" stroke="url(#one-cu)" :stroke-width="wirePx" stroke-linecap="round" />
            <path :d="`M${geo.end.x} ${geo.end.y} l30 0`" stroke="url(#one-cu)" :stroke-width="wirePx" stroke-linecap="round" />
            <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
                <text x="16" y="22" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 3) : 'N ?'"></text>
                <text x="384" y="22" text-anchor="end" fill="#a5b4fc" x-text="Number.isFinite(Q) ? 'Q ≈ ' + $num(Q, 3) : ''"></text>
                <text x="200" y="232" text-anchor="middle" fill="#cbd5e1" x-text="'p = ' + $num(P, 3) + ' mm · d = ' + $num(Dw, 3) + ' mm'"></text>
            </g>
        </svg>
        @include('livewire.tools.coils.partials.stage-footer')
    </x-calc.stage>
</div>
