{{-- Form alanı sarmalayıcısı: etiket + içerik + hata/ipucu --}}
@props(['label', 'for' => null, 'error' => null, 'hint' => null, 'optional' => false])

<div {{ $attributes->class('min-w-0') }}>
    <label @if ($for) for="{{ $for }}" @endif class="flex items-baseline justify-between text-sm font-medium text-slate-700 dark:text-slate-200">
        <span>{{ $label }}</span>
        @if ($optional)
            <span class="text-xs font-normal text-slate-400">opsiyonel</span>
        @endif
    </label>
    <div class="mt-1.5">
        {{ $slot }}
    </div>
    @if ($error && $errors->has($error))
        <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $errors->first($error) }}</p>
    @elseif ($hint)
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
