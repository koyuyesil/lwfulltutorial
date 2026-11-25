@use('App\Enums\TicketStatus')
@php
    $slaBadge = [
        'overdue' => ['Gecikti', 'bg-red-600 text-white animate-pulse'],
        'warning' => ['Riskli', 'bg-amber-500 text-white'],
        'ok' => ['Zamanında', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200'],
    ];
@endphp

<div class="space-y-3">
    {{-- Durum sayaçları / filtreler --}}
    <div class="flex flex-wrap gap-2 text-xs">
        @php
            $openTotal = collect(TicketStatus::openValues())->sum(fn ($s) => $countsByStatus[$s] ?? 0);
            $chip = 'inline-flex items-center gap-1 rounded-full px-3 py-1 font-semibold ring-1 transition';
        @endphp
        <button type="button" wire:click="$set('statusFilter', 'open')"
            @class([$chip, 'bg-indigo-600 text-white ring-indigo-700' => $statusFilter === 'open', 'bg-white text-gray-700 ring-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600' => $statusFilter !== 'open'])>
            Açık işler <span class="opacity-70">{{ $openTotal }}</span>
        </button>
        @foreach (TicketStatus::cases() as $case)
            <button type="button" wire:click="$set('statusFilter', '{{ $case->value }}')"
                @class([$chip, $case->badge(), 'ring-2 ring-offset-1 ring-gray-900 dark:ring-white' => $statusFilter === $case->value])>
                {{ $case->label() }} <span class="opacity-70">{{ $countsByStatus[$case->value] ?? 0 }}</span>
            </button>
        @endforeach
        <button type="button" wire:click="$set('statusFilter', '')"
            @class([$chip, 'bg-indigo-600 text-white ring-indigo-700' => $statusFilter === '', 'bg-white text-gray-700 ring-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600' => $statusFilter !== ''])>
            Tümü
        </button>
    </div>

    {{-- SLA uyarı şeridi --}}
    <div class="flex flex-wrap gap-2 text-xs">
        <button type="button" wire:click="$set('slaFilter', '{{ $slaFilter === 'overdue' ? '' : 'overdue' }}')"
            @class(['inline-flex items-center gap-2 rounded-lg px-3 py-1.5 font-semibold ring-1 transition',
                'bg-red-600 text-white ring-red-700' => $slaFilter === 'overdue',
                'bg-red-50 text-red-700 ring-red-200 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-200 dark:ring-red-800' => $slaFilter !== 'overdue'])>
            ⏰ Geciken: {{ $overdueCount }}
        </button>
        <button type="button" wire:click="$set('slaFilter', '{{ $slaFilter === 'at_risk' ? '' : 'at_risk' }}')"
            @class(['inline-flex items-center gap-2 rounded-lg px-3 py-1.5 font-semibold ring-1 transition',
                'bg-amber-500 text-white ring-amber-600' => $slaFilter === 'at_risk',
                'bg-amber-50 text-amber-800 ring-amber-200 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-200 dark:ring-amber-800' => $slaFilter !== 'at_risk'])>
            ⚠️ Riskli: {{ $atRiskCount }}
        </button>
    </div>

    @forelse ($tickets as $ticket)
        @php
            $client = $ticket->clientProduct->client;
            $device = $ticket->clientProduct;
            $sla = $ticket->slaState();
        @endphp
        <div wire:key="ticket-{{ $ticket->id }}" @class([
            'rounded-lg bg-white p-3 shadow-md dark:bg-gray-800 border-l-4',
            'border-red-500' => $sla === 'overdue',
            'border-amber-400' => $sla === 'warning',
            'border-emerald-400' => $sla === 'ok',
            'border-gray-300 dark:border-gray-600' => in_array($sla, ['closed', 'none']),
        ])>
            {{-- Başlık satırı --}}
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <a href="{{ route('tickets.show', $ticket) }}" wire:navigate
                    class="text-sm font-bold text-gray-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-300">
                    #{{ $ticket->id }}
                </a>
                <span class="rounded-full px-2 py-0.5 font-semibold ring-1 {{ $ticket->status->badge() }}">{{ $ticket->status->label() }}</span>
                <span class="rounded-full px-2 py-0.5 font-semibold {{ $ticket->priority->badge() }}">{{ $ticket->priority->label() }}</span>
                @isset($slaBadge[$sla])
                    <span class="rounded-full px-2 py-0.5 font-semibold {{ $slaBadge[$sla][1] }}"
                        title="Hedef: {{ $ticket->due_at->format('d.m.Y H:i') }}">
                        {{ $slaBadge[$sla][0] }} · {{ $ticket->due_at->locale('tr')->diffForHumans(['parts' => 2, 'short' => true]) }}
                    </span>
                @endisset
                @if ($sla === 'closed' && ! is_null($ticket->metSla()))
                    <span @class(['rounded-full px-2 py-0.5 font-semibold',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' => $ticket->metSla(),
                        'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' => ! $ticket->metSla()])>
                        {{ $ticket->metSla() ? 'Hedefte kapandı' : 'Hedef aşıldı' }}
                    </span>
                @endif
                <span class="ms-auto text-gray-500 dark:text-gray-400">Açılış {{ $ticket->created_at->format('d.m.Y H:i') }}</span>
            </div>

            {{-- Müşteri + cihaz --}}
            <div class="mt-2 grid gap-1 text-xs text-gray-600 dark:text-gray-300 sm:grid-cols-2">
                <div>
                    <strong class="text-gray-800 dark:text-gray-100">{{ $client->company ?: $client->first_name . ' ' . $client->last_name }}</strong>
                    · {{ $client->phone }} @if ($client->email) · {{ $client->email }} @endif
                </div>
                <div>
                    <strong class="text-gray-800 dark:text-gray-100">{{ $device->product->brand }} {{ $device->product->model_name }}</strong>
                    @if ($device->imei) · IMEI {{ $device->imei }} @endif
                    @if ($device->serial) · SN {{ $device->serial }} @endif
                    @if ($device->color) · {{ $device->color }} @endif
                </div>
            </div>
            <p class="mt-1 text-sm text-gray-800 dark:text-gray-100">{{ $ticket->problem }}</p>

            @error('status.' . $ticket->id)
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

            {{-- İşlemler --}}
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap gap-1">
                    @forelse ($ticket->status->allowedTransitions() as $next)
                        <button type="button" wire:click="changeStatus({{ $ticket->id }}, '{{ $next->value }}')"
                            wire:loading.attr="disabled"
                            class="rounded-md px-3 py-1 text-xs font-semibold ring-1 transition hover:brightness-95 {{ $next->badge() }}">
                            → {{ $next->label() }}
                        </button>
                    @empty
                        <span class="text-xs text-gray-500">İş kapandı.</span>
                    @endforelse
                </div>
                <div class="flex flex-wrap gap-1 text-xs">
                    <a href="{{ route('tickets.show', $ticket) }}" wire:navigate
                        class="rounded-md border border-indigo-600 px-3 py-1 font-medium text-indigo-700 hover:bg-indigo-600 hover:text-white dark:border-indigo-400 dark:text-indigo-300">
                        Detay & geçmiş
                    </a>
                    <button type="button" wire:click="$dispatch('edit', {id: {{ $ticket->id }}})"
                        class="rounded-md border border-teal-700 px-3 py-1 font-medium text-teal-700 hover:bg-teal-700 hover:text-white dark:border-teal-500 dark:text-teal-400">
                        Düzenle
                    </button>
                    @php
                        $printable = array_merge($ticket->toArray(), [
                            'status' => $ticket->status->label(),
                            'priority' => $ticket->priority->label(),
                        ]);
                    @endphp
                    <button type="button" x-on:click="window.downloadTicketPdf(@js($printable))"
                        class="rounded-md border border-gray-400 px-3 py-1 font-medium text-gray-700 hover:bg-gray-700 hover:text-white dark:text-gray-300">
                        PDF
                    </button>
                    <button type="button" x-on:click="window.printTicket(@js($printable))"
                        class="rounded-md border border-gray-400 px-3 py-1 font-medium text-gray-700 hover:bg-gray-700 hover:text-white dark:text-gray-300">
                        Yazdır
                    </button>
                    <button type="button" wire:click="delete({{ $ticket->id }})"
                        wire:confirm="Bu servis kaydı silinsin mi?"
                        class="rounded-md border border-red-600 px-3 py-1 font-medium text-red-700 hover:bg-red-600 hover:text-white dark:text-red-400">
                        Sil
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
            Bu filtreye uyan servis kaydı yok.
        </div>
    @endforelse

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
