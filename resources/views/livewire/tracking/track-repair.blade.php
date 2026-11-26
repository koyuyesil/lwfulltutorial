@use('App\Enums\TicketStatus')
@php
    // Müşteriye gösterilen sadeleştirilmiş adımlar
    $publicSteps = [
        ['label' => 'Kabul', 'statuses' => [TicketStatus::RECEIVED]],
        ['label' => 'İnceleme', 'statuses' => [TicketStatus::DIAGNOSING, TicketStatus::AWAITING_APPROVAL]],
        ['label' => 'Onarım', 'statuses' => [TicketStatus::AWAITING_PARTS, TicketStatus::IN_REPAIR, TicketStatus::TESTING]],
        ['label' => 'Hazır', 'statuses' => [TicketStatus::READY]],
        ['label' => 'Teslim', 'statuses' => [TicketStatus::DELIVERED]],
    ];

    $customerMessage = fn (TicketStatus $s) => match ($s) {
        TicketStatus::RECEIVED => 'Cihazınız servisimize kabul edildi ve sıraya alındı.',
        TicketStatus::DIAGNOSING => 'Teknisyenimiz cihazınızı inceliyor.',
        TicketStatus::AWAITING_APPROVAL => 'İnceleme tamamlandı. Onarım için onayınızı bekliyoruz; servisimiz sizinle iletişime geçecek.',
        TicketStatus::AWAITING_PARTS => 'Onarım için gerekli parça tedarik ediliyor.',
        TicketStatus::IN_REPAIR => 'Cihazınız onarılıyor.',
        TicketStatus::TESTING => 'Onarım tamamlandı, son kalite testleri yapılıyor.',
        TicketStatus::READY => 'Cihazınız hazır! Servisimizden teslim alabilirsiniz.',
        TicketStatus::DELIVERED => 'Cihazınız teslim edildi. Bizi tercih ettiğiniz için teşekkürler.',
        TicketStatus::CANCELLED => 'Servis kaydı kapatıldı. Ayrıntı için servisimizle iletişime geçin.',
    };
@endphp

