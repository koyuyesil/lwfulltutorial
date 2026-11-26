{{-- Hesap yönü: sarımdan endüktans (L) ya da hedef endüktanstan sarım sayısı (N) --}}
<div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5" role="radiogroup" aria-label="Hesap yönü">
    @foreach (['L' => 'Endüktansı bul', 'N' => 'Sarım sayısını bul'] as $m => $l)
        <button type="button" role="radio" @click="mode = '{{ $m }}'" :aria-checked="mode === '{{ $m }}' ? 'true' : 'false'"
            :class="mode === '{{ $m }}' ? 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500'"
            class="rounded-md px-3 py-1.5 text-sm font-semibold">{{ $l }}</button>
    @endforeach
</div>
