@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Günaydın' : ($hour < 18 ? 'İyi günler' : 'İyi akşamlar');
    $card = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10';
@endphp

<x-slot name="header">
    <h2>Dashboard</h2>
</x-slot>

<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    {{-- Karşılama --}}
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ now()->locale('tr')->translatedFormat('d F Y, l') }}</p>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ $greeting }}, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }} 👋</h1>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-300">
            @if ($kpi['overdue'])
                <span class="font-semibold text-red-600 dark:text-red-400">{{ $kpi['overdue'] }} iş gecikmede</span> ·
            @endif
            Bugün teslim hedefi olan <strong>{{ $kpi['dueToday'] }}</strong> iş var.
        </p>
    </div>

    {{-- KPI kartları --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-6">
        @php
            $kpis = [
                ['label' => 'Açık iş', 'value' => $kpi['open'], 'sub' => $kpi['clients'] . ' müşteri', 'href' => route('tickets.index'), 'tone' => 'indigo'],
                ['label' => 'Geciken', 'value' => $kpi['overdue'], 'sub' => 'Hedef süresi geçti', 'href' => route('tickets.index', ['sla' => 'overdue']), 'tone' => 'red'],
                ['label' => 'Riskli', 'value' => $kpi['atRisk'], 'sub' => "Sürenin son %25'i", 'href' => route('tickets.index', ['sla' => 'at_risk']), 'tone' => 'amber'],
                ['label' => 'Teslime hazır', 'value' => $kpi['ready'], 'sub' => 'Müşteri bekleniyor', 'href' => route('tickets.index', ['durum' => 'ready']), 'tone' => 'emerald'],
                ['label' => 'SLA uyumu', 'value' => is_null($kpi['compliance']) ? '—' : '%' . $kpi['compliance'], 'sub' => $kpi['delivered30'] . ' teslim · 30 gün', 'href' => null, 'tone' => 'sky'],
                ['label' => 'Ort. çözüm', 'value' => is_null($kpi['avgHours']) ? '—' : $kpi['avgHours'] . ' sa', 'sub' => 'Kabulden teslime', 'href' => null, 'tone' => 'violet'],
            ];
            $tones = [
                'indigo' => 'bg-indigo-500', 'red' => 'bg-red-500', 'amber' => 'bg-amber-400',
                'emerald' => 'bg-emerald-500', 'sky' => 'bg-sky-500', 'violet' => 'bg-violet-500',
            ];
        @endphp
        @foreach ($kpis as $item)
            <{{ $item['href'] ? 'a' : 'div' }} @if ($item['href']) href="{{ $item['href'] }}" wire:navigate @endif
                class="{{ $card }} group relative overflow-hidden {{ $item['href'] ? 'transition hover:-translate-y-0.5 hover:shadow-lg' : '' }}">
                <span class="absolute inset-y-0 left-0 w-1 {{ $tones[$item['tone']] }}"></span>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $item['label'] }}</p>
                <p @class(['mt-2 text-3xl font-black tracking-tight',
                    'text-red-600 dark:text-red-400' => $item['tone'] === 'red' && $item['value'] > 0,
                    'text-slate-900 dark:text-white' => ! ($item['tone'] === 'red' && $item['value'] > 0)])>{{ $item['value'] }}</p>
                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $item['sub'] }}</p>
            </{{ $item['href'] ? 'a' : 'div' }}>
        @endforeach
    </div>

    {{-- Grafikler: trend + durum --}}
    <div class="grid gap-6 xl:grid-cols-3">
        <section class="{{ $card }} xl:col-span-2">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Servis hacmi</h3>
                    <p class="text-xs text-slate-500">Son {{ \App\Livewire\Dashboard\DashboardIndex::TREND_DAYS }} gün · açılan ve kapanan kayıtlar</p>
                </div>
            </header>
            <div wire:ignore class="mt-2" x-data="apexChart(@js($trendChart))"></div>
        </section>
        <section class="{{ $card }}">
            <header>
                <h3 class="font-bold text-slate-900 dark:text-white">Servis durumu dağılımı</h3>
                <p class="text-xs text-slate-500">Açık işler iş akışında nerede?</p>
            </header>
            <div wire:ignore class="mt-2" x-data="apexChart(@js($statusChart))"></div>
        </section>
    </div>

    {{-- Teknisyen + marka + stok --}}
    <div class="grid gap-6 xl:grid-cols-3">
        <section class="{{ $card }}">
            <header>
                <h3 class="font-bold text-slate-900 dark:text-white">Teknisyen performansı</h3>
                <p class="text-xs text-slate-500">Son 30 gün · tamamlanan iş ve toplam işlem</p>
            </header>
            <div wire:ignore class="mt-2" x-data="apexChart(@js($technicianChart))"></div>
        </section>
        <section class="{{ $card }}">
            <header>
                <h3 class="font-bold text-slate-900 dark:text-white">En çok gelen markalar</h3>
                <p class="text-xs text-slate-500">Tüm servis kayıtları</p>
            </header>
            <div wire:ignore class="mt-2" x-data="apexChart(@js($brandChart))"></div>
        </section>
        <section class="{{ $card }} flex flex-col">
            <header class="flex items-start justify-between gap-2">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Kritik stok seviyeleri</h3>
                    <p class="text-xs text-slate-500">Yedek parça stoğu</p>
                </div>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600 dark:bg-white/10 dark:text-slate-300">Yakında</span>
            </header>
            <div class="mt-4 flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 p-6 text-center dark:border-white/15">
                <svg class="h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Parça stoğu modülü henüz etkin değil</p>
                <p class="mt-1 text-xs text-slate-500">Etkinleştirildiğinde minimum seviyenin altına düşen parçalar burada listelenecek.</p>
            </div>
        </section>
    </div>

    {{-- Acil işler + son hareketler --}}
    <div class="grid gap-6 xl:grid-cols-5">
        <section class="{{ $card }} xl:col-span-3">
            <header class="flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white">Teslim hedefi en yakın işler</h3>
                <a href="{{ route('tickets.index') }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Tümü →</a>
            </header>
            <ul class="mt-3 divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($urgent as $t)
                    @php $sla = $t->slaState(); @endphp
                    <li>
                        <a href="{{ route('tickets.show', $t) }}" wire:navigate class="-mx-2 flex items-center gap-3 rounded-lg px-2 py-2.5 transition hover:bg-slate-50 dark:hover:bg-white/5">
                            <span @class(['h-2.5 w-2.5 shrink-0 rounded-full',
                                'bg-red-500' => $sla === 'overdue', 'bg-amber-400' => $sla === 'warning', 'bg-emerald-500' => $sla === 'ok', 'bg-slate-300' => $sla === 'none'])></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $t->clientProduct->product->brand }} {{ $t->clientProduct->product->model_name }}
                                    <span class="font-normal text-slate-500">· {{ $t->clientProduct->client->first_name }} {{ $t->clientProduct->client->last_name }}</span>
                                </span>
                                <span class="block truncate text-xs text-slate-500">{{ $t->problem }}</span>
                            </span>
                            <span class="hidden rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 sm:inline {{ $t->status->badge() }}">{{ $t->status->label() }}</span>
                            <span @class(['w-24 shrink-0 text-right text-xs font-semibold',
                                'text-red-600 dark:text-red-400' => $sla === 'overdue', 'text-amber-600 dark:text-amber-400' => $sla === 'warning', 'text-slate-500' => ! in_array($sla, ['overdue', 'warning'])])>
                                {{ $t->due_at?->locale('tr')->diffForHumans(['short' => true]) }}
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="py-10 text-center text-sm text-slate-500">
                        Açık servis kaydınız yok.
                        <a href="{{ route('tickets.index') }}#yeni" wire:navigate class="font-semibold text-indigo-600">İlk kaydı oluşturun →</a>
                    </li>
                @endforelse
            </ul>
        </section>

        <section class="{{ $card }} xl:col-span-2">
            <h3 class="font-bold text-slate-900 dark:text-white">Son hareketler</h3>
            <ol class="mt-4 space-y-4">
                @forelse ($activity as $event)
                    <li class="flex gap-3">
                        <span @class(['mt-1 h-2 w-2 shrink-0 rounded-full',
                            'bg-indigo-500' => $event->type === 'status_changed', 'bg-emerald-500' => $event->type === 'created', 'bg-slate-400' => $event->type === 'note'])></span>
                        <div class="min-w-0 text-sm">
                            <p class="text-slate-700 dark:text-slate-200">
                                <strong>{{ $event->user?->name ?? 'Sistem' }}</strong>
                                @switch($event->type)
                                    @case('created') yeni kayıt açtı @break
                                    @case('status_changed') durumu <strong>{{ $event->to_status?->label() }}</strong> yaptı @break
                                    @default not ekledi
                                @endswitch
                                <a href="{{ route('tickets.show', $event->ticket_id) }}" wire:navigate class="font-mono text-xs text-indigo-600 dark:text-indigo-400">{{ $event->ticket?->tracking_code }}</a>
                            </p>
                            @if ($event->note)
                                <p class="truncate text-xs text-slate-500">{{ $event->note }}</p>
                            @endif
                            <p class="text-xs text-slate-400">{{ $event->created_at->locale('tr')->diffForHumans() }}</p>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Henüz hareket yok.</li>
                @endforelse
            </ol>
        </section>
    </div>
</div>
