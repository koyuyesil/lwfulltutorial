{{--
    Hesaplayıcı değer girişi: SI öneklerini (4k7, 100n) kabul eder; geçersiz girişte kırmızı çerçeve.
    Sayısal yukarı / aşağı: − / + düğmeleri (basılı tutunca hızlanır), klavyede ↑ / ↓ (Shift: 10 kat).
    Adım: değerin basamağına göre (4k7 → 100, 20m → 1m) ya da step ile sabit. min varsayılanı 0.
--}}
@props(['label', 'model', 'unit' => null, 'hint' => null, 'placeholder' => null, 'si' => true, 'step' => null, 'min' => 0, 'max' => null, 'stepper' => true])
@php
    $opts = \Illuminate\Support\Js::from([
        'si' => (bool) $si,
        'step' => $step === null ? null : (float) $step,
        'min' => $min === null || $min === '' ? null : (float) $min,
        'max' => $max === null || $max === '' ? null : (float) $max,
    ]);
    $btn = 'grid w-10 shrink-0 select-none place-items-center text-lg font-bold text-slate-500 transition [touch-action:manipulation] hover:bg-slate-100 hover:text-slate-900 active:bg-indigo-100 active:text-indigo-700 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white dark:active:bg-indigo-500/20';
@endphp

<label {{ $attributes->class('block min-w-0') }}>
    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}</span>
    <span class="relative mt-1.5 flex rounded-lg shadow-sm">
        <span class="relative block min-w-0 flex-1">
            <input type="text" inputmode="decimal" autocomplete="off" spellcheck="false" x-model="{{ $model }}"
                @if ($stepper)
                    @keydown.arrow-up.prevent="{{ $model }} = $stepValue({{ $model }}, $event.shiftKey ? 10 : 1, {{ $opts }})"
                    @keydown.arrow-down.prevent="{{ $model }} = $stepValue({{ $model }}, $event.shiftKey ? -10 : -1, {{ $opts }})"
                    aria-description="Yukarı / aşağı ok tuşlarıyla değiştirin, Shift ile 10 kat"
                @endif
                @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                @if ($si) :class="String({{ $model }}).trim() !== '' && ! Number.isFinite($parse({{ $model }})) ? 'ring-rose-400 focus:ring-rose-500' : 'ring-slate-300 focus:ring-indigo-600 dark:ring-white/10'" @endif
                @class(['block w-full border-0 bg-white py-2.5 pl-3 font-mono text-base text-slate-900 ring-1 ring-inset placeholder:text-slate-400 focus:z-10 focus:ring-2 focus:ring-inset dark:bg-slate-950 dark:text-white',
                    'rounded-lg' => ! $stepper, 'rounded-l-lg' => $stepper,
                    'pr-14' => $unit, 'pr-3' => ! $unit, 'ring-slate-300 focus:ring-indigo-600 dark:ring-white/10' => ! $si])>
            @if ($unit)
                <span class="pointer-events-none absolute right-3 top-1/2 z-10 -translate-y-1/2 text-sm font-semibold text-slate-400">{{ $unit }}</span>
            @endif
        </span>
        @if ($stepper)
            <span class="flex overflow-hidden rounded-r-lg bg-white ring-1 ring-inset ring-slate-300 dark:bg-slate-950 dark:ring-white/10" role="group" aria-label="{{ $label }} değerini değiştir">
                <button type="button" tabindex="-1" aria-label="Azalt" class="{{ $btn }} border-r border-slate-200 dark:border-white/10"
                    @pointerdown.prevent="$hold((m) => {{ $model }} = $stepValue({{ $model }}, -m, {{ $opts }}))" @contextmenu.prevent>−</button>
                <button type="button" tabindex="-1" aria-label="Arttır" class="{{ $btn }}"
                    @pointerdown.prevent="$hold((m) => {{ $model }} = $stepValue({{ $model }}, m, {{ $opts }}))" @contextmenu.prevent>+</button>
            </span>
        @endif
    </span>
    @if ($hint)
        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</span>
    @endif
</label>
