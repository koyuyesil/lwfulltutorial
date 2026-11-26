@php
    $client = $ticket->clientProduct->client;
    $device = $ticket->clientProduct;
    $product = $device->product;
    $sla = $ticket->slaState();
    $visited = $ticket->events->pluck('to_status')->filter()->map->value->unique()->all();
    $human = fn (int|float $seconds) => \Carbon\CarbonInterval::seconds((int) $seconds)->cascade()
        ->locale('tr')->forHumans(['short' => true, 'parts' => 2]) ?: '0 sn';
    $card = 'rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10';
    $eventDot = ['status_changed' => 'bg-indigo-500', 'created' => 'bg-emerald-500', 'note' => 'bg-slate-400', 'quote' => 'bg-amber-500', 'notified' => 'bg-green-500', 'intake' => 'bg-sky-500'];
    $eventLabel = ['quote' => 'Teklif', 'notified' => 'Bildirim', 'intake' => 'Kabul formu'];
@endphp

<x-slot name="header">
    <div class="flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1">
        <a href="{{ route('tickets.index') }}" wire:navigate class="text-sm text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">← Servis kayıtları</a>
        <h2 class="text-xl font-semibold leading-tight">
            <span class="sr-only">Servis kaydı</span>
            <x-copy-code :code="$ticket->tracking_code" text-class="font-black tracking-wider text-slate-900 dark:text-white" />
        </h2>
        <span class="rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $ticket->status->badge() }}">{{ $ticket->status->label() }}</span>
        <span class="hidden rounded-full px-3 py-1 text-xs font-semibold sm:inline {{ $ticket->priority->badge() }}">{{ $ticket->priority->label() }}</span>
    </div>
</x-slot>

