<div x-data="coreCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5" role="radiogroup" aria-label="Çekirdek türü">
                @foreach (['EE' => 'E + E', 'EI' => 'E + I', 'pot' => 'Pot core', 'U' => 'U + U'] as $k => $l)
                    <button type="button" role="radio" @click="type = '{{ $k }}'" :aria-checked="type === '{{ $k }}' ? 'true' : 'false'"
                        :class="type === '{{ $k }}' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">{{ $l }}</button>
                @endforeach
            </div>
            @include('livewire.tools.coils.partials.mode')
        </div>

        <template x-if="type !== 'pot'">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-calc.input label="A · toplam genişlik" model="A" unit="mm" />
                <x-calc.input label="B · yarı yüksekliği" model="B" unit="mm" />
                <x-calc.input label="C · derinlik" model="C" unit="mm" />
                <x-calc.input label="D · pencere yüksekliği" model="D" unit="mm" hint="Bir yarıdaki" />
                <x-calc.input label="E · pencereler arası" model="E" unit="mm" hint="Dış bacakların iç yüzleri" />
                <template x-if="type !== 'U'"><x-calc.input label="F · orta ayak genişliği" model="F" unit="mm" /></template>
            </div>
        </template>
        <template x-if="type === 'pot'">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-calc.input label="D1 · dış çap" model="D1" unit="mm" />
                <x-calc.input label="D2 · iç duvar çapı" model="D2" unit="mm" />
                <x-calc.input label="D3 · orta ayak çapı" model="D3" unit="mm" />
                <x-calc.input label="D4 · orta delik çapı" model="D4" unit="mm" />
                <x-calc.input label="h1 · çift yüksekliği" model="h1" unit="mm" />
                <x-calc.input label="h2 · pencere yüksekliği" model="h2" unit="mm" hint="Çiftin toplam penceresi" />
                <x-calc.input label="b · tel yuvası genişliği" model="bslot" unit="mm" />
            </div>
        </template>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-calc.input label="Malzeme geçirgenliği (µi)" model="mu" :si="false" step="10" min="1" max="100000" hint="N87 ≈ 2200, N97 ≈ 2300, 3C90 ≈ 2300" />
            <x-calc.input label="Hava boşluğu (toplam)" model="g" unit="mm" hint="Orta ayaktaki boşluk; yoksa 0" />
            <template x-if="mode === 'L'"><x-calc.input label="Sarım sayısı (N)" model="n" :si="false" step="1" min="1" max="10000" /></template>
            <template x-if="mode === 'N'"><x-calc.input label="Hedef endüktans" model="target" unit="H" /></template>
            <x-calc.input label="Paralel kondansatör (isteğe bağlı)" model="c" unit="F" />
        </div>

        <p x-show="! base" x-cloak class="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 ring-1 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/30">Ölçüler tutarsız (ör. iç ölçü dıştan büyük). Datasheet'teki değerleri kontrol edin.</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-calc.result label="Endüktans" value="$si(L, 'H')" accent />
            <x-calc.result label="Sarım sayısı" value="Number.isFinite(N) ? $num(N, 4) + (mode === 'N' ? ' → ' + Math.ceil(N) : '') : '—'" />
            <x-calc.result label="AL değeri" value="base ? $num(base.AL, 4) + ' nH/N²' : '—'" />
            <x-calc.result label="Etkin geçirgenlik (µe)" value="base ? $num(base.mue, 4) : '—'" />
            <x-calc.result label="le · Ae" value="base ? $num(base.le, 3) + ' mm · ' + $num(base.Ae, 3) + ' mm²' : '—'" />
            <x-calc.result label="Rezonans (LC)" value="$si(f0, 'Hz')" x-show="Number.isFinite(f0)" />
        </div>

        <x-calc.formula>C1 = Σ l/A, C2 = Σ l/A² → le = C1²/C2, Ae = C1/C2 &nbsp;·&nbsp; µe = µi / (1 + g·µi/le) &nbsp;·&nbsp; L = µ0·µe·N² / C1 (IEC 60205)</x-calc.formula>
    </div>

    <x-calc.stage label="3D model" badge="manyetik devre · hava boşluğu">
        <div class="grid place-items-center py-2 [perspective:1000px]">
            <div class="w-full transition-transform" :style="swingStyle">
                <svg viewBox="0 0 400 250" class="w-full" fill="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="core-fe" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#64748b" /><stop offset=".5" stop-color="#334155" /><stop offset="1" stop-color="#1e293b" /></linearGradient>
                        <pattern id="core-wind" width="6" height="5" patternUnits="userSpaceOnUse"><rect width="6" height="5" fill="#9a3412" /><rect width="6" height="3.2" fill="#f59e0b" /><rect width="6" height="1" fill="#fde68a" /></pattern>
                    </defs>
                    {{-- Arka yüz (derinlik), ön yüz --}}
                    @foreach ([0, 1] as $h)
                        <path :d="geo?.back?.[{{ $h }}]" fill="#0f172a" stroke="#475569" stroke-opacity=".5" />
                    @endforeach
                    @foreach ([0, 1] as $h)
                        <path :d="geo?.shapes?.[{{ $h }}]" fill="url(#core-fe)" stroke="#94a3b8" stroke-opacity=".5" />
                    @endforeach
                    <rect x-show="geo?.holeRect" :x="geo?.holeRect?.x" :y="geo?.holeRect?.y" :width="geo?.holeRect?.w" :height="geo?.holeRect?.h" fill="#020617" opacity=".85" />
                    {{-- Sarım (pencerelerde) --}}
                    @foreach ([0, 1] as $w)
                        <rect x-show="geo?.windows?.[{{ $w }}]" :x="geo?.windows?.[{{ $w }}]?.x" :y="geo?.windows?.[{{ $w }}]?.y" :width="geo?.windows?.[{{ $w }}]?.w" :height="geo?.windows?.[{{ $w }}]?.h" rx="3" fill="url(#core-wind)" opacity=".9" />
                    @endforeach
                    {{-- Hava boşluğu --}}
                    <rect x-show="geo?.gapY" :x="geo?.gapY?.x - 4" :y="geo?.gapY?.y" :width="(geo?.gapY?.w ?? 0) + 8" :height="geo?.gapY?.h" fill="#f43f5e" opacity=".85" />
                    {{-- Manyetik devre halkaları --}}
                    <g :opacity="density.opacity">
                        @foreach (range(0, 9) as $i)
                            <g x-show="geo?.loops?.[{{ $i }}]">
                                <path :d="geo?.loops?.[{{ $i }}]?.d" stroke="#7dd3fc" stroke-width="1.5" stroke-linejoin="round" class="calc-flow"
                                    :style="{ animationDuration: density.speed + 's', animationPlayState: flowState }" />
                                @foreach ([0, 1] as $k)
                                    <path :d="geo?.loops?.[{{ $i }}] ? arrow(geo.loops[{{ $i }}].arrows[{{ $k }}].x, geo.loops[{{ $i }}].arrows[{{ $k }}].y, geo.loops[{{ $i }}].arrows[{{ $k }}].a, 4) : ''" fill="#e0f2fe" />
                                @endforeach
                            </g>
                        @endforeach
                    </g>
                    <g font-family="ui-monospace,monospace" font-size="11" font-weight="700">
                        <text x="16" y="18" fill="#fde68a" x-text="Number.isFinite(N) ? 'N = ' + $num(N, 3) : 'N ?'"></text>
                        <text x="384" y="18" text-anchor="end" fill="#a5b4fc" x-text="base ? 'µe ' + $num(base.mue, 4) : ''"></text>
                        <text x="200" y="246" text-anchor="middle" fill="#fda4af" x-show="$parse(g) > 0" x-text="'hava boşluğu ' + $num($parse(g), 3) + ' mm'"></text>
                    </g>
                </svg>
            </div>
        </div>
        @include('livewire.tools.coils.partials.stage-footer', ['note' => '<span x-text="type === \'pot\' ? \'Pot core kesit görünümü\' : \'Ön görünüm\'"></span>'])
    </x-calc.stage>
</div>
