@use('App\Enums\TicketStatus')
@php
    $phone = preg_replace('/[^\d+]/', '', (string) $client->phone);
@endphp

<section @class(['rounded-2xl bg-white shadow-sm ring-1 transition dark:bg-slate-800',
    'ring-2 ring-emerald-400 shadow-emerald-500/10' => $suggest,
    'ring-slate-200 dark:ring-white/10' => ! $suggest])
    x-data="{ msg: $wire.entangle('message') }">
    <header class="flex flex-wrap items-center gap-3 px-5 py-4">
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
        </span>
        <div class="min-w-0 flex-1">
            <h3 class="font-bold text-slate-900 dark:text-white">Müşteriyi bilgilendir</h3>
            <p class="text-xs text-slate-500">
                @if ($suggest) <strong class="text-emerald-600">Durum değişti — müşteriye haber vermek ister misiniz?</strong>
                @elseif ($lastNotified) Son bildirim {{ $lastNotified->created_at->locale('tr')->diffForHumans() }}.
                @else Hazır şablon takip bağlantısıyla doldurulur; düzenleyip tek tıkla gönderin.
                @endif
            </p>
        </div>
        <select wire:model.live="template" class="rounded-lg border-0 bg-slate-100 py-1.5 pl-3 pr-8 text-xs font-semibold dark:bg-white/5 dark:text-white">
            @foreach (TicketStatus::cases() as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}{{ $s === $ticket->status ? ' (güncel)' : '' }}</option>
            @endforeach
        </select>
        @if ($suggest) <button type="button" wire:click="dismiss" class="text-xs text-slate-400 hover:text-slate-600" aria-label="Öneriyi kapat">✕</button> @endif
    </header>
    <div class="space-y-3 border-t border-slate-100 p-5 dark:border-white/5">
        <textarea x-model="msg" rows="4" class="block w-full rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm text-slate-800 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-500 dark:bg-white/5 dark:text-slate-100 dark:ring-white/10"></textarea>
        <div class="flex flex-wrap items-center gap-2">
            @if ($wa)
                <a :href="'https://wa.me/{{ $wa }}?text=' + encodeURIComponent(msg)" target="_blank" rel="noopener" @click="$wire.logSent('whatsapp')"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#25D366] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:brightness-95">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.1-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1-.3-.1-1.2-.5-2.3-1.4-.9-.8-1.4-1.7-1.6-2-.2-.3 0-.5.1-.6l.4-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.7-.7 1.9-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 21.8a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.8 9.8 0 1 1 12 21.8M12 0a12 12 0 0 0-10.3 18L0 24l6.2-1.6A12 12 0 1 0 12 0"/></svg>
                    WhatsApp
                </a>
            @endif
            @if ($phone)
                <a :href="'sms:{{ $phone }}?body=' + encodeURIComponent(msg)" @click="$wire.logSent('sms')"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/15 dark:hover:bg-white/5">SMS</a>
            @endif
            @if ($client->email)
                <a :href="'mailto:{{ $client->email }}?subject=' + encodeURIComponent(@js($ticket->tracking_code.' · Servis bilgilendirme')) + '&body=' + encodeURIComponent(msg)" @click="$wire.logSent('email')"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/15 dark:hover:bg-white/5">E-posta</a>
            @endif
            <button type="button" @click="copyText(msg, 'Mesaj kopyalandı.').then((ok) => ok && $wire.logSent('copy'))"
                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/15 dark:hover:bg-white/5">Kopyala</button>
            <a href="{{ route('settings', 'bildirimler') }}" wire:navigate class="ms-auto text-xs font-semibold text-slate-500 hover:text-indigo-600">Şablonları düzenle →</a>
        </div>
        @unless ($wa || $phone || $client->email)
            <p class="text-xs text-amber-600">Müşterinin telefon ya da e-postası kayıtlı değil.</p>
        @endunless
    </div>
</section>
