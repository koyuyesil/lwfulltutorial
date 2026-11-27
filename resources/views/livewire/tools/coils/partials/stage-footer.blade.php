{{-- 3D panel alt satırı: not ve ortak Durdur / Döndür düğmesi (dönüş ve alan akışı birlikte durur) --}}
<div class="mt-1 flex items-center justify-between gap-2 text-[11px] text-slate-400">
    <span>{!! $note ?? '' !!}</span>
    <span class="flex items-center gap-3">
        <span class="hidden items-center gap-1.5 sm:inline-flex"><span class="h-0.5 w-4 rounded bg-sky-300"></span> alan · yoğunluk endüktansla artar</span>
        <button type="button" @click="spinning = ! spinning" class="rounded-md px-2 py-1 font-semibold text-slate-300 ring-1 ring-white/10 hover:bg-white/10" x-text="spinning ? '⏸ Durdur' : '▶ Oynat'"></button>
    </span>
</div>
