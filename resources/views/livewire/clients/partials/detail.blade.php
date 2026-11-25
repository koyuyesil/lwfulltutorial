@php
    $allTickets = $client->clientsProducts->flatMap->tickets->sortByDesc('id');
    $openCount = $allTickets->reject(fn ($t) => $t->status->isClosed())->count();
@endphp

<div class="flex-1 overflow-y-auto">
    {{-- Kimlik --}}
    <div class="px-6 py-6">
        <div class="flex items-center gap-4">
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-full text-lg font-bold text-white {{ $avatarClass }}">{{ $client->initials }}</span>
            <div class="min-w-0">
                <p class="truncate text-xl font-bold text-slate-900 dark:text-white">{{ $client->full_name }}</p>
                <p class="truncate text-sm text-slate-500">{{ $client->company ?: 'Bireysel müşteri' }} · {{ $client->created_at?->locale('tr')->translatedFormat('d F Y') }} tarihinden beri</p>
            </div>
        </div>

        {{-- Hızlı iletişim --}}
        <div class="mt-5 grid grid-cols-3 gap-2">
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $client->phone) }}" class="flex flex-col items-center gap-1 rounded-xl bg-slate-50 py-3 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 dark:bg-white/5 dark:text-slate-200 dark:ring-white/10">
                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                Ara
            </a>
            @if ($wa = $client->whatsappNumber())
                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-1 rounded-xl bg-slate-50 py-3 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 dark:bg-white/5 dark:text-slate-200 dark:ring-white/10">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
                    WhatsApp
                </a>
            @else
                <span class="flex flex-col items-center gap-1 rounded-xl bg-slate-50 py-3 text-xs font-semibold text-slate-300 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">WhatsApp</span>
            @endif
            @if ($client->email)
                <a href="mailto:{{ $client->email }}" class="flex flex-col items-center gap-1 rounded-xl bg-slate-50 py-3 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100 dark:bg-white/5 dark:text-slate-200 dark:ring-white/10">
                    <svg class="h-5 w-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                    E-posta
                </a>
            @else
                <span class="flex flex-col items-center gap-1 rounded-xl bg-slate-50 py-3 text-xs font-semibold text-slate-300 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">E-posta</span>
            @endif
        </div>

        <dl class="mt-5 grid grid-cols-[90px_1fr] gap-x-3 gap-y-2 text-sm">
            <dt class="text-slate-500">Telefon</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $client->phone }}</dd>
            <dt class="text-slate-500">E-posta</dt><dd class="text-slate-700 dark:text-slate-200">{{ $client->email ?: '—' }}</dd>
            <dt class="text-slate-500">Adres</dt><dd class="text-slate-700 dark:text-slate-200">{{ $client->address ?: '—' }}</dd>
        </dl>

        <div class="mt-5 grid grid-cols-3 divide-x divide-slate-200 rounded-xl bg-slate-50 py-3 text-center ring-1 ring-slate-200 dark:divide-white/10 dark:bg-white/5 dark:ring-white/10">
            <div><p class="text-xl font-black">{{ $client->clientsProducts->count() }}</p><p class="text-xs text-slate-500">Cihaz</p></div>
            <div><p class="text-xl font-black">{{ $allTickets->count() }}</p><p class="text-xs text-slate-500">Servis</p></div>
            <div><p @class(['text-xl font-black', 'text-amber-600' => $openCount])>{{ $openCount }}</p><p class="text-xs text-slate-500">Açık iş</p></div>
        </div>
    </div>

    {{-- Cihazlar --}}
    <section class="border-t border-slate-200 px-6 py-5 dark:border-white/10">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Cihazlar</h3>
        <ul class="mt-3 space-y-2">
            @forelse ($client->clientsProducts as $device)
                <li class="flex items-center gap-3 rounded-lg p-3 ring-1 ring-slate-200 dark:ring-white/10">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500 dark:bg-white/10">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $device->product?->brand }} {{ $device->product?->model_name }}</p>
                        <p class="truncate font-mono text-xs text-slate-500">
                            {{ $device->imei ? 'IMEI ' . $device->imei : '' }}{{ $device->imei && $device->serial ? ' · ' : '' }}{{ $device->serial ? 'SN ' . $device->serial : '' }}
                            {{ ! $device->imei && ! $device->serial ? 'IMEI / seri no yok' : '' }}
                        </p>
                    </div>
                    <span class="text-xs text-slate-500">{{ $device->tickets->count() }} servis</span>
                </li>
            @empty
                <li class="text-sm text-slate-500">Kayıtlı cihaz yok.</li>
            @endforelse
        </ul>
    </section>

    {{-- Servis geçmişi --}}
    <section class="border-t border-slate-200 px-6 py-5 dark:border-white/10">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Servis geçmişi</h3>
        <ul class="mt-3 divide-y divide-slate-100 dark:divide-white/5">
            @forelse ($allTickets as $ticket)
                <li>
                    <a href="{{ route('tickets.show', $ticket) }}" wire:navigate class="-mx-2 flex items-center gap-3 rounded-lg px-2 py-2.5 hover:bg-slate-50 dark:hover:bg-white/5">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $ticket->problem }}</p>
                            <p class="font-mono text-xs text-slate-500">{{ $ticket->tracking_code }} · {{ $ticket->created_at->format('d.m.Y') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $ticket->status->badge() }}">{{ $ticket->status->label() }}</span>
                    </a>
                </li>
            @empty
                <li class="py-2 text-sm text-slate-500">Henüz servis kaydı yok.</li>
            @endforelse
        </ul>
    </section>
</div>

<footer class="flex items-center gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-slate-950/50">
    <button type="button" x-on:click="$dispatch('close-drawer', { name: 'client-detail' }); $dispatch('open-drawer', { name: 'client-form' }); Livewire.dispatch('edit-client', { id: {{ $client->id }} })"
        class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-white dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5">Düzenle</button>
    <a href="{{ route('tickets.index', ['yeni' => 1, 'musteri' => $client->id]) }}" wire:navigate
        class="ms-auto inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Servis kaydı aç</a>
</footer>
