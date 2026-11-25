@php
    // Şema sembolleri: eleman (0,0) merkezli, yatay, ~44 px
    $mono = 'font-family="ui-monospace,monospace"';
@endphp
<div x-data="networkCalc" class="grid gap-6 xl:grid-cols-2">
    <div class="min-w-0 space-y-5">
        <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
            @foreach (['R' => 'Direnç', 'C' => 'Kondansatör', 'L' => 'Bobin'] as $k => $label)
                <button type="button" @click="kind = '{{ $k }}'" :class="kind === '{{ $k }}' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'" class="rounded-md px-3 py-1.5 text-sm font-semibold">{{ $label }}</button>
            @endforeach
        </div>

        <label class="block">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Değerler</span>
            <textarea x-model="values" rows="3" spellcheck="false"
                class="mt-1.5 block w-full rounded-lg border-0 bg-white px-3 py-2.5 font-mono text-base text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10"></textarea>
            <span class="mt-1 block text-xs" :class="invalid ? 'text-rose-600' : 'text-slate-500'"
                x-text="invalid ? 'Bazı değerler okunamadı; boşluk ya da noktalı virgülle ayırın.' : 'Boşluk, &quot;; &quot; ya da &quot;, &quot; ile ayırın. Örn: 1k 2k2 4k7'"></span>
        </label>

        <template x-if="result">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.result label="Seri bağlı" value="$si(result.series, unit)" accent />
                <x-calc.result label="Paralel bağlı" value="$si(result.parallel, unit)" accent />
            </div>
        </template>

        <x-calc.formula><span x-text="kind === 'C' ? 'Paralel: C = C1 + C2 + …  ·  Seri: 1/C = 1/C1 + 1/C2 + …' : 'Seri: X = X1 + X2 + …  ·  Paralel: 1/X = 1/X1 + 1/X2 + …'"></span></x-calc.formula>
    </div>

    <x-calc.stage label="Şema" badge="ilk 6 eleman">
        <svg viewBox="0 0 400 270" class="w-full" fill="none" aria-hidden="true">
            {{-- SERİ --}}
            <text x="20" y="22" fill="#94a3b8" {!! $mono !!} font-size="10" font-weight="700" letter-spacing="2">SERİ</text>
            <text x="380" y="22" text-anchor="end" fill="#a5b4fc" {!! $mono !!} font-size="12" font-weight="800" x-text="result ? '= ' + $si(result.series, unit, 3) : ''"></text>
            <line x1="20" y1="70" x2="380" y2="70" stroke="#64748b" stroke-width="3" />
            <circle cx="20" cy="70" r="5" fill="#f8fafc" /><circle cx="380" cy="70" r="5" fill="#f8fafc" />
            <text x="20" y="96" text-anchor="middle" fill="#64748b" {!! $mono !!} font-size="10">A</text>
            <text x="380" y="96" text-anchor="middle" fill="#64748b" {!! $mono !!} font-size="10">B</text>

            {{-- PARALEL --}}
            <text x="20" y="132" fill="#94a3b8" {!! $mono !!} font-size="10" font-weight="700" letter-spacing="2">PARALEL</text>
            <text x="380" y="132" text-anchor="end" fill="#a5b4fc" {!! $mono !!} font-size="12" font-weight="800" x-text="result ? '= ' + $si(result.parallel, unit, 3) : ''"></text>
            <line x1="20" y1="160" x2="380" y2="160" stroke="#64748b" stroke-width="3" />
            <line x1="20" y1="240" x2="380" y2="240" stroke="#64748b" stroke-width="3" />
            <circle cx="20" cy="160" r="5" fill="#f8fafc" /><circle cx="20" cy="240" r="5" fill="#f8fafc" />

            @foreach (range(0, 5) as $i)
                {{-- seri eleman --}}
                <g x-show="shown.length > {{ $i }}" :transform="`translate(${slot({{ $i }})} 70)`">
                    <g x-show="kind === 'R'"><rect x="-20" y="-9" width="40" height="18" rx="3" fill="#0b1220" stroke="#38bdf8" stroke-width="2.5" /></g>
                    <g x-show="kind === 'C'"><rect x="-7" y="-16" width="14" height="32" fill="#0b1220" /><line x1="-5" y1="-16" x2="-5" y2="16" stroke="#fbbf24" stroke-width="3" /><line x1="5" y1="-16" x2="5" y2="16" stroke="#fbbf24" stroke-width="3" /></g>
                    <g x-show="kind === 'L'"><rect x="-22" y="-12" width="44" height="14" fill="#0b1220" /><path d="M-20 0 a5 6 0 0 1 10 0 a5 6 0 0 1 10 0 a5 6 0 0 1 10 0 a5 6 0 0 1 10 0" stroke="#c084fc" stroke-width="2.5" /></g>
                    <text y="-16" text-anchor="middle" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="700" x-text="shown.length > {{ $i }} ? $si(shown[{{ $i }}], unit, 3) : ''"></text>
                </g>
                {{-- paralel kol --}}
                <g x-show="shown.length > {{ $i }}" :transform="`translate(${slot({{ $i }})} 200)`">
                    <line x1="0" y1="-40" x2="0" y2="40" stroke="#64748b" stroke-width="3" />
                    <circle cy="-40" r="3.5" fill="#94a3b8" /><circle cy="40" r="3.5" fill="#94a3b8" />
                    <g transform="rotate(90)">
                        <g x-show="kind === 'R'"><rect x="-20" y="-9" width="40" height="18" rx="3" fill="#0b1220" stroke="#38bdf8" stroke-width="2.5" /></g>
                        <g x-show="kind === 'C'"><rect x="-7" y="-16" width="14" height="32" fill="#0b1220" /><line x1="-5" y1="-16" x2="-5" y2="16" stroke="#fbbf24" stroke-width="3" /><line x1="5" y1="-16" x2="5" y2="16" stroke="#fbbf24" stroke-width="3" /></g>
                        <g x-show="kind === 'L'"><rect x="-22" y="-12" width="44" height="14" fill="#0b1220" /><path d="M-20 0 a5 6 0 0 1 10 0 a5 6 0 0 1 10 0 a5 6 0 0 1 10 0 a5 6 0 0 1 10 0" stroke="#c084fc" stroke-width="2.5" /></g>
                    </g>
                    <text x="16" y="4" fill="#e2e8f0" {!! $mono !!} font-size="10" font-weight="700" x-text="shown.length > {{ $i }} ? $si(shown[{{ $i }}], unit, 3) : ''"></text>
                </g>
            @endforeach
            <text x="200" y="264" text-anchor="middle" fill="#94a3b8" {!! $mono !!} font-size="10" x-show="extra > 0" x-text="'+' + extra + ' eleman daha (hesaba dahil)'"></text>
        </svg>
    </x-calc.stage>
</div>
