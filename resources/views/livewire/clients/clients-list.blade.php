@php
    $filters = ['' => 'Tümü', 'active' => 'Açık işi olan', 'corporate' => 'Kurumsal', 'individual' => 'Bireysel'];
    $sorts = ['newest' => 'En yeni', 'name' => 'Ada göre (A-Z)', 'devices' => 'En çok cihaz', 'tickets' => 'En çok servis'];
    $palette = ['bg-sky-500', 'bg-indigo-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-teal-500', 'bg-fuchsia-500'];
    $avatar = fn ($c) => $palette[$c->id % count($palette)];
    $isFiltered = $search !== '' || $filter !== '' || $sort !== 'newest';
@endphp

<div class="mt-6 space-y-4">
    {{-- Özet --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-ui.stat label="Toplam müşteri" :value="$stats['total']" tone="indigo" />
        <x-ui.stat label="Açık işi olan" :value="$stats['active']" hint="Serviste cihazı bulunan" tone="amber" />
        <x-ui.stat label="Kurumsal" :value="$stats['corporate']" :hint="$stats['total'] ? '%' . round($stats['corporate'] / $stats['total'] * 100) . ' oranında' : null" tone="violet" />
        <x-ui.stat label="Bu ay eklenen" :value="$stats['thisMonth']" tone="emerald" />
    </div>

    {{-- Araç çubuğu --}}
    <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
        <x-ui.search wire:model.live.debounce.300ms="search" placeholder="Ad, telefon, e-posta, firma, IMEI…" />

        <div class="flex flex-wrap gap-1.5">
            @foreach ($filters as $key => $label)
                <button type="button" wire:click="$set('filter', '{{ $key }}')" @class([
                    'rounded-full px-3 py-1.5 text-xs font-semibold transition',
                    'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => $filter === $key,
                    'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10' => $filter !== $key,
                ])>{{ $label }}</button>
            @endforeach
        </div>

        <div class="ms-auto flex items-center gap-2">
            @if ($isFiltered)
                <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Temizle</button>
            @endif
            <label class="sr-only" for="client-sort">Sırala</label>
            <select id="client-sort" wire:model.live="sort"
                class="rounded-lg border-0 bg-slate-100 py-1.5 pl-3 pr-8 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-600 dark:bg-white/5 dark:text-slate-200">
                @foreach ($sorts as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex rounded-lg bg-slate-100 p-0.5 dark:bg-white/5" role="group" aria-label="Görünüm">
                @foreach (['table' => 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5', 'cards' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'] as $mode => $icon)
                    <button type="button" wire:click="$set('view', '{{ $mode }}')" title="{{ $mode === 'table' ? 'Kompakt liste' : 'Kartlar' }}"
                        @class(['rounded-md p-1.5 transition', 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' => $view === $mode, 'text-slate-400 hover:text-slate-600' => $view !== $mode])>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Liste --}}
    <div class="relative" wire:loading.class="opacity-60" wire:target="search,filter,sort,view,gotoPage,nextPage,previousPage">
        @if ($clients->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-white/10 dark:bg-slate-800/50">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                @if ($isFiltered)
                    <p class="mt-3 font-semibold text-slate-800 dark:text-white">Aramanıza uyan müşteri yok</p>
                    <button type="button" wire:click="clearFilters" class="mt-2 text-sm font-semibold text-indigo-600">Filtreleri temizle</button>
                @else
                    <p class="mt-3 font-semibold text-slate-800 dark:text-white">Henüz müşteri eklemediniz</p>
                    <p class="mt-1 text-sm text-slate-500">İlk müşterinizi ekleyin; servis kaydı açarken saniyeler içinde seçebilirsiniz.</p>
                    <button type="button" x-on:click="$dispatch('open-drawer', { name: 'client-form' }); Livewire.dispatch('create-client')"
                        class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Yeni müşteri</button>
                @endif
            </div>
        @elseif ($view === 'table')
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10 [&_thead_th:first-child]:rounded-tl-xl [&_thead_th:last-child]:rounded-tr-xl">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-white/5 dark:text-slate-400">
                        <tr>
                            <th class="w-full px-4 py-3">Müşteri</th>
                            <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">İletişim</th>
                            <th class="hidden px-4 py-3 lg:table-cell">Cihazlar</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center">Servis</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Kayıt</th>
                            <th class="px-4 py-3"><span class="sr-only">İşlemler</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($clients as $client)
                            <tr wire:key="client-{{ $client->id }}" class="group cursor-pointer transition hover:bg-slate-50 dark:hover:bg-white/5" x-on:click="$dispatch('open-drawer', { name: 'client-detail' }); Livewire.dispatch('show-client', { id: {{ $client->id }} })">
                                {{-- Ad sütunu kalan genişliği alır; sığmazsa "…" ile kısalır, üzerine gelince tam ad --}}
                                <td class="w-full min-w-40 max-w-0 px-4 py-3">
                                    <div class="flex items-center gap-3" title="{{ $client->full_name }}{{ $client->company ? ' · '.$client->company : '' }}">
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-xs font-bold text-white {{ $avatar($client) }}">{{ $client->initials }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $client->full_name }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $client->company ?: 'Bireysel' }} <span class="md:hidden">· {{ $client->phone }}</span></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden max-w-64 px-4 py-3 md:table-cell">
                                    <p class="whitespace-nowrap font-medium text-slate-700 dark:text-slate-200">{{ $client->phone }}</p>
                                    <p class="truncate text-xs text-slate-500" title="{{ $client->email }}">{{ $client->email ?: '—' }}</p>
                                </td>
                                <td class="hidden min-w-44 max-w-72 px-4 py-3 lg:table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($client->clientsProducts->take(2) as $device)
                                            <span class="max-w-full truncate whitespace-nowrap rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-700 dark:bg-white/10 dark:text-slate-200" title="{{ $device->product?->brand }} {{ $device->product?->model_name }}">{{ $device->product?->brand }} {{ $device->product?->model_name }}</span>
                                        @endforeach
                                        @if ($client->devices_count > 2)
                                            <span class="rounded-md px-1.5 py-0.5 text-xs text-slate-500">+{{ $client->devices_count - 2 }}</span>
                                        @elseif ($client->devices_count === 0)
                                            <span class="text-xs text-slate-400">Cihaz yok</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    @if ($client->open_tickets_count)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-500/15 dark:text-amber-300" title="Açık servis kaydı">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ $client->open_tickets_count }} açık
                                        </span>
                                    @endif
                                    <span class="block text-xs text-slate-500">{{ $client->tickets_count }} toplam</span>
                                </td>
                                <td class="hidden whitespace-nowrap px-4 py-3 text-xs text-slate-500 xl:table-cell">{{ $client->created_at?->locale('tr')->diffForHumans() }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right" x-on:click.stop>
                                    @include('livewire.clients.partials.row-actions', ['client' => $client])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($clients as $client)
                    <article wire:key="client-card-{{ $client->id }}"
                        class="group flex flex-col rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-slate-800 dark:ring-white/10">
                        <div class="flex items-start gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full text-sm font-bold text-white {{ $avatar($client) }}">{{ $client->initials }}</span>
                            <button type="button" x-on:click="$dispatch('open-drawer', { name: 'client-detail' }); Livewire.dispatch('show-client', { id: {{ $client->id }} })" class="min-w-0 flex-1 text-left">
                                <p class="truncate font-bold text-slate-900 hover:text-indigo-600 dark:text-white" title="{{ $client->full_name }}">{{ $client->full_name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $client->company ?: 'Bireysel müşteri' }}</p>
                            </button>
                            @include('livewire.clients.partials.row-actions', ['client' => $client])
                        </div>
                        <dl class="mt-4 space-y-1 text-sm">
                            <div class="flex gap-2"><dt class="w-16 shrink-0 text-slate-500">Telefon</dt><dd class="font-medium text-slate-800 dark:text-slate-100">{{ $client->phone }}</dd></div>
                            <div class="flex gap-2"><dt class="w-16 shrink-0 text-slate-500">E-posta</dt><dd class="truncate text-slate-700 dark:text-slate-200">{{ $client->email ?: '—' }}</dd></div>
                        </dl>
                        <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-3 text-xs dark:border-white/5">
                            <span class="rounded-md bg-slate-100 px-2 py-1 font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $client->devices_count }} cihaz</span>
                            <span class="rounded-md bg-slate-100 px-2 py-1 font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $client->tickets_count }} servis</span>
                            @if ($client->open_tickets_count)
                                <span class="rounded-md bg-amber-100 px-2 py-1 font-semibold text-amber-800 dark:bg-amber-500/15 dark:text-amber-300">{{ $client->open_tickets_count }} açık</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div>{{ $clients->links() }}</div>
</div>
