{{-- Hesaplayıcı değer girişi: SI öneklerini (4k7, 100n…) kabul eder; geçersiz girişte kırmızı çerçeve. --}}
@props(['label', 'model', 'unit' => null, 'hint' => null, 'placeholder' => null, 'si' => true])

<label {{ $attributes->class('block min-w-0') }}>
    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}</span>
    <span class="relative mt-1.5 block">
        <input type="text" inputmode="decimal" autocomplete="off" spellcheck="false" x-model="{{ $model }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($si) :class="String({{ $model }}).trim() !== '' && ! Number.isFinite($parse({{ $model }})) ? 'ring-rose-400 focus:ring-rose-500' : 'ring-slate-300 focus:ring-indigo-600 dark:ring-white/10'" @endif
            @class(['block w-full rounded-lg border-0 bg-white py-2.5 pl-3 font-mono text-base text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset dark:bg-slate-950 dark:text-white', 'pr-14' => $unit, 'pr-3' => ! $unit, 'ring-slate-300 focus:ring-indigo-600 dark:ring-white/10' => ! $si])>
        @if ($unit)
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">{{ $unit }}</span>
        @endif
    </span>
    @if ($hint)
        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</span>
    @endif
</label>
