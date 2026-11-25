@php
    $client = $ticket->clientProduct->client;
    $device = $ticket->clientProduct;
    $product = $device->product;
    $sla = $ticket->slaState();
    $visited = $ticket->events->pluck('to_status')->filter()->map->value->unique()->all();
    $human = fn (int|float $seconds) => \Carbon\CarbonInterval::seconds((int) $seconds)->cascade()
        ->locale('tr')->forHumans(['short' => true, 'parts' => 2]) ?: '0 sn';
@endphp

<x-slot name="header">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('tickets.index') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">← Servis kayıtları</a>
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Servis kaydı #{{ $ticket->id }}
        </h2>
        <span class="rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $ticket->status->badge() }}">{{ $ticket->status->label() }}</span>
        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $ticket->priority->badge() }}">{{ $ticket->priority->label() }}</span>
    </div>
</x-slot>

<div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

    {{-- İş akışı adımları --}}
    <section class="overflow-x-auto rounded-xl bg-white p-4 shadow dark:bg-gray-800">
        <ol class="flex min-w-max items-center gap-1 text-xs">
            @foreach ($mainPath as $step)
                @php
                    $isCurrent = $ticket->status === $step;
                    $isDone = ! $isCurrent && in_array($step->value, $visited, true);
                @endphp
                <li class="flex items-center gap-1">
                    <span @class([
                        'flex items-center gap-1 rounded-full px-3 py-1.5 font-semibold ring-1',
                        $step->badge() . ' ring-2 shadow' => $isCurrent,
                        'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800' => $isDone,
                        'bg-gray-50 text-gray-400 ring-gray-200 dark:bg-gray-900/40 dark:text-gray-500 dark:ring-gray-700' => ! $isCurrent && ! $isDone,
                    ])>
                        @if ($isDone) ✓ @endif {{ $step->label() }}
                        @isset($durations[$step->value])
                            <span class="font-normal opacity-70">· {{ $human($durations[$step->value]) }}</span>
                        @endisset
                    </span>
                    @unless ($loop->last)
                        <span class="text-gray-300 dark:text-gray-600">›</span>
                    @endunless
                </li>
            @endforeach
        </ol>
        @if ($ticket->status === \App\Enums\TicketStatus::CANCELLED)
            <p class="mt-3 text-sm font-semibold text-rose-600">Bu kayıt iptal / iade edildi.</p>
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Sol: bilgiler + SLA --}}
        <div class="space-y-6 lg:col-span-1">
            <section @class([
                'rounded-xl p-4 shadow text-sm',
                'bg-red-600 text-white' => $sla === 'overdue',
                'bg-amber-400 text-amber-950' => $sla === 'warning',
                'bg-emerald-600 text-white' => $sla === 'ok',
                'bg-white text-gray-800 dark:bg-gray-800 dark:text-gray-100' => in_array($sla, ['closed', 'none']),
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
                        <input type="datetime-local" wire:model="dueAt"
                            class="w-full rounded-md border-0 bg-white/90 px-2 py-1 text-sm text-gray-900">
                        <button class="rounded-md bg-black/20 px-3 text-xs font-semibold hover:bg-black/30">Güncelle</button>
                    </form>
                    @error('dueAt') <p class="mt-1 text-xs">{{ $message }}</p> @enderror
                @endunless
                <p class="mt-3 text-xs opacity-80">Toplam süre: {{ $human($ticket->created_at->diffInSeconds($ticket->closed_at ?? now())) }}</p>
            </section>

            <section class="rounded-xl bg-white p-4 text-sm shadow dark:bg-gray-800 dark:text-gray-200">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Müşteri</h3>
                <p class="mt-1 font-semibold">{{ $client->first_name }} {{ $client->last_name }}</p>
                @if ($client->company) <p>{{ $client->company }}</p> @endif
                <p><a class="text-indigo-600 dark:text-indigo-300" href="tel:{{ $client->phone }}">{{ $client->phone }}</a></p>
                @if ($client->email) <p>{{ $client->email }}</p> @endif
                @if ($client->address) <p class="text-gray-500">{{ $client->address }}</p> @endif

                <h3 class="mt-4 text-xs font-bold uppercase tracking-wider text-gray-500">Cihaz</h3>
                <p class="mt-1 font-semibold">{{ $product->brand }} {{ $product->model_name }}</p>
                <dl class="mt-1 grid grid-cols-[auto_1fr] gap-x-3 text-xs">
                    <dt class="text-gray-500">IMEI</dt><dd>{{ $device->imei ?: '—' }}</dd>
                    <dt class="text-gray-500">Seri no</dt><dd>{{ $device->serial ?: '—' }}</dd>
                    <dt class="text-gray-500">Renk</dt><dd>{{ $device->color ?: '—' }}</dd>
                    <dt class="text-gray-500">Model no</dt><dd>{{ $product->model_number ?: '—' }}</dd>
                </dl>

                <h3 class="mt-4 text-xs font-bold uppercase tracking-wider text-gray-500">Arıza</h3>
                <p class="mt-1">{{ $ticket->problem }}</p>
            </section>
        </div>

        {{-- Sağ: işlem paneli + zaman çizelgesi --}}
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">İşlem / not</h3>
                <textarea wire:model="note" rows="3" placeholder="Yapılan işlem, teşhis sonucu, müşteriyle görüşme notu…"
                    class="mt-2 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                @error('note') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('transition') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <label class="me-auto inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                        <input type="checkbox" wire:model="notePublic" class="rounded border-gray-300">
                        Müşteri takip sayfasında görünsün
                    </label>
                    <button type="button" wire:click="addNote" wire:loading.attr="disabled"
                        class="rounded-md bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-700 dark:bg-gray-200 dark:text-gray-900">
                        Not ekle
                    </button>
                    @foreach ($ticket->status->allowedTransitions() as $next)
                        <button type="button" wire:click="moveTo('{{ $next->value }}')" wire:loading.attr="disabled"
                            class="rounded-md px-3 py-1.5 text-xs font-semibold ring-1 hover:brightness-95 {{ $next->badge() }}">
                            → {{ $next->label() }}
                        </button>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Durum değiştirirken yazdığınız not, geçişle birlikte geçmişe kaydedilir.</p>
            </section>

            <section class="rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Zaman çizelgesi</h3>
                <ol class="relative mt-4 space-y-4 border-s border-gray-200 ps-5 dark:border-gray-700">
                    @foreach ($ticket->events as $event)
                        <li wire:key="event-{{ $event->id }}" class="relative">
                            <span @class([
                                'absolute -start-[1.65rem] top-1 h-3 w-3 rounded-full ring-4 ring-white dark:ring-gray-800',
                                'bg-indigo-500' => $event->type === 'status_changed',
                                'bg-emerald-500' => $event->type === 'created',
                                'bg-gray-400' => $event->type === 'note',
                            ])></span>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <time title="{{ $event->created_at->format('d.m.Y H:i:s') }}">{{ $event->created_at->format('d.m.Y H:i') }}</time>
                                <span>· {{ $event->user?->name ?? 'Sistem' }}</span>
                                @if ($event->is_public)
                                    <span class="rounded bg-sky-100 px-1.5 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">müşteri görür</span>
                                @else
                                    <span class="rounded bg-gray-100 px-1.5 text-gray-600 dark:bg-gray-700 dark:text-gray-300">dahili</span>
                                @endif
                            </div>
                            <div class="mt-1 text-sm text-gray-800 dark:text-gray-100">
                                @switch($event->type)
                                    @case('created')
                                        Servis kaydı açıldı.
                                        @break
                                    @case('status_changed')
                                        <span class="rounded px-1.5 text-xs ring-1 {{ $event->from_status?->badge() }}">{{ $event->from_status?->label() }}</span>
                                        →
                                        <span class="rounded px-1.5 text-xs ring-1 {{ $event->to_status?->badge() }}">{{ $event->to_status?->label() }}</span>
                                        @break
                                @endswitch
                                @if ($event->note)
                                    <p class="mt-1 whitespace-pre-line rounded-md bg-gray-50 p-2 text-sm dark:bg-gray-900/50">{{ $event->note }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    </div>
</div>
