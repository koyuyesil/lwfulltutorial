{{-- Hesaplayıcı görsel paneli: koyu "laboratuvar" zemini, ızgara deseni, isteğe bağlı başlık ve sağ üst rozet. --}}
@props(['label' => null, 'badge' => null])

<div {{ $attributes->class('relative isolate flex min-w-0 flex-col overflow-hidden rounded-2xl bg-[radial-gradient(120%_90%_at_20%_0%,#1e293b_0%,#0b1220_55%,#020617_100%)] p-4 text-slate-200 shadow-inner ring-1 ring-white/10 sm:p-5') }}>
    <div class="pointer-events-none absolute inset-0 -z-10 opacity-[.08] [background-image:linear-gradient(#94a3b8_1px,transparent_1px),linear-gradient(90deg,#94a3b8_1px,transparent_1px)] [background-size:22px_22px]"></div>
    <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"></div>
    @if ($label || $badge)
        <div class="mb-3 flex items-center justify-between gap-2">
            @if ($label)
                <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[.2em] text-slate-400">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>{{ $label }}
                </p>
            @endif
            @if ($badge)
                <span class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] font-semibold text-slate-400 ring-1 ring-white/10">{{ $badge }}</span>
            @endif
        </div>
    @endif
    <div class="flex min-w-0 flex-1 flex-col justify-center">{{ $slot }}</div>
</div>