<div class="relative">
    <div class="absolute inset-x-0 top-0 -z-10 h-96 bg-[radial-gradient(60%_60%_at_50%_0%,rgba(16,185,129,0.15),transparent)]"></div>

    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
        <div class="text-center">
            <span class="inline-grid h-14 w-14 place-items-center rounded-2xl bg-linear-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
            </span>
            <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl dark:text-white">Arıza Takibi</h1>
            <p class="mt-3 text-slate-600 dark:text-slate-400">Servis fişinizdeki takip kodunu ve kayıtlı telefonunuzun son 4 hanesini girin.</p>
        </div>

        @unless ($ticket)
            <form wire:submit="track" class="mt-10 rounded-2xl bg-white p-6 shadow-xl shadow-slate-200/60 ring-1 ring-slate-200 dark:bg-slate-900 dark:shadow-none dark:ring-white/10">
                <div class="grid gap-4 sm:grid-cols-[1fr_180px]">
                    <div>
                        <label for="code" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Takip kodu</label>
                        <input id="code" wire:model="code" autocomplete="off" autocapitalize="characters" placeholder="KM-XXXXXXXX"
                            class="mt-1.5 w-full rounded-xl border-slate-300 px-4 py-3 font-mono uppercase tracking-wider focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/10 dark:bg-slate-950">
                    </div>
                    <div>
                        <label for="phoneLast4" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Telefon son 4 hane</label>
                        <input id="phoneLast4" wire:model="phoneLast4" inputmode="numeric" maxlength="4" placeholder="••••" autocomplete="off"
                            class="mt-1.5 w-full rounded-xl border-slate-300 px-4 py-3 text-center font-mono tracking-[0.5em] focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/10 dark:bg-slate-950">
                    </div>
                </div>
                @if ($errors->any())
                    <div class="mt-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif
                <button type="submit" wire:loading.attr="disabled"
                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="track">Durumu sorgula</span>
                    <span wire:loading wire:target="track">Sorgulanıyor…</span>
                </button>
                <p class="mt-4 text-center text-xs text-slate-500">Bilgileriniz yalnızca kaydı doğrulamak için kullanılır.</p>
            </form>
        @else
            @php
                $status = $ticket->status;
                $currentStep = collect($publicSteps)->search(fn ($s) => in_array($status, $s['statuses'], true));
                $device = $ticket->clientProduct;
            @endphp

            <div class="mt-10 overflow-hidden rounded-2xl bg-white shadow-xl shadow-slate-200/60 ring-1 ring-slate-200 dark:bg-slate-900 dark:shadow-none dark:ring-white/10">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4 dark:border-white/5">
                    <div>
                        <p class="text-xs text-slate-500">Takip kodu</p>
                        <p class="text-lg"><x-copy-code :code="$ticket->tracking_code" text-class="font-bold" /></p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-sm font-semibold ring-1 {{ $status->badge() }}">{{ $status->label() }}</span>
                </div>

                <div class="px-6 py-6">
                    @if ($status === TicketStatus::CANCELLED)
                        <p class="rounded-xl bg-rose-50 p-4 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">{{ $customerMessage($status) }}</p>
                    @else
                        {{-- İlerleme çubuğu --}}
                        <ol class="grid grid-cols-5 gap-2">
                            @foreach ($publicSteps as $i => $step)
                                <li class="text-center">
                                    <span @class([
                                        'block h-2 rounded-full transition',
                                        'bg-emerald-500' => $i < $currentStep || $status === TicketStatus::DELIVERED,
                                        'bg-emerald-500 animate-pulse' => $i === $currentStep && $status !== TicketStatus::DELIVERED,
                                        'bg-slate-200 dark:bg-white/10' => $i > $currentStep,
                                    ])></span>
                                    <span @class(['mt-2 block text-xs font-medium',
                                        'text-slate-900 dark:text-white' => $i <= $currentStep,
                                        'text-slate-400' => $i > $currentStep])>{{ $step['label'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <p class="mt-6 text-lg font-semibold text-slate-900 dark:text-white">
                            {{ $ticket->quote_status === 'pending' ? 'İnceleme tamamlandı. Aşağıdaki onarım teklifini onaylamanızı bekliyoruz.' : $customerMessage($status) }}
                        </p>
                    @endif

                    {{-- Onarım teklifi --}}
                    @if ($ticket->quote_status && $ticket->items->isNotEmpty())
                        @php $money = fn ($v) => \App\Models\Ticket::money((float) $v); @endphp
                        <div @class(['mt-6 overflow-hidden rounded-2xl ring-2',
                            'ring-amber-400' => $ticket->quote_status === 'pending',
                            'ring-emerald-400' => $ticket->quote_status === 'approved',
                            'ring-rose-300' => $ticket->quote_status === 'rejected'])>
                            <div @class(['flex items-center justify-between px-4 py-3',
                                'bg-amber-50 dark:bg-amber-500/10' => $ticket->quote_status === 'pending',
                                'bg-emerald-50 dark:bg-emerald-500/10' => $ticket->quote_status === 'approved',
                                'bg-rose-50 dark:bg-rose-500/10' => $ticket->quote_status === 'rejected'])>
                                <p class="font-bold text-slate-900 dark:text-white">Onarım teklifi</p>
                                <p class="text-sm font-semibold">
                                    @if ($ticket->quote_status === 'pending') <span class="text-amber-700 dark:text-amber-300">Onayınızı bekliyor</span>
                                    @elseif ($ticket->quote_status === 'approved') <span class="text-emerald-700 dark:text-emerald-300">✓ Onaylandı · {{ $ticket->quote_decided_at->format('d.m.Y H:i') }}</span>
                                    @else <span class="text-rose-700 dark:text-rose-300">Reddedildi</span>
                                    @endif
                                </p>
                            </div>
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                    @foreach ($ticket->items as $item)
                                        <tr>
                                            <td class="px-4 py-2 text-slate-700 dark:text-slate-200">{{ $item->name }} @if ($item->qty > 1)<span class="text-slate-400">× {{ $item->qty }}</span>@endif</td>
                                            <td class="px-4 py-2 text-right font-mono">{{ $money($item->total()) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-slate-50 dark:bg-white/5">
                                        <td class="px-4 py-3 font-bold">Toplam (KDV dahil)</td>
                                        <td class="px-4 py-3 text-right font-mono text-lg font-black">{{ $money($ticket->quote_total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @if ($ticket->quote_status === 'pending')
                                <div class="space-y-3 border-t border-slate-100 p-4 dark:border-white/5">
                                    <textarea wire:model="quoteNote" rows="2" placeholder="Servise notunuz (isteğe bağlı)…"
                                        class="block w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/10 dark:bg-slate-950"></textarea>
                                    @error('quote') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <button type="button" wire:click="decideQuote(true)" wire:confirm="{{ $money($ticket->quote_total) }} tutarındaki onarım teklifini onaylıyor musunuz?"
                                            class="rounded-xl bg-emerald-600 px-4 py-3 font-semibold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700">✓ Teklifi onayla, onarıma başlansın</button>
                                        <button type="button" wire:click="decideQuote(false)" wire:confirm="Teklifi reddetmek istediğinize emin misiniz? Cihazınız onarılmadan iade edilir."
                                            class="rounded-xl px-4 py-3 font-semibold text-rose-700 ring-1 ring-rose-200 hover:bg-rose-50 dark:text-rose-300 dark:ring-rose-500/30 dark:hover:bg-rose-500/10">Reddet</button>
                                    </div>
                                    <p class="text-center text-xs text-slate-500">Onayınız tarih ve saatiyle kayıt altına alınır.</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <dl class="mt-6 grid gap-4 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-3 dark:bg-white/5">
                        <div>
                            <dt class="text-slate-500">Cihaz</dt>
                            <dd class="font-semibold">{{ $device->product->brand }} {{ $device->product->model_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Kabul tarihi</dt>
                            <dd class="font-semibold">{{ $ticket->created_at->format('d.m.Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ $status->isClosed() ? 'Kapanış' : 'Tahmini teslim' }}</dt>
                            <dd class="font-semibold">
                                @if ($status->isClosed())
                                    {{ $ticket->closed_at?->format('d.m.Y') ?? '—' }}
                                @elseif ($ticket->due_at)
                                    {{ $ticket->due_at->locale('tr')->translatedFormat('d F, l') }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>

                    @if ($ticket->events->isNotEmpty())
                        <h2 class="mt-8 text-sm font-bold uppercase tracking-wider text-slate-500">Servis geçmişi</h2>
                        <ol class="mt-4 space-y-4 border-s-2 border-slate-100 ps-5 dark:border-white/10">
                            @foreach ($ticket->events as $event)
                                <li class="relative">
                                    <span class="absolute -start-[1.6rem] top-1.5 h-3 w-3 rounded-full bg-emerald-500 ring-4 ring-white dark:ring-slate-900"></span>
                                    <p class="text-xs text-slate-500">{{ $event->created_at->format('d.m.Y H:i') }}</p>
                                    <p class="font-medium text-slate-900 dark:text-white">
                                        @if ($event->type === 'created') Servis kaydı oluşturuldu
                                        @elseif ($event->to_status) {{ $event->to_status->label() }}
                                        @elseif ($event->type === 'quote') Onarım teklifi
                                        @else Servis notu
                                        @endif
                                    </p>
                                    @if ($event->note)
                                        <p class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $event->note }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 text-sm dark:border-white/5 dark:bg-white/5">
                    @php $company = \App\Support\Company::for($ticket->user); @endphp
                    <div class="flex min-w-0 items-center gap-3">
                        @if ($company['logo'])<img src="{{ $company['logo'] }}" alt="" class="h-9 w-9 shrink-0 rounded-lg bg-white object-contain p-0.5 ring-1 ring-slate-200 dark:ring-white/10">@endif
                        <div class="min-w-0 text-slate-600 dark:text-slate-300">
                            <p>Servis: <strong>{{ $company['name'] }}</strong></p>
                            <p class="truncate text-xs text-slate-500">
                                @if ($company['phone'])<a href="tel:{{ preg_replace('/[^\d+]/', '', $company['phone']) }}" class="font-semibold text-emerald-700 dark:text-emerald-400">{{ $company['phone'] }}</a>@endif
                                {{ \App\Support\Company::addressLine($company) ? ' · '.\App\Support\Company::addressLine($company) : '' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="resetSearch" class="font-semibold text-emerald-700 hover:text-emerald-900 dark:text-emerald-400">Başka kayıt sorgula</button>
                </div>
            </div>
        @endunless
    </div>
</div>
