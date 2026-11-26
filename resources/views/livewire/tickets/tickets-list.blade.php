@use('App\Enums\TicketStatus')
@php
    $slaBadge = [
        'overdue' => ['Gecikti', 'bg-red-600 text-white'],
        'warning' => ['Riskli', 'bg-amber-400 text-amber-950'],
        'ok' => ['Zamanında', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'],
    ];
    $openTotal = collect(TicketStatus::openValues())->sum(fn ($s) => $countsByStatus[$s] ?? 0);
    $chip = 'inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition';
    $chipOff = 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/5';
    $chipOn = 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
    $isFiltered = $search !== '' || $slaFilter !== '' || $statusFilter !== 'open';
@endphp

<div class="mt-6 space-y-4">
    {{-- Araç çubuğu --}}
    <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
        <x-ui.search wire:model.live.debounce.300ms="search" placeholder="Takip kodu, müşteri, telefon, IMEI, model…" />

        <div class="flex flex-wrap gap-1.5">
            <button type="button" wire:click="$set('slaFilter', '{{ $slaFilter === 'overdue' ? '' : 'overdue' }}')"
                @class([$chip, 'bg-red-600 text-white' => $slaFilter === 'overdue', 'bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30' => $slaFilter !== 'overdue'])>
                <span class="h-1.5 w-1.5 rounded-full bg-current"></span> Geciken {{ $overdueCount }}
            </button>
            <button type="button" wire:click="$set('slaFilter', '{{ $slaFilter === 'at_risk' ? '' : 'at_risk' }}')"
                @class([$chip, 'bg-amber-400 text-amber-950' => $slaFilter === 'at_risk', 'bg-amber-50 text-amber-800 ring-1 ring-amber-200 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30' => $slaFilter !== 'at_risk'])>
                <span class="h-1.5 w-1.5 rounded-full bg-current"></span> Riskli {{ $atRiskCount }}
            </button>
        </div>

        <div class="ms-auto flex items-center gap-2">
            @if ($isFiltered)
                <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Temizle</button>
            @endif
            <div class="flex rounded-lg bg-slate-100 p-0.5 dark:bg-white/5" role="group" aria-label="Görünüm">
                @foreach (['table' => ['M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5', 'Kompakt liste'], 'cards' => ['M3.75 6A2.25 2.25 0 0 1 6 3.75h12A2.25 2.25 0 0 1 20.25 6v3A2.25 2.25 0 0 1 18 11.25H6A2.25 2.25 0 0 1 3.75 9V6Zm0 9A2.25 2.25 0 0 1 6 12.75h12A2.25 2.25 0 0 1 20.25 15v3A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18v-3Z', 'Detaylı kartlar']] as $mode => [$icon, $label])
                    <button type="button" wire:click="$set('view', '{{ $mode }}')" title="{{ $label }}"
                        @class(['rounded-md p-1.5 transition', 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' => $view === $mode, 'text-slate-400 hover:text-slate-600' => $view !== $mode])>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Durum sekmeleri --}}
    <x-scroll-row track="gap-1.5 py-0.5" label="Durum filtresi">
        <button type="button" wire:click="$set('statusFilter', 'open')" aria-pressed="{{ $statusFilter === 'open' ? 'true' : 'false' }}" @class([$chip, 'shrink-0 whitespace-nowrap', $statusFilter === 'open' ? $chipOn : $chipOff])>
            Açık işler <span class="opacity-60">{{ $openTotal }}</span>
        </button>
        @foreach (TicketStatus::cases() as $case)
            <button type="button" wire:click="$set('statusFilter', '{{ $case->value }}')" aria-pressed="{{ $statusFilter === $case->value ? 'true' : 'false' }}" @class([$chip, 'shrink-0 whitespace-nowrap', $statusFilter === $case->value ? $chipOn : $chipOff])>
                <span class="h-2 w-2 rounded-full {{ $case->bar() }}"></span>
                {{ $case->label() }} <span class="opacity-60">{{ $countsByStatus[$case->value] ?? 0 }}</span>
            </button>
        @endforeach
        <button type="button" wire:click="$set('statusFilter', '')" aria-pressed="{{ $statusFilter === '' ? 'true' : 'false' }}" @class([$chip, 'shrink-0 whitespace-nowrap', $statusFilter === '' ? $chipOn : $chipOff])>Tümü</button>
    </x-scroll-row>

    @if ($searchingAll)
        <p class="text-xs text-slate-500">"{{ $search }}" için kapanmış kayıtlar dahil tüm servis geçmişinde arandı.</p>
    @endif

    <div wire:loading.class="opacity-60" wire:target="search,statusFilter,slaFilter,view,changeStatus,gotoPage,nextPage,previousPage">
        @if ($tickets->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-white/10 dark:bg-slate-800/50">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                @if ($isFiltered)
                    <p class="mt-3 font-semibold text-slate-800 dark:text-white">Bu filtreye uyan servis kaydı yok</p>
                    <button type="button" wire:click="clearFilters" class="mt-2 text-sm font-semibold text-indigo-600">Filtreleri temizle</button>
                @else
                    <p class="mt-3 font-semibold text-slate-800 dark:text-white">Açık servis kaydınız yok 🎉</p>
                    <p class="mt-1 text-sm text-slate-500">Yeni bir cihaz kabul ettiğinizde buradan takip edin.</p>
                    <button type="button" x-on:click="$dispatch('open-drawer', { name: 'ticket-form' }); Livewire.dispatch('create-ticket')"
                        class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Yeni servis kaydı</button>
                @endif
            </div>
        @elseif ($view === 'table')
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10 [&_thead_th:first-child]:rounded-tl-xl [&_thead_th:last-child]:rounded-tr-xl">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-white/5 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Kayıt</th>
                            <th class="hidden px-4 py-3 md:table-cell">Müşteri / cihaz</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Arıza</th>
                            <th class="px-4 py-3">Durum</th>
                            <th class="hidden px-4 py-3 sm:table-cell">Hedef</th>
                            <th class="px-4 py-3"><span class="sr-only">İşlemler</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($tickets as $ticket)
                            @php
                                $client = $ticket->clientProduct->client;
                                $product = $ticket->clientProduct->product;
                                $sla = $ticket->slaState();
                                $next = $ticket->status->allowedTransitions();
                            @endphp
                            <tr wire:key="t-row-{{ $ticket->id }}" class="group hover:bg-slate-50 dark:hover:bg-white/5">
                                <td class="relative px-4 py-3">
                                    <span @class(['absolute inset-y-0 left-0 w-1',
                                        'bg-red-500' => $sla === 'overdue', 'bg-amber-400' => $sla === 'warning', 'bg-emerald-400' => $sla === 'ok', 'bg-transparent' => in_array($sla, ['closed', 'none'])])></span>
                                    <x-copy-code :code="$ticket->tracking_code" :href="route('tickets.show', $ticket)" class="text-sm" />
                                    <p class="text-xs text-slate-500">{{ $ticket->created_at->format('d.m.Y H:i') }}</p>
                                    <p class="text-xs text-slate-600 md:hidden dark:text-slate-300">{{ $client->full_name }} · {{ $product->display_name }}</p>
                                </td>
                                <td class="hidden max-w-56 px-4 py-3 md:table-cell">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $client->full_name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $product->display_name }}{{ $ticket->clientProduct->imei ? ' · ' . $ticket->clientProduct->imei : '' }}</p>
                                </td>
                                <td class="hidden max-w-64 px-4 py-3 xl:table-cell">
                                    <p class="line-clamp-2 text-slate-700 dark:text-slate-200" title="{{ $ticket->problem }}">{{ $ticket->problem }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $ticket->status->badge() }}">{{ $ticket->status->label() }}</span>
                                    @if ($ticket->priority === \App\Enums\PriorityType::HIGH)
                                        <span class="ms-1 rounded-full px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $ticket->priority->badge() }}">Acil</span>
                                    @endif
                                </td>
                                <td class="hidden whitespace-nowrap px-4 py-3 sm:table-cell">
                                    @isset($slaBadge[$sla])
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $slaBadge[$sla][1] }}" title="{{ $ticket->due_at->format('d.m.Y H:i') }}">
                                            {{ $ticket->due_at->locale('tr')->diffForHumans(['short' => true]) }}
                                        </span>
                                    @elseif ($sla === 'closed')
                                        <span @class(['text-xs font-semibold', 'text-emerald-600' => $ticket->metSla(), 'text-rose-600' => $ticket->metSla() === false, 'text-slate-400' => is_null($ticket->metSla())])>
                                            {{ $ticket->metSla() ? '✓ Hedefte' : ($ticket->metSla() === false ? '✗ Aşıldı' : '—') }}
                                        </span>
                                    @endisset
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    @include('livewire.tickets.partials.row-actions', ['ticket' => $ticket, 'next' => $next])
                                </td>
                            </tr>
                            @error('status.' . $ticket->id)
                                <tr><td colspan="6" class="bg-rose-50 px-4 py-2 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">{{ $message }}</td></tr>
                            @enderror
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="grid gap-3 lg:grid-cols-2">
                @foreach ($tickets as $ticket)
                    @php
                        $client = $ticket->clientProduct->client;
                        $device = $ticket->clientProduct;
                        $sla = $ticket->slaState();
                    @endphp
                    <article wire:key="t-card-{{ $ticket->id }}" @class([
                        'flex flex-col rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10 border-l-4',
                        'border-l-red-500' => $sla === 'overdue', 'border-l-amber-400' => $sla === 'warning', 'border-l-emerald-400' => $sla === 'ok',
                        'border-l-slate-200 dark:border-l-white/10' => in_array($sla, ['closed', 'none']),
                    ])>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-copy-code :code="$ticket->tracking_code" :href="route('tickets.show', $ticket)" />
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $ticket->status->badge() }}">{{ $ticket->status->label() }}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $ticket->priority->badge() }}">{{ $ticket->priority->label() }}</span>
                            @isset($slaBadge[$sla])
                                <span class="ms-auto rounded-full px-2 py-0.5 text-xs font-semibold {{ $slaBadge[$sla][1] }}">{{ $slaBadge[$sla][0] }} · {{ $ticket->due_at->locale('tr')->diffForHumans(['short' => true]) }}</span>
                            @endisset
                        </div>
                        <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                            <div class="min-w-0">
                                <p class="text-xs text-slate-500">Müşteri</p>
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $client->full_name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $client->phone }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-slate-500">Cihaz</p>
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $device->product->display_name }}</p>
                                <p class="truncate font-mono text-xs text-slate-500">{{ $device->imei ?: ($device->serial ?: '—') }}</p>
                            </div>
                        </div>
                        <p class="mt-3 line-clamp-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:bg-white/5 dark:text-slate-200">{{ $ticket->problem }}</p>
                        @error('status.' . $ticket->id) <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                        <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3 dark:border-white/5">
                            <span class="text-xs text-slate-500">{{ $ticket->created_at->locale('tr')->diffForHumans() }} açıldı</span>
                            @include('livewire.tickets.partials.row-actions', ['ticket' => $ticket, 'next' => $ticket->status->allowedTransitions()])
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div>{{ $tickets->links() }}</div>
</div>