<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    {{-- Üst bilgi: müşteri, cihaz, eylemler --}}
    <section class="{{ $card }} p-5">
        <div class="flex flex-wrap items-start gap-4">
            @if ($product->image)
                <img src="{{ $product->image }}" alt="" class="h-16 w-16 shrink-0 rounded-xl bg-slate-50 object-contain ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
            @endif
            <div class="min-w-0 flex-1">
                <p class="text-sm text-slate-500">{{ $client->full_name }} · <a class="text-indigo-600 dark:text-indigo-300" href="tel:{{ $client->phone }}">{{ $client->phone }}</a></p>
                <p class="text-xl font-black text-slate-900 dark:text-white">{{ $product->brand }} {{ $product->model_name }}</p>
                <p class="mt-1 line-clamp-2 text-sm text-slate-700 dark:text-slate-200">{{ $ticket->problem }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Teknisyen --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = ! open" class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-200">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">{{ $ticket->technician ? mb_strtoupper(mb_substr($ticket->technician, 0, 1)) : '?' }}</span>
                        {{ $ticket->technician ?: 'Teknisyen ata' }}
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute right-0 z-30 mt-1 w-56 overflow-hidden rounded-xl bg-white py-1 shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                        @forelse ($technicians as $tech)
                            <button type="button" wire:click="assignTechnician(@js($tech))" @click="open = false" @class(['flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-white/5', 'font-semibold text-indigo-600' => $tech === $ticket->technician])>
                                {{ $tech }} @if ($tech === $ticket->technician) ✓ @endif
                            </button>
                        @empty
                            <p class="px-3 py-2 text-xs text-slate-500">Teknisyen listesi boş.</p>
                        @endforelse
                        @if ($ticket->technician)
                            <button type="button" wire:click="assignTechnician('')" @click="open = false" class="w-full border-t border-slate-100 px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50 dark:border-white/5 dark:hover:bg-rose-500/10">Atamayı kaldır</button>
                        @endif
                        <a href="{{ route('settings', 'servis') }}" wire:navigate class="block border-t border-slate-100 px-3 py-2 text-xs font-semibold text-indigo-600 hover:bg-slate-50 dark:border-white/5 dark:hover:bg-white/5">Teknisyenleri yönet →</a>
                    </div>
                </div>
                {{-- Baskı --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" @click="open = ! open" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                        Yazdır ▾
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute right-0 z-30 mt-1 w-60 overflow-hidden rounded-xl bg-white py-1 shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                        @foreach (['a4' => ['A4 kabul formu', 'Fotoğraflar, imza ve teklifle'], 'a4-2' => ['A4 · 2 nüsha', 'Üst yarı müşteriye, alt yarı işletmeye'], 'a5' => ['A5 dikey form', 'Kompakt kabul formu'], '80' => ['Termal fiş 80 mm', 'QR ile takip'], '58' => ['Termal fiş 58 mm', 'Dar rulo'], 'label' => ['Cihaz etiketi 50×30', 'QR okutunca kayıt açılır']] as $f => [$l, $d])
                            <a href="{{ route('tickets.print', [$ticket, $f]) }}" target="_blank" @click="open = false" class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-white/5">
                                <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $l }}</span>
                                <span class="block text-[11px] text-slate-500">{{ $d }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @error('technician') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror

        {{-- İş akışı --}}
        @php
            // Adım durumları: done | current | todo · son adım (teslim) güncelse tamamlanmış sayılır
            $steps = collect($mainPath)->values()->map(function ($step, $i) use ($ticket, $visited, $mainPath) {
                $current = $ticket->status === $step;
                $state = $current ? ($i === count($mainPath) - 1 ? 'done' : 'current') : (in_array($step->value, $visited, true) ? 'done' : 'todo');

                return ['step' => $step, 'state' => $state, 'isCurrent' => $current];
            });
            $doneCount = $steps->where('state', 'done')->count();
        @endphp
        <div class="mt-5 border-t border-slate-100 pt-5 dark:border-white/5">
            <div class="mb-3 flex items-center justify-between gap-3 text-xs">
                <p class="font-semibold uppercase tracking-wider text-slate-500">İş akışı</p>
                <p class="tabular-nums text-slate-500">{{ $doneCount }} / {{ $steps->count() }} adım</p>
            </div>
            <div class="-mx-2 overflow-x-auto px-2 pb-1 [scrollbar-width:thin]">
                <ol class="flex min-w-[44rem]" aria-label="Servis iş akışı">
                    @foreach ($steps as $i => $s)
                        @php
                            $next = $steps[$i + 1] ?? null;
                            // Sonraki adıma giden çizgi: ulaşıldıysa dolu, sıradaki güncelse geçişli, değilse kesikli
                            $line = ! $next ? null : ($next['state'] === 'done' ? 'done' : ($next['state'] === 'current' ? 'active' : 'todo'));
                        @endphp
                        <li class="relative flex min-w-0 flex-1 flex-col items-center px-1 text-center" @if ($s['isCurrent']) aria-current="step" @endif>
                            @if ($line)
                                <span aria-hidden="true" @class([
                                    'absolute left-[calc(50%+1.25rem)] right-[calc(-50%+1.25rem)] top-[0.9375rem] h-0.5 rounded-full',
                                    'bg-emerald-500' => $line === 'done',
                                    'bg-gradient-to-r from-emerald-500 to-indigo-500' => $line === 'active',
                                    'bg-[repeating-linear-gradient(90deg,var(--color-slate-300)_0_6px,transparent_6px_11px)] dark:bg-[repeating-linear-gradient(90deg,var(--color-slate-600)_0_6px,transparent_6px_11px)]' => $line === 'todo',
                                ])></span>
                            @endif

                            <span @class([
                                'relative z-10 grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-bold transition',
                                'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30' => $s['state'] === 'done',
                                'bg-indigo-600 text-white shadow-lg shadow-indigo-500/40 ring-4 ring-indigo-500/20' => $s['state'] === 'current',
                                'bg-white text-slate-400 ring-2 ring-inset ring-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:ring-white/10' => $s['state'] === 'todo',
                            ])>
                                @if ($s['state'] === 'current')
                                    <span class="absolute inset-0 animate-ping rounded-full bg-indigo-500/40 motion-reduce:hidden" aria-hidden="true"></span>
                                    <span class="relative h-2.5 w-2.5 rounded-full bg-white"></span>
                                @elseif ($s['state'] === 'done')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>

                            <span @class(['mt-2 text-xs font-semibold leading-tight',
                                'text-slate-900 dark:text-white' => $s['state'] === 'done',
                                'text-indigo-700 dark:text-indigo-300' => $s['state'] === 'current',
                                'text-slate-400 dark:text-slate-500' => $s['state'] === 'todo'])>{{ $s['step']->label() }}</span>
                            <span class="mt-0.5 text-[11px] tabular-nums text-slate-500">
                                @isset($durations[$s['step']->value])
                                    {{ $human($durations[$s['step']->value]) }}
                                @elseif ($s['state'] === 'current')
                                    şu an
                                @else
                                    &nbsp;
                                @endisset
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>
            @if ($ticket->status === \App\Enums\TicketStatus::CANCELLED)
                <p class="mt-3 text-sm font-semibold text-rose-600">Bu kayıt iptal / iade edildi.</p>
            @endif
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Sol --}}
        <div class="space-y-6 lg:col-span-1">
            <section @class([
                'rounded-2xl p-5 shadow-sm text-sm',
                'bg-red-600 text-white' => $sla === 'overdue',
                'bg-amber-400 text-amber-950' => $sla === 'warning',
                'bg-emerald-600 text-white' => $sla === 'ok',
                'bg-white text-slate-800 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:ring-white/10' => in_array($sla, ['closed', 'none']),
            ])>
                <h3 class="text-xs font-bold uppercase tracking-wider opacity-80">Teslim hedefi (SLA)</h3>
                @if ($ticket->due_at)
                    <p class="mt-1 text-2xl font-black">{{ $ticket->due_at->format('d.m.Y H:i') }}</p>
                    <p class="mt-1 font-semibold">
                        @switch($sla)
                            @case('overdue') {{ $ticket->due_at->locale('tr')->diffForHumans(['parts' => 2]) }} gecikti @break
                            @case('warning') Süre daralıyor: {{ $ticket->due_at->locale('tr')->diffForHumans(['parts' => 2]) }} @break
                            @case('ok') {{ $ticket->due_at->locale('tr')->diffForHumans(['parts' => 2]) }} @break
                            @default
                                @if ($ticket->metSla() === true) ✓ Hedef süresinde kapandı
                                @elseif ($ticket->metSla() === false) ✗ Hedef {{ $human($ticket->due_at->diffInSeconds($ticket->closed_at)) }} aşılarak kapandı
                                @endif
                        @endswitch
                    </p>
                @endif
                @unless ($ticket->status->isClosed())
                    <form wire:submit="updateDueAt" class="mt-3 flex gap-2">
                        <input type="datetime-local" wire:model="dueAt" class="w-full rounded-md border-0 bg-white/90 px-2 py-1 text-sm text-slate-900">
                        <button class="rounded-md bg-black/20 px-3 text-xs font-semibold hover:bg-black/30">Güncelle</button>
                    </form>
                    @error('dueAt') <p class="mt-1 text-xs">{{ $message }}</p> @enderror
                @endunless
                <p class="mt-3 text-xs opacity-80">Toplam süre: {{ $human($ticket->created_at->diffInSeconds($ticket->closed_at ?? now())) }}</p>
            </section>

            <section class="{{ $card }} p-5 text-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Müşteri</h3>
                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $client->full_name }}</p>
                @if ($client->company) <p class="text-slate-600 dark:text-slate-300">{{ $client->company }}</p> @endif
                <p><a class="text-indigo-600 dark:text-indigo-300" href="tel:{{ $client->phone }}">{{ $client->phone }}</a></p>
                @if ($client->email) <p class="text-slate-600 dark:text-slate-300">{{ $client->email }}</p> @endif
                @if ($client->address) <p class="text-slate-500">{{ $client->address }}</p> @endif

                <h3 class="mt-4 text-xs font-bold uppercase tracking-wider text-slate-500">Cihaz</h3>
                <dl class="mt-1 grid grid-cols-[auto_1fr] gap-x-3 gap-y-0.5 text-xs">
                    <dt class="text-slate-500">IMEI</dt><dd>@if ($device->imei)<x-copy-code :code="$device->imei" text-class="text-slate-800 dark:text-slate-100" />@else — @endif</dd>
                    <dt class="text-slate-500">Seri no</dt><dd>@if ($device->serial)<x-copy-code :code="$device->serial" text-class="text-slate-800 dark:text-slate-100" />@else — @endif</dd>
                    <dt class="text-slate-500">Renk</dt><dd class="text-slate-800 dark:text-slate-100">{{ $device->color ?: '—' }}</dd>
                    <dt class="text-slate-500">Model no</dt><dd class="text-slate-800 dark:text-slate-100">{{ $product->model_number ?: '—' }}</dd>
                </dl>
            </section>

            {{-- Cihaz geçmişi (IMEI / seri) --}}
            <section class="{{ $card }} p-5 text-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Cihaz geçmişi</h3>
                    @if ($history->isNotEmpty())
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800 dark:bg-amber-500/15 dark:text-amber-200">{{ $history->count() }} önceki servis</span>
                    @endif
                </div>
                @forelse ($history as $past)
                    @php $sameOwner = $past->clientProduct->client_id === $client->id; @endphp
                    <a href="{{ route('tickets.show', $past) }}" wire:navigate class="-mx-2 mt-2 block rounded-lg px-2 py-2 hover:bg-slate-50 dark:hover:bg-white/5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ $past->tracking_code }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $past->status->badge() }}">{{ $past->status->label() }}</span>
                        </div>
                        <p class="mt-0.5 line-clamp-1 text-xs text-slate-600 dark:text-slate-300">{{ $past->problem }}</p>
                        <p class="text-[11px] text-slate-400">{{ $past->created_at->format('d.m.Y') }}
                            @if ($past->closed_at && $past->status === \App\Enums\TicketStatus::DELIVERED && $past->closed_at->gt(now()->subDays(90))) · <strong class="text-rose-600">90 gün içinde geri geldi</strong> @endif
                            @unless ($sameOwner) · <strong class="text-amber-600">farklı müşteri: {{ $past->clientProduct->client->full_name }}</strong> @endunless
                        </p>
                    </a>
                @empty
                    <p class="mt-2 text-xs text-slate-500">{{ $device->imei || $device->serial ? 'Bu IMEI / seri numarasıyla başka servis kaydı yok.' : 'IMEI ya da seri numarası girilmemiş; geçmiş eşleştirilemez.' }}</p>
                @endforelse
            </section>

            {{-- Memnuniyet --}}
            @if ($ticket->status === \App\Enums\TicketStatus::DELIVERED)
                <section class="{{ $card }} p-5 text-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Memnuniyet anketi</h3>
                    @if (! is_null($ticket->survey_score))
                        @php $s = $ticket->survey_score; @endphp
                        <p class="mt-2 flex items-baseline gap-2">
                            <span @class(['text-3xl font-black', 'text-emerald-600' => $s >= 9, 'text-amber-500' => $s >= 7 && $s <= 8, 'text-rose-600' => $s <= 6])>{{ $s }}</span>
                            <span class="text-slate-500">/ 10 · {{ $s >= 9 ? 'Destekçi' : ($s >= 7 ? 'Pasif' : 'Eleştirmen') }}</span>
                        </p>
                        @if ($ticket->survey_comment) <p class="mt-1 rounded-lg bg-slate-50 p-2 text-slate-700 dark:bg-white/5 dark:text-slate-200">“{{ $ticket->survey_comment }}”</p> @endif
                        <p class="mt-1 text-[11px] text-slate-400">{{ $ticket->survey_at->format('d.m.Y H:i') }}</p>
                    @else
                        <p class="mt-1 text-xs text-slate-500">Henüz yanıt yok. Teslim bildirimi şablonundaki {anket} bağlantısıyla müşteriye gönderin.</p>
                        <div class="mt-2"><x-copy-code :code="$ticket->surveyUrl()" class="max-w-full text-[11px]" text-class="text-indigo-600 dark:text-indigo-300" /></div>
                    @endif
                </section>
            @endif
        </div>

        {{-- Sağ --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- İşlem --}}
            <section class="{{ $card }} p-5">
                <h3 class="font-bold text-slate-900 dark:text-white">İşlem / not</h3>
                <textarea wire:model="note" rows="3" placeholder="Yapılan işlem, teşhis sonucu, müşteriyle görüşme notu…"
                    class="mt-2 block w-full rounded-xl border-0 bg-slate-50 px-3 py-2 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 dark:bg-white/5 dark:text-slate-100 dark:ring-white/10"></textarea>
                @error('note') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                @error('transition') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <label class="me-auto inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                        <input type="checkbox" wire:model="notePublic" class="rounded border-slate-300 text-indigo-600">
                        Müşteri takip sayfasında görünsün
                    </label>
                    <button type="button" wire:click="addNote" wire:loading.attr="disabled" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900">Not ekle</button>
                    @foreach ($ticket->status->allowedTransitions() as $next)
                        <button type="button" wire:click="moveTo('{{ $next->value }}')" wire:loading.attr="disabled" class="rounded-lg px-3 py-1.5 text-xs font-semibold ring-1 hover:brightness-95 {{ $next->badge() }}">→ {{ $next->label() }}</button>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Durum değiştirirken yazdığınız not, geçişle birlikte geçmişe kaydedilir.</p>
            </section>

            <livewire:tickets.ticket-notify :ticket-id="$ticket->id" :key="'notify-'.$ticket->id" />
            <livewire:tickets.ticket-quote :ticket-id="$ticket->id" :key="'quote-'.$ticket->id" />
            <livewire:tickets.ticket-intake :ticket-id="$ticket->id" :key="'intake-'.$ticket->id" />

            {{-- Zaman çizelgesi --}}
            <section class="{{ $card }} p-5">
                <h3 class="font-bold text-slate-900 dark:text-white">Zaman çizelgesi</h3>
                <ol class="relative mt-4 space-y-4 border-s border-slate-200 ps-5 dark:border-white/10">
                    @foreach ($ticket->events as $event)
                        <li wire:key="event-{{ $event->id }}" class="relative">
                            <span class="absolute -start-[1.65rem] top-1 h-3 w-3 rounded-full ring-4 ring-white dark:ring-slate-800 {{ $eventDot[$event->type] ?? 'bg-slate-400' }}"></span>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <time title="{{ $event->created_at->format('d.m.Y H:i:s') }}">{{ $event->created_at->format('d.m.Y H:i') }}</time>
                                <span>· {{ $event->user?->name ?? ($event->type === 'quote' ? 'Müşteri' : 'Sistem') }}</span>
                                @isset($eventLabel[$event->type]) <span class="rounded bg-slate-100 px-1.5 font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $eventLabel[$event->type] }}</span> @endisset
                                @if ($event->is_public)
                                    <span class="rounded bg-sky-100 px-1.5 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">müşteri görür</span>
                                @else
                                    <span class="rounded bg-slate-100 px-1.5 text-slate-600 dark:bg-white/10 dark:text-slate-300">dahili</span>
                                @endif
                            </div>
                            <div class="mt-1 text-sm text-slate-800 dark:text-slate-100">
                                @switch($event->type)
                                    @case('created') Servis kaydı açıldı. @break
                                    @case('status_changed')
                                        <span class="rounded px-1.5 text-xs ring-1 {{ $event->from_status?->badge() }}">{{ $event->from_status?->label() }}</span>
                                        →
                                        <span class="rounded px-1.5 text-xs ring-1 {{ $event->to_status?->badge() }}">{{ $event->to_status?->label() }}</span>
                                        @break
                                @endswitch
                                @if ($event->note)
                                    <p class="mt-1 whitespace-pre-line rounded-lg bg-slate-50 p-2 text-sm dark:bg-white/5">{{ $event->note }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    </div>
</div>
