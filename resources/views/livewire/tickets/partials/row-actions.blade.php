@php
    // İş akışındaki "doğal" sonraki adım birincil buton olur; iptal gibi yan yollar menüde kalır
    $primary = collect($next)->first(fn ($s) => $s !== \App\Enums\TicketStatus::CANCELLED);
    $others = collect($next)->reject(fn ($s) => $s === $primary);
@endphp

<div class="relative inline-flex items-center gap-1" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
    @if ($primary)
        <button type="button" wire:click="changeStatus({{ $ticket->id }}, '{{ $primary->value }}')" wire:loading.attr="disabled"
            title="Durumu '{{ $primary->label() }}' yap"
            class="hidden rounded-lg px-2.5 py-1.5 text-xs font-semibold ring-1 transition hover:brightness-95 sm:inline-flex {{ $primary->badge() }}">
            → {{ $primary->label() }}
        </button>
    @endif
    <button type="button" @click="menu = ! menu" :aria-expanded="menu" aria-haspopup="menu" aria-label="Diğer işlemler"
        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
    </button>
    <div x-show="menu" x-cloak x-transition.origin.top.right role="menu"
        class="absolute right-0 top-full z-20 mt-1 w-56 overflow-hidden rounded-xl bg-white py-1 text-left text-sm shadow-xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-white/10">
        <a role="menuitem" href="{{ route('tickets.show', $ticket) }}" wire:navigate class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">Detay ve zaman çizelgesi</a>
        @if ($primary)
            <button type="button" role="menuitem" wire:click="changeStatus({{ $ticket->id }}, '{{ $primary->value }}')" @click="menu = false"
                class="block w-full px-4 py-2 text-left text-slate-700 hover:bg-slate-50 sm:hidden dark:text-slate-200 dark:hover:bg-white/5">→ {{ $primary->label() }}</button>
        @endif
        @foreach ($others as $s)
            <button type="button" role="menuitem" wire:click="changeStatus({{ $ticket->id }}, '{{ $s->value }}')" @click="menu = false"
                @class(['block w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-white/5', 'text-rose-600' => $s === \App\Enums\TicketStatus::CANCELLED, 'text-slate-700 dark:text-slate-200' => $s !== \App\Enums\TicketStatus::CANCELLED])>
                → {{ $s->label() }}
            </button>
        @endforeach
        <div class="my-1 border-t border-slate-100 dark:border-white/5"></div>
        <a role="menuitem" href="{{ route('tickets.print', [$ticket, 'a4']) }}" target="_blank" @click="menu = false" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">A4 kabul formu / PDF</a>
        <a role="menuitem" href="{{ route('tickets.print', [$ticket, 'a4-2']) }}" target="_blank" @click="menu = false" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">A4 · 2 nüsha (müşteri + işletme)</a>
        <a role="menuitem" href="{{ route('tickets.print', [$ticket, 'a5']) }}" target="_blank" @click="menu = false" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">A5 dikey form</a>
        <a role="menuitem" href="{{ route('tickets.print', [$ticket, '80']) }}" target="_blank" @click="menu = false" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">Termal fiş (80 mm)</a>
        <a role="menuitem" href="{{ route('tickets.print', [$ticket, 'label']) }}" target="_blank" @click="menu = false" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">QR cihaz etiketi</a>
        <button type="button" role="menuitem" @click="menu = false; copyText(@js(route('tracking', ['kod' => $ticket->tracking_code])), 'Takip bağlantısı kopyalandı.')"
            class="block w-full px-4 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">Takip bağlantısını kopyala</button>
        <div class="my-1 border-t border-slate-100 dark:border-white/5"></div>
        <button type="button" role="menuitem" wire:click="delete({{ $ticket->id }})" wire:confirm="{{ $ticket->tracking_code }} servis kaydı ve tüm geçmişi kalıcı olarak silinecek. Emin misiniz?"
            @click="menu = false" class="block w-full px-4 py-2 text-left text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">Sil</button>
    </div>
</div>
