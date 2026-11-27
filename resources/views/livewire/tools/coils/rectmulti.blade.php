<div x-data="rectMultiCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5" role="radiogroup" aria-label="Sarım türü">
                @foreach (['rect' => 'Dikdörtgen makara', 'foil' => 'Bakır folyo'] as $k => $l)
                    <button type="button" role="radio" @click="kind = '{{ $k }}'" :aria-checked="kind === '{{ $k }}' ? 'true' : 'false'"
                        :class="kind === '{{ $k }}' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">{{ $l }}</button>
                @endforeach
            </div>
            @include('livewire.tools.coils.partials.mode')
        </div>

        <template x-if="kind === 'rect'">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-calc.input label="Makara kenarı a" model="a" unit="mm" />
                <x-calc.input label="Makara kenarı b" model="b" unit="mm" />
                <x-calc.input label="Sarım uzunluğu" model="len" unit="mm" />
                <x-calc.input label="Tel çapı (yalıtımlı)" model="dw" unit="mm" />
                <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="100000" /></template>
                <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" /></template>
            </div>
        </template>
        <template x-if="kind === 'foil'">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-calc.input label="Gövde çapı" model="d" unit="mm" />
                <x-calc.input label="Folyo genişliği" model="w" unit="mm" hint="= sarım uzunluğu" />
                <x-calc.input label="Folyo kalınlığı" model="ft" unit="mm" />
                <x-calc.input label="Ara yalıtım kalınlığı" model="ins" unit="mm" />
                <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="nf" :si="false" step="1" min="1" max="2000" /></template>
                <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" /></template>
            </div>
        </template>
        <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" class="sm:max-w-[50%]" />

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı" value="Number.isFinite(N) ? $num(N, 4) : '—'" />
            <x-calc.result label="Sarım derinliği" value="r ? $num(r.c, 3) + ' mm' + (kind === 'rect' ? ' · ' + r.layers + ' kat × ' + r.perLayer : '') : '—'" />
            <x-calc.result label="İletken uzunluğu / DC direnç" value="$si(wireLen, 'm') + ' · ' + $si(rdc, 'Ω')" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" class="sm:col-span-2" x-show="Number.isFinite(f0)" />
        </div>

        <x-calc.formula>L[µH] = 0,8·a²·N² / (6a + 9b + 10c) (Wheeler, inç) &nbsp;·&nbsp; dikdörtgen makarada ortalama sarım aynı alanlı daireye çevrilir · folyoda c = N·(t + yalıtım)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="katmanlar · alan çizgileri">
        <svg viewBox="0 0 400 240" class="w-full" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="rm-cu" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fde68a" /><stop offset=".4" stop-color="#f59e0b" /><stop offset="1" stop-color="#9a3412" /></linearGradient>
                <linearGradient id="rm-foil" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fed7aa" /><stop offset=".3" stop-color="#fb923c" /><stop offset=".7" stop-color="#c2410c" /><stop offset="1" stop-color="#7c2d12" /></linearGradient>
            </defs>
            {{-- Arka: makara katmanlarının arka yarısı ve gövde (SVG içinde x-if çalışmaz; x-show kullanılır) --}}
            <g x-show="! geo.foil">
                @foreach (range(0, 3) as $i)<path x-show="geo.rings?.[{{ $i }}]" :d="geo.rings?.[{{ $i }}]?.back" stroke="#7c2d12" stroke-width="2.2" stroke-linecap="round" opacity="{{ 0.45 + $i * 0.12 }}" />@endforeach
            </g>
            <path :d="geo.core?.body" fill="#334155" opacity=".9" />
            @include('livewire.tools.coils.partials.field', ['lines' => 'geo.field', 'x1' => 'geo.fx1', 'x2' => 'geo.fx2'])
            <g x-show="! geo.foil">
                @foreach (range(0, 3) as $i)<path x-show="geo.rings?.[{{ $i }}]" :d="geo.rings?.[{{ $i }}]?.front" stroke="url(#rm-cu)" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" opacity="{{ 0.55 + $i * 0.15 }}" />@endforeach
                <path :d="geo.core?.cap" fill="#1e293b" stroke="#94a3b8" stroke-opacity=".5" />
            </g>
            <g x-show="geo.foil">
                <path :d="geo.outer?.body" fill="url(#rm-foil)" stroke="#7c2d12" stroke-opacity=".6" />
                {{-- Uç yüz: folyo katmanlarının spirali --}}
                <g :transform="`translate(${geo.outer?.x2 ?? 0} 120) scale(${geo.sx ?? 1} 1)`">
                    <circle :r="geo.outer?.ry ?? 0" fill="#9a3412" />
                    <path :d="geo.roll?.d" stroke="#fdba74" stroke-width="1.2" />
                    <circle :r="geo.core?.ry ?? 0" fill="#0f172a" />
                </g>
            </g>
            <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
                <text x="16" y="22" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 4) : 'N ?'"></text>
                <text x="384" y="22" text-anchor="end" fill="#a5b4fc" x-text="kind === 'foil' ? 'bakır folyo' : 'dikdörtgen makara'"></text>
            </g>
        </svg>
        @include('livewire.tools.coils.partials.stage-footer')
    </x-calc.stage>
</div>