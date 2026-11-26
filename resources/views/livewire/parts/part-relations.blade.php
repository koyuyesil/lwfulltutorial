@use('App\Enums\CompatibilityKind')
@php
    $field = 'block rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
    $th = 'px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500';
    $stockBadge = fn ($p) => $p->stock > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400';
@endphp

<div class="space-y-6">
    {{-- 1. Bulunduğu cihazlar --}}
    <section class="rounded-2xl ring-1 ring-slate-200 dark:ring-white/10">
        <header class="flex items-center gap-2 rounded-t-2xl border-b border-slate-100 bg-sky-50/60 px-4 py-3 dark:border-white/5 dark:bg-sky-500/5">
            <span class="grid h-7 w-7 place-items-center rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Bulunduğu cihazlar <span class="font-normal text-slate-500">{{ $part->products->count() }}</span></h3>
                <p class="text-[11px] text-slate-500">Bu parça hangi telefonlarda takılı geliyor</p>
            </div>
        </header>
        <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-white/5">
            <thead class="bg-slate-50/60 dark:bg-white/[.02]"><tr><th class="{{ $th }}">Cihaz</th><th class="{{ $th }}">Kod adı</th><th class="{{ $th }}">Varyant</th><th class="w-10"></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($part->products as $p)
                    <tr wire:key="pp-{{ $p->pivot->id }}" class="group">
                        <td class="px-3 py-2 font-semibold text-slate-900 dark:text-white">{{ $p->brand }} {{ $p->model_name }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $p->codename ?: '-' }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $p->pivot->variant ?: '-' }}</td>
                        <td class="px-2 py-2 text-right">
                            <button type="button" wire:click="detachDevice({{ $p->pivot->id }})" class="rounded px-1.5 text-slate-400 opacity-0 hover:text-rose-600 focus:opacity-100 group-hover:opacity-100" aria-label="Cihaz bağlantısını kaldır">✕</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-4 text-center text-xs text-slate-500">Henüz cihaz bağlanmadı. Bağımsız parça olarak da kalabilir.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-100 p-3 dark:border-white/5" x-data="{ open: false }" @click.outside="open = false">
            <div class="relative flex gap-2">
                <input type="search" wire:model.live.debounce.250ms="deviceSearch" @focus="open = true" @input="open = true" placeholder="Cihaz ara ve ekle (model, kod adı, model no)" class="{{ $field }} min-w-0 flex-1">
                <input wire:model="variant" placeholder="Varyant (8/256)" class="{{ $field }} w-32 flex-none font-mono">
                <div x-show="open && $wire.deviceSearch.length > 1" x-cloak class="absolute left-0 right-0 top-full z-20 mt-1 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                    @forelse ($this->deviceResults as $p)
                        <button type="button" wire:click="attachDevice({{ $p->id }})" @click="open = false" class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                            <span class="truncate font-medium text-slate-900 dark:text-white">{{ $p->brand }} {{ $p->model_name }}</span>
                            <span class="shrink-0 font-mono text-xs text-slate-500">{{ $p->codename }}</span>
                        </button>
                    @empty
                        <p class="px-3 py-3 text-xs text-slate-500">Eşleşen cihaz yok. <a href="{{ route('products.index') }}" wire:navigate class="font-semibold text-indigo-600">Cihaz modeli ekle →</a></p>
                    @endforelse
                </div>
            </div>
            @if ($reachable->isNotEmpty())
                <p class="mt-2 text-xs text-slate-500"><strong class="text-emerald-600">Muadilleri sayesinde</strong> şu cihazlarda da kullanılabilir: {{ $reachable->map(fn ($p) => $p->brand.' '.$p->model_name)->implode(', ') }}</p>
            @endif
        </div>
    </section>

    {{-- 2. Muadiller: 3 sütunlu eşleştirme tablosu --}}
    <section class="rounded-2xl ring-1 ring-slate-200 dark:ring-white/10">
        <header class="flex items-center gap-2 rounded-t-2xl border-b border-slate-100 bg-emerald-50/60 px-4 py-3 dark:border-white/5 dark:bg-emerald-500/5">
            <span class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Muadiller <span class="font-normal text-slate-500">{{ $compatibles->count() }}</span></h3>
                <p class="text-[11px] text-slate-500">Yerine takılabilecek parçalar · iki yönlü: karşı parçada da görünür</p>
            </div>
        </header>
        <table class="min-w-full table-fixed divide-y divide-slate-100 text-sm dark:divide-white/5">
            <thead class="bg-slate-50/60 dark:bg-white/[.02]"><tr><th class="{{ $th }} w-[36%]">Bu parça</th><th class="{{ $th }} w-[28%] text-center!">Uyum</th><th class="{{ $th }}">Muadil parça</th><th class="w-10"></th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($compatibles as $o)
                    @php $k = CompatibilityKind::tryFrom($o->pivot->kind) ?? CompatibilityKind::DIRECT; @endphp
                    <tr wire:key="pc-{{ $o->id }}" class="group">
                        <td class="truncate px-3 py-2 font-mono text-xs font-semibold text-slate-500" title="{{ $part->label() }}">{{ $part->ref() }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $k->badge() }}" title="{{ $k->description() }}">⇄ {{ $k->label() }}</span>
                            @if ($o->pivot->note)<p class="mt-0.5 truncate text-[11px] text-slate-500" title="{{ $o->pivot->note }}">{{ $o->pivot->note }}</p>@endif
                        </td>
                        <td class="px-3 py-2">
                            <button type="button" x-on:click="Livewire.dispatch('open-part', { id: {{ $o->id }} })" class="flex w-full min-w-0 items-center gap-2 text-left">
                                @if ($o->type)<span class="shrink-0 rounded px-1 text-[9px] font-black {{ $o->type->badge() }}">{{ $o->type->short() }}</span>@endif
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-mono text-sm font-bold text-slate-900 hover:text-indigo-600 dark:text-white">{{ $o->ref() }}</span>
                                    @if ($o->code && $o->name !== $o->code)<span class="block truncate text-[11px] text-slate-500">{{ $o->name }}</span>@endif
                                </span>
                                <span class="shrink-0 rounded px-1.5 font-mono text-[11px] font-bold {{ $stockBadge($o) }}" title="Stok">{{ $o->stock }}</span>
                            </button>
                        </td>
                        <td class="px-2 py-2 text-right">
                            <button type="button" wire:click="unlink({{ $o->id }})" class="rounded px-1.5 text-slate-400 opacity-0 hover:text-rose-600 focus:opacity-100 group-hover:opacity-100" aria-label="Muadilliği kaldır">✕</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-4 text-center text-xs text-slate-500">Muadil parça eklenmedi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="space-y-2 border-t border-slate-100 p-3 dark:border-white/5" x-data="{ open: false }" @click.outside="open = false">
            <div class="grid gap-2 sm:grid-cols-[1fr_170px]">
                <div class="relative">
                    <input type="search" wire:model.live.debounce.250ms="compatSearch" @focus="open = true" @input="open = true" placeholder="Muadil parça ara (kod, ad, stok kodu)" class="{{ $field }} w-full font-mono">
                    <div x-show="open && $wire.compatSearch.length > 0" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                        @forelse ($this->compatResults as $o)
                            <button type="button" wire:click="link({{ $o->id }})" @click="open = false" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                                @if ($o->type)<span class="rounded px-1 text-[9px] font-black {{ $o->type->badge() }}">{{ $o->type->short() }}</span>@endif
                                <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $o->ref() }}</span>
                                <span class="truncate text-xs text-slate-500">{{ $o->code ? $o->name : '' }}</span>
                            </button>
                        @empty
                            <p class="px-3 py-3 text-xs text-slate-500">Eşleşen parça yok. Önce o parçayı ekleyin.</p>
                        @endforelse
                    </div>
                </div>
                <select wire:model="kind" class="{{ $field }} w-full pr-8">
                    @foreach (CompatibilityKind::cases() as $k)<option value="{{ $k->value }}">{{ $k->label() }}</option>@endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <input wire:model="compatNote" placeholder="Not (isteğe bağlı): ör. firmware yazılmalı" class="{{ $field }} min-w-0 flex-1">
                @if ($part->type)
                    <label class="inline-flex shrink-0 items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300"><input type="checkbox" wire:model.live="sameType" class="rounded border-slate-300 text-indigo-600"> Yalnızca aynı tür</label>
                @endif
            </div>
        </div>
    </section>
</div>
