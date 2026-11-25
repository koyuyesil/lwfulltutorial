{{--
    Board ID devresinin canlı PCB görünümü (2D / gerçek CSS 3D).
    Katmanlar: taban SVG (kart, yollar, pedler, yazılar) + 3D kutular (dirençler, entegre) + havada ölçüm etiketi.
    source: { type, vref, r1, r2, name } döndüren Alpine ifadesi (fonksiyon).
    compact: araç çubuğu olmadan küçük önizleme.
--}}
@props(['source', 'compact' => false])
@php
    $id = 'pcb'.\Illuminate\Support\Str::random(6);
    $mono = 'font-family="ui-monospace, SFMono-Regular, Menlo, monospace"';
    $tin = 'linear-gradient(180deg,#f8fafc 0%,#94a3b8 55%,#e2e8f0 100%)';
@endphp

<div x-data="boardPcb({{ $source }})" {{ $attributes->class('select-none') }}>
    @unless ($compact)
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div class="inline-flex rounded-lg bg-slate-100 p-0.5 dark:bg-white/5" role="group" aria-label="Görünüm">
                <button type="button" @click="setMode('2d')" :class="! is3d ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1 text-xs font-semibold">2D</button>
                <button type="button" @click="setMode('3d')" :class="is3d ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1 text-xs font-semibold">3D</button>
            </div>
            <p x-show="is3d" x-cloak class="text-[11px] text-slate-500">Sürükleyerek döndürün · çift tık: sıfırla</p>
        </div>
    @endunless

    <div class="overflow-hidden rounded-xl bg-[radial-gradient(circle_at_50%_30%,#1e293b,#020617)]"
        :class="is3d ? 'cursor-grab active:cursor-grabbing' : ''"
        @pointerdown="start($event)" @pointermove="move($event)" @pointerup="end()" @pointerleave="end()" @dblclick="is3d && setMode('3d')">
        <div x-ref="wrap" class="relative w-full transition-[height] duration-500" :style="wrapHeight">
            <div class="absolute transition-all duration-500" :style="stageStyle">
                <div class="relative transition-transform duration-500 ease-out" :style="sceneStyle"
                    role="img" :aria-label="`Board ID devresi: R1 ${label(r1)}, R2 ${label(r2)}, çıkış ${volts(vout)}`">

                    {{-- Kart kalınlığı (3D'de görünür) --}}
                    <div class="absolute" style="left:18px; top:306px; width:484px; height:10px; background:#03361f; transform-origin:top; transform:rotateX(-90deg)"></div>
                    <div class="absolute" style="left:510px; top:18px; width:10px; height:280px; background:#022a18; transform-origin:left; transform:rotateY(90deg)"></div>

                    {{-- Taban katman --}}
                    <svg viewBox="0 0 520 320" width="520" height="320" class="absolute inset-0 overflow-visible">
                        <defs>
                            <linearGradient id="{{ $id }}-mask" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0" stop-color="#11834b" /><stop offset="1" stop-color="#0a5531" />
                            </linearGradient>
                            <linearGradient id="{{ $id }}-tin" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#f1f5f9" /><stop offset=".5" stop-color="#94a3b8" /><stop offset="1" stop-color="#e2e8f0" />
                            </linearGradient>
                            <linearGradient id="{{ $id }}-gold" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#fde68a" /><stop offset="1" stop-color="#b7791f" />
                            </linearGradient>
                            <pattern id="{{ $id }}-dots" width="18" height="18" patternUnits="userSpaceOnUse">
                                <circle cx="9" cy="9" r="1" fill="#ffffff" opacity=".07" />
                            </pattern>
                        </defs>

                        <rect x="10" y="10" width="500" height="296" rx="14" fill="url(#{{ $id }}-mask)" stroke="#0b3d24" stroke-width="2" />
                        <rect x="10" y="10" width="500" height="296" rx="14" fill="url(#{{ $id }}-dots)" />
                        @foreach ([[34, 34], [486, 34], [34, 282], [486, 282]] as [$hx, $hy])
                            <circle cx="{{ $hx }}" cy="{{ $hy }}" r="9" fill="url(#{{ $id }}-gold)" /><circle cx="{{ $hx }}" cy="{{ $hy }}" r="5" fill="#020617" />
                        @endforeach

                        {{-- Serigrafi --}}
                        <text x="56" y="38" fill="#e2e8f0" {!! $mono !!} font-size="12" font-weight="700" letter-spacing="1" x-text="'BOARD ID · ' + (p.name || 'KART')"></text>
                        <text x="56" y="296" fill="#e2e8f0" opacity=".55" {!! $mono !!} font-size="9">KoyuMavi · ID NETWORK</text>

                        {{-- Bakır yollar --}}
                        <g fill="none" stroke="#5be39a" stroke-opacity=".55" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
                            <path x-show="! internal" d="M84 72 H200 V84" />
                            <path x-show="internal" d="M84 72 H395 V80" />
                            <path x-show="! internal" d="M200 128 V146" />
                            <path d="M200 146 H322" />
                            <path d="M200 146 V172" />
                            <path d="M200 216 V262" />
                        </g>

                        {{-- VREF / GND --}}
                        <circle cx="78" cy="72" r="11" fill="url(#{{ $id }}-gold)" /><circle cx="78" cy="72" r="4.5" fill="#020617" />
                        <text x="78" y="100" text-anchor="middle" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="700">VREF</text>
                        <text x="78" y="112" text-anchor="middle" fill="#fde68a" {!! $mono !!} font-size="10" x-text="volts(vref)"></text>
                        <circle cx="200" cy="268" r="11" fill="url(#{{ $id }}-gold)" /><circle cx="200" cy="268" r="4.5" fill="#020617" />
                        <text x="222" y="272" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="700">GND</text>

                        {{-- Direnç pedleri, NC çerçevesi, etiketler --}}
                        @foreach (['r1' => 106, 'r2' => 194] as $key => $cy)
                            <g @if ($key === 'r1') x-show="! internal" @endif>
                                <rect x="185" y="{{ $cy - 25 }}" width="30" height="11" rx="2" :fill="{{ $key }} === null ? 'url(#{{ $id }}-gold)' : 'url(#{{ $id }}-tin)'" />
                                <rect x="185" y="{{ $cy + 14 }}" width="30" height="11" rx="2" :fill="{{ $key }} === null ? 'url(#{{ $id }}-gold)' : 'url(#{{ $id }}-tin)'" />
                                <g x-show="{{ $key }} === null">
                                    <rect x="181" y="{{ $cy - 29 }}" width="38" height="58" rx="3" fill="none" stroke="#e2e8f0" stroke-opacity=".7" stroke-dasharray="4 3" />
                                    <text x="200" y="{{ $cy + 4 }}" text-anchor="middle" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="700">NC</text>
                                </g>
                                <text x="160" y="{{ $cy + 4 }}" text-anchor="end" fill="#f8fafc" {!! $mono !!} font-size="12" font-weight="700">{{ strtoupper($key) }}</text>
                                <text x="160" y="{{ $cy + 17 }}" text-anchor="end" fill="#a7f3d0" {!! $mono !!} font-size="9" x-text="label({{ $key }})"></text>
                            </g>
                        @endforeach

                        {{-- Entegre bacakları (kartta) --}}
                        @foreach (range(0, 7) as $i)
                            <rect x="318" y="{{ 94 + $i * 16 }}" width="12" height="7" rx="1" fill="{{ $i === 3 ? '#fbbf24' : '#cbd5e1' }}" />
                            <rect x="470" y="{{ 94 + $i * 16 }}" width="12" height="7" rx="1" fill="#cbd5e1" />
                            <rect x="{{ 346 + $i * 15 }}" y="76" width="7" height="12" rx="1" fill="{{ $i === 3 ? '#fbbf24' : '#cbd5e1' }}" />
                            <rect x="{{ 346 + $i * 15 }}" y="224" width="7" height="12" rx="1" fill="#cbd5e1" />
                        @endforeach

                        {{-- Ölçüm noktası --}}
                        <circle cx="200" cy="146" r="6" fill="#f59e0b" stroke="#fff7ed" stroke-width="2" />

                        {{-- Lojik seviye --}}
                        <g x-show="p.type === 'logic'">
                            <rect x="330" y="252" width="140" height="26" rx="13" :fill="level === 1 ? '#16a34a' : (level === 0 ? '#475569' : '#b45309')" />
                            <text x="400" y="269" text-anchor="middle" fill="#fff" {!! $mono !!} font-size="11" font-weight="700"
                                x-text="level === 1 ? 'GPIO = 1 · HIGH' : (level === 0 ? 'GPIO = 0 · LOW' : 'GPIO belirsiz')"></text>
                        </g>
                    </svg>

                    {{-- 3D dirençler (0402) --}}
                    @foreach (['r1' => 106, 'r2' => 194] as $key => $cy)
                        <x-pcb-box x-show="{{ $key }} !== null{{ $key === 'r1' ? ' && ! internal' : '' }}"
                            :x="188" :y="$cy - 17" :w="24" :h="34" depth="h(10)"
                            :top="'linear-gradient(180deg,#e2e8f0 0 17%,#111827 17% 83%,#e2e8f0 83%)'"
                            :side="'linear-gradient(180deg,#94a3b8 0 17%,#0b0f17 17% 83%,#94a3b8 83%)'"
                            :side2="'linear-gradient(180deg,#64748b 0 17%,#000 17% 83%,#64748b 83%)'"
                            :ns="'#cbd5e1'" :ns2="'#64748b'">
                            <div class="flex h-full items-center justify-center">
                                <span class="-rotate-90 font-mono text-[10px] font-bold tracking-wide text-white" x-text="code({{ $key }})"></span>
                            </div>
                        </x-pcb-box>
                    @endforeach

                    {{-- 3D entegre (QFN) --}}
                    <x-pcb-box :x="326" :y="84" :w="148" :h="144" depth="h(16)"
                        :top="'linear-gradient(135deg,#2a2a2e,#09090b)'" side="#1c1c20" side2="#050505" :radius="4">
                        <svg viewBox="326 84 148 144" width="148" height="144" class="absolute inset-0">
                            <circle cx="340" cy="98" r="3.5" fill="#52525b" />
                            <text x="400" y="214" text-anchor="middle" fill="#e4e4e7" font-family="ui-sans-serif, system-ui" font-size="18" font-weight="800" letter-spacing="2" x-text="meta.chip"></text>
                            <text x="334" y="139" fill="#fbbf24" {!! $mono !!} font-size="9" font-weight="700" x-text="meta.pin"></text>
                            <g x-show="internal">
                                <text x="404" y="100" fill="#a1a1aa" {!! $mono !!} font-size="8">VDD_IO</text>
                                <rect x="372" y="106" width="88" height="84" rx="6" fill="#0b1220" stroke="#7dd3fc" stroke-dasharray="4 3" />
                                <path d="M395 84 V106 l-7 4 l14 6 l-14 6 l14 6 l-14 6 l7 4 V146 H326" fill="none" stroke="#e2e8f0" stroke-width="2" stroke-linejoin="round" />
                                <circle cx="395" cy="146" r="2.5" fill="#e2e8f0" />
                                <text x="418" y="126" fill="#f8fafc" {!! $mono !!} font-size="11" font-weight="700">R1</text>
                                <text x="418" y="139" fill="#a7f3d0" {!! $mono !!} font-size="9" x-text="label(r1)"></text>
                                <text x="416" y="182" text-anchor="middle" fill="#7dd3fc" {!! $mono !!} font-size="8" font-weight="700">DAHİLİ PULL-UP</text>
                            </g>
                        </svg>
                    </x-pcb-box>

                    {{-- Ölçüm etiketi: kart yüzeyinde (serigrafi gibi) --}}
                    <div class="absolute flex items-center justify-center rounded-md border border-amber-400 bg-slate-950/85 font-mono text-xs font-bold text-amber-200 transition-transform duration-500"
                        style="left:222px; top:114px; width:88px; height:24px;"
                        :style="{ transform: `translateZ(${h(0.5)}px)` }" x-text="volts(vout)"></div>
                </div>
            </div>
        </div>
    </div>

    @unless ($compact)
        <p x-show="invalid" class="mt-2 text-xs text-rose-600">Direnç değerlerinden biri okunamadı (örn. 100k, 4k7, 22k1 yazın).</p>
    @endunless
</div>
