{{--
    Board ID devresinin canlı PCB görünümü (2D / gerçek CSS 3D).
    Katmanlar: taban SVG (kart, yollar, pedler, yazılar) + 3D kutular (dirençler, entegre) + kart yüzeyinde ölçüm etiketi.
    Yerleşim simetriktir: ölçüm hattı (y=160) kartın yatay eksenidir; direnç kolonu (x=150) ve entegre (merkez x=375) bu eksende ortalanır,
    R1 / R2 ölçüm noktasına eşit uzaklıktadır, VREF üstte, GND altta.
    source: { type, chip, vref, r1, r2, r1_org, r2_org, name } döndüren Alpine ifadesi (fonksiyon).
    compact: araç çubuğu olmadan küçük önizleme.
--}}
@props(['source', 'compact' => false])
@php
    $id = 'pcb'.\Illuminate\Support\Str::random(6);
    $mono = 'font-family="ui-monospace, SFMono-Regular, Menlo, monospace"';
    $cx = 150;      // direnç kolonu
    $axis = 160;    // ölçüm hattı
    $res = ['r1' => $axis - 50, 'r2' => $axis + 50];
    // Entegre: 140×140, merkez (375, 160); 7'şer bacak, ortadaki bacak eksende
    [$chipX, $chipY, $chipS] = [305, 90, 140];
    $mid = $chipX + $chipS / 2; // entegre merkezi
    $pins = range(0, 6);
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

                        {{-- Serigrafi: üst ve alt kenarda ortalı --}}
                        <text x="260" y="34" text-anchor="middle" fill="#e2e8f0" {!! $mono !!} font-size="12" font-weight="700" letter-spacing="1" x-text="'BOARD ID · ' + (p.name || 'KART').toUpperCase()"></text>
                        <text x="260" y="294" text-anchor="middle" fill="#e2e8f0" opacity=".5" {!! $mono !!} font-size="9" letter-spacing="1">KoyuMavi · ID NETWORK</text>

                        {{-- Bakır yollar --}}
                        <g fill="none" stroke="#5be39a" stroke-opacity=".55" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
                            <path x-show="! internal" d="M{{ $cx }} 54 V{{ $res['r1'] - 25 }}" />
                            <path x-show="internal" d="M{{ $cx }} 54 H{{ $mid }} V{{ $chipY - 12 }}" />
                            <path x-show="! internal" d="M{{ $cx }} {{ $res['r1'] + 25 }} V{{ $axis }}" />
                            <path d="M{{ $cx }} {{ $axis }} V{{ $res['r2'] - 25 }}" />
                            <path d="M{{ $cx }} {{ $axis }} H{{ $chipX - 12 }}" />
                            <path d="M{{ $cx }} {{ $res['r2'] + 25 }} V266" />
                        </g>

                        {{-- VREF (üst) / GND (alt) --}}
                        <circle cx="{{ $cx }}" cy="54" r="11" fill="url(#{{ $id }}-gold)" /><circle cx="{{ $cx }}" cy="54" r="4.5" fill="#020617" />
                        <text x="{{ $cx - 20 }}" y="52" text-anchor="end" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="700">VREF</text>
                        <text x="{{ $cx - 20 }}" y="64" text-anchor="end" fill="#fde68a" {!! $mono !!} font-size="10" x-text="volts(vref)"></text>
                        <circle cx="{{ $cx }}" cy="266" r="11" fill="url(#{{ $id }}-gold)" /><circle cx="{{ $cx }}" cy="266" r="4.5" fill="#020617" />
                        <text x="{{ $cx - 20 }}" y="270" text-anchor="end" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="700">GND</text>

                        {{-- Direnç pedleri, NC çerçevesi, etiketler (değer + orijinal değer) --}}
                        @foreach ($res as $key => $cy)
                            <g @if ($key === 'r1') x-show="! internal" @endif>
                                <rect x="{{ $cx - 15 }}" y="{{ $cy - 25 }}" width="30" height="11" rx="2" :fill="{{ $key }} === null ? 'url(#{{ $id }}-gold)' : 'url(#{{ $id }}-tin)'" />
                                <rect x="{{ $cx - 15 }}" y="{{ $cy + 14 }}" width="30" height="11" rx="2" :fill="{{ $key }} === null ? 'url(#{{ $id }}-gold)' : 'url(#{{ $id }}-tin)'" />
                                <g x-show="{{ $key }} === null">
                                    <rect x="{{ $cx - 19 }}" y="{{ $cy - 29 }}" width="38" height="58" rx="3" fill="none" stroke="#e2e8f0" stroke-opacity=".7" stroke-dasharray="4 3" />
                                    <text x="{{ $cx }}" y="{{ $cy + 4 }}" text-anchor="middle" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="700">NC</text>
                                </g>
                                <text x="{{ $cx - 30 }}" y="{{ $cy - 4 }}" text-anchor="end" fill="#f8fafc" {!! $mono !!} font-size="12" font-weight="700">{{ strtoupper($key) }}</text>
                                <text x="{{ $cx - 30 }}" y="{{ $cy + 9 }}" text-anchor="end" fill="#a7f3d0" {!! $mono !!} font-size="9" x-text="label({{ $key }})"></text>
                                <text x="{{ $cx - 30 }}" y="{{ $cy + 21 }}" text-anchor="end" fill="#fcd34d" {!! $mono !!} font-size="9" x-show="{{ $key }}Org !== null" x-text="orig({{ $key }}Org)"></text>
                            </g>
                        @endforeach

                        {{-- Entegre bacakları: dört kenarda 7'şer, ortadaki bacak eksende --}}
                        @foreach ($pins as $i)
                            @php $o = $chipY + 15.5 + $i * 17; $ox = $chipX + 15.5 + $i * 17; @endphp
                            <rect x="{{ $chipX - 12 }}" y="{{ $o }}" width="12" height="7" rx="1" fill="{{ $i === 3 ? '#fbbf24' : '#cbd5e1' }}" />
                            <rect x="{{ $chipX + $chipS }}" y="{{ $o }}" width="12" height="7" rx="1" fill="#cbd5e1" />
                            <rect x="{{ $ox }}" y="{{ $chipY - 12 }}" width="7" height="12" rx="1" :fill="internal && {{ $i }} === 3 ? '#fbbf24' : '#cbd5e1'" />
                            <rect x="{{ $ox }}" y="{{ $chipY + $chipS }}" width="7" height="12" rx="1" fill="#cbd5e1" />
                        @endforeach

                        {{-- Ölçüm noktası --}}
                        <circle cx="{{ $cx }}" cy="{{ $axis }}" r="6" fill="#f59e0b" stroke="#fff7ed" stroke-width="2" />

                        {{-- Lojik seviye (entegrenin altında, ortalı) --}}
                        <g x-show="p.type === 'logic'">
                            <rect x="{{ $mid - 70 }}" y="258" width="140" height="22" rx="11" :fill="level === 1 ? '#16a34a' : (level === 0 ? '#475569' : '#b45309')" />
                            <text x="{{ $mid }}" y="273" text-anchor="middle" fill="#fff" {!! $mono !!} font-size="10" font-weight="700"
                                x-text="level === 1 ? 'GPIO = 1 · HIGH' : (level === 0 ? 'GPIO = 0 · LOW' : 'GPIO belirsiz')"></text>
                        </g>
                    </svg>

                    {{-- 3D dirençler (0402) --}}
                    @foreach ($res as $key => $cy)
                        <x-pcb-box x-show="{{ $key }} !== null{{ $key === 'r1' ? ' && ! internal' : '' }}"
                            :x="$cx - 12" :y="$cy - 17" :w="24" :h="34" depth="h(10)"
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
                    <x-pcb-box :x="$chipX" :y="$chipY" :w="$chipS" :h="$chipS" depth="h(16)"
                        :top="'linear-gradient(135deg,#2a2a2e,#09090b)'" side="#1c1c20" side2="#050505" :radius="4">
                        <svg viewBox="{{ $chipX }} {{ $chipY }} {{ $chipS }} {{ $chipS }}" width="{{ $chipS }}" height="{{ $chipS }}" class="absolute inset-0">
                            <circle cx="{{ $chipX + 13 }}" cy="{{ $chipY + 13 }}" r="3.5" fill="#52525b" />
                            <text x="{{ $mid }}" y="{{ $chipY + $chipS - 12 }}" text-anchor="middle" fill="#e4e4e7" font-family="ui-sans-serif, system-ui" font-size="17" font-weight="800" letter-spacing="2" x-text="meta.chip"></text>
                            <text x="{{ $chipX + 6 }}" y="{{ $axis - 6 }}" fill="#fbbf24" {!! $mono !!} font-size="9" font-weight="700" x-text="meta.pin"></text>
                            <g x-show="internal">
                                <text x="{{ $mid + 8 }}" y="{{ $chipY + 10 }}" fill="#a1a1aa" {!! $mono !!} font-size="7">VDD_IO</text>
                                <rect x="{{ $mid - 20 }}" y="{{ $chipY + 14 }}" width="82" height="80" rx="6" fill="#0b1220" stroke="#7dd3fc" stroke-dasharray="4 3" />
                                <path d="M{{ $mid }} {{ $chipY }} V{{ $chipY + 24 }} l-7 4 l14 6 l-14 6 l14 6 l-14 6 l7 4 V{{ $axis }} H{{ $chipX }}" fill="none" stroke="#e2e8f0" stroke-width="2" stroke-linejoin="round" />
                                <circle cx="{{ $mid }}" cy="{{ $axis }}" r="2.5" fill="#e2e8f0" />
                                <text x="{{ $mid + 14 }}" y="{{ $chipY + 36 }}" fill="#f8fafc" {!! $mono !!} font-size="11" font-weight="700">R1</text>
                                <text x="{{ $mid + 14 }}" y="{{ $chipY + 48 }}" fill="#a7f3d0" {!! $mono !!} font-size="8" x-text="r1 === null ? 'bilinmiyor' : label(r1)"></text>
                                <text x="{{ $mid + 14 }}" y="{{ $chipY + 59 }}" fill="#fcd34d" {!! $mono !!} font-size="8" x-show="r1Org !== null" x-text="orig(r1Org)"></text>
                                <text x="{{ $mid + 21 }}" y="{{ $chipY + 88 }}" text-anchor="middle" fill="#7dd3fc" {!! $mono !!} font-size="7" font-weight="700">DAHİLİ PULL-UP</text>
                            </g>
                        </svg>
                    </x-pcb-box>

                    {{-- Ölçüm etiketi: kart yüzeyinde, ölçüm hattının üstünde ortalı --}}
                    <div class="absolute flex items-center justify-center rounded-md border border-amber-400 bg-slate-950/85 font-mono text-xs font-bold text-amber-200 transition-transform duration-500"
                        style="left:{{ ($cx + $chipX - 12) / 2 - 44 }}px; top:{{ $axis - 32 }}px; width:88px; height:24px;"
                        :style="{ transform: `translateZ(${h(0.5)}px)` }" x-text="volts(vout)"></div>
                </div>
            </div>
        </div>
    </div>

    @unless ($compact)
        <p x-show="invalid" class="mt-2 text-xs text-rose-600">Direnç değerlerinden biri okunamadı (örn. 100k, 4k7, 22k1 yazın).</p>
    @endunless
</div>
