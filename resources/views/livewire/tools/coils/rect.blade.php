<div x-data="rectCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        @include('livewire.tools.coils.partials.mode')

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Gövde kenarı a (yükseklik)" model="a" unit="mm" />
            <x-calc.input label="Gövde kenarı b (derinlik)" model="b" unit="mm" hint="Kare gövde için a = b" />
            <x-calc.input label="Tel çapı" model="dw" unit="mm" />
            <x-calc.input label="Sarım adımı" model="p" unit="mm" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="2000" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" /></template>
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" />
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı · uzunluk" value="Number.isFinite(N) ? $num(N, 4) + ' · ' + $num(Len, 3) + ' mm' : '—'" />
            <x-calc.result label="Eşdeğer daire çapı" value="Number.isFinite(De) ? $num(De, 4) + ' mm' : '—'" />
            <x-calc.result label="Tel uzunluğu / DC direnç" value="$si(wireLen, 'm') + ' · ' + $si(rdc, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>

        <x-calc.formula>Dikdörtgen kesit, aynı alanlı daireye (D<sub>e</sub> = 2·√(a·b/π)) çevrilip tek katman formülüyle hesaplanır · yaklaşık ±%5</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="dikdörtgen kesit · alan çizgileri">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="rect-cu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".35" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
                <linearGradient id="rect-core" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#94a3b8" stop-opacity=".4" /><stop offset=".5" stop-color="#e2e8f0" stop-opacity=".12" /><stop offset="1" stop-color="#0f172a" stop-opacity=".55" /></linearGradient>
            </defs>
            <path :d="geo.back" stroke="#7c2d12" stroke-width="3" stroke-linecap="round" opacity=".8" />
            <path :d="geo.body.body" fill="url(#rect-core)" stroke="#94a3b8" stroke-opacity=".3" />
            <path :d="geo.body.cap" fill="#1e293b" fill-opacity=".6" stroke="#94a3b8" stroke-opacity=".4" />
            @include('livewire.tools.coils.partials.field', ['lines' => 'geo.field', 'x1' => 'geo.x1 - geo.body.rx', 'x2' => 'geo.x2 + geo.body.rx'])
            <path :d="geo.front" stroke="url(#rect-cu)" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" />
            <path :d="geo.front" stroke="#fff7ed" stroke-width=".8" stroke-linecap="round" opacity=".5" />
            <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
                <text x="16" y="22" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 3) : 'N ?'"></text>
                <text x="384" y="22" text-anchor="end" fill="#a5b4fc" x-text="$num(A, 3) + ' × ' + $num(B, 3) + ' mm'"></text>
            </g>
        </svg>
        @include('livewire.tools.coils.partials.stage-footer')
    </x-calc.stage>
</div>
