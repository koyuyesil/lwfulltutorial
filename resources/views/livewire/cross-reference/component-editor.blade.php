@use('App\Enums\ComponentType')
@use('App\Enums\CompatibilityKind')
@php
    $field = 'block rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
    $input = 'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
    $section = 'rounded-2xl ring-1 ring-slate-200 dark:ring-white/10';
@endphp

<div class="flex min-h-0 flex-1 flex-col">
    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
        {{-- Bilgiler --}}
        <form wire:submit="save" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-[200px_1fr]">
                <x-ui.field label="Tür" for="ce-type" error="type">
                    <select id="ce-type" wire:model.live="type" class="{{ $input }} pr-8">
                        @foreach (ComponentType::cases() as $t)<option value="{{ $t->value }}">{{ $t->label() }}</option>@endforeach
                    </select>
                </x-ui.field>
                <x-ui.field label="Parça / model kodu" for="ce-code" error="code" hint="Entegre üzerindeki ya da datasheet'teki kod">
                    <input id="ce-code" wire:model="code" class="{{ $input }} font-mono uppercase" placeholder="örn. SM8550, K3LK7K70BM-BGCP, PM8550">
                </x-ui.field>
                <x-ui.field label="Ticari ad / açıklama" for="ce-name" optional class="sm:col-span-1">
                    <input id="ce-name" wire:model="name" class="{{ $input }}" placeholder="Snapdragon 8 Gen 2 · 12 GB LPDDR5X">
                </x-ui.field>
                <x-ui.field label="Üretici" for="ce-man" optional>
                    <input id="ce-man" wire:model="manufacturer" list="ce-makers" class="{{ $input }}" placeholder="Qualcomm, Samsung, SK hynix, Micron…">
                    <datalist id="ce-makers">@foreach (['Qualcomm', 'MediaTek', 'Samsung', 'SK hynix', 'Micron', 'Kioxia', 'Western Digital', 'Apple', 'Google', 'HiSilicon', 'Unisoc', 'Texas Instruments', 'Richtek'] as $m)<option value="{{ $m }}">@endforeach</datalist>
                </x-ui.field>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Teknik özellikler</p>
                    <button type="button" wire:click="addSpec" class="text-xs font-semibold text-indigo-600">+ Özellik</button>
                </div>
                <div class="mt-2 space-y-2">
                    @foreach ($specs as $i => $spec)
                        <div wire:key="spec-{{ $i }}" class="flex gap-2">
                            <input wire:model="specs.{{ $i }}.0" placeholder="Özellik" class="{{ $field }} w-40 flex-none">
                            <input wire:model="specs.{{ $i }}.1" placeholder="Değer" class="{{ $field }} min-w-0 flex-1">
                            <button type="button" wire:click="removeSpec({{ $i }})" class="rounded-lg px-2 text-slate-400 hover:text-rose-600" aria-label="Özelliği sil">✕</button>
                        </div>
                    @endforeach
                </div>
            </div>
            <x-ui.field label="Not" for="ce-notes" optional>
                <textarea id="ce-notes" wire:model="notes" rows="2" class="{{ $input }}" placeholder="Lehim notu, dikkat edilecekler, kaynak…"></textarea>
            </x-ui.field>
            <div class="flex justify-end">
                <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500" wire:loading.attr="disabled" wire:target="save">{{ $cmp ? 'Bilgileri kaydet' : 'Bileşeni oluştur' }}</button>
            </div>
        </form>

        @if (! $cmp)
            <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:bg-white/5 dark:text-slate-300">Bileşeni oluşturduktan sonra bulunduğu cihazları ve uyumlu bileşenleri ekleyebilirsiniz.</p>
        @else
            {{-- Cihazlar --}}
            <section class="{{ $section }}">
                <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-white/5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Bulunduğu cihazlar <span class="font-normal text-slate-500">{{ $cmp->products->count() }}</span></h3>
                </header>
                <div class="space-y-3 p-4">
                    <div class="flex flex-wrap gap-1.5">
                        @forelse ($cmp->products as $p)
                            <span wire:key="cp-{{ $p->pivot->id }}" class="group inline-flex max-w-full items-center gap-1.5 rounded-lg bg-slate-100 py-1 pl-2.5 pr-1 text-xs text-slate-700 dark:bg-white/10 dark:text-slate-200">
                                <span class="truncate" title="{{ $p->brand }} {{ $p->model_name }}">{{ $p->brand }} {{ $p->model_name }}</span>
                                @if ($p->pivot->variant)<span class="rounded bg-white px-1 font-mono text-[10px] dark:bg-white/10">{{ $p->pivot->variant }}</span>@endif
                                <button type="button" wire:click="detachDevice({{ $p->pivot->id }})" class="rounded px-1 text-slate-400 hover:text-rose-600" aria-label="Bağlantıyı kaldır">✕</button>
                            </span>
                        @empty
                            <p class="text-xs text-slate-500">Henüz cihaz bağlanmadı.</p>
                        @endforelse
                    </div>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <div class="flex gap-2">
                            <input type="search" wire:model.live.debounce.250ms="deviceSearch" @focus="open = true" @input="open = true" placeholder="Cihaz ara ve ekle (model, kod adı, model no)…" class="{{ $field }} min-w-0 flex-1">
                            <input wire:model="variant" placeholder="Varyant (8/256)" class="{{ $field }} w-32 flex-none font-mono">
                        </div>
                        <div x-show="open && $wire.deviceSearch.length > 1" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
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
                        <p class="text-xs text-slate-500"><strong class="text-emerald-600">Uyumlu bileşenler sayesinde</strong> şu cihazlarda da kullanılabilir:
                            {{ $reachable->map(fn ($p) => $p->brand.' '.$p->model_name)->implode(', ') }}</p>
                    @endif
                </div>
            </section>

            {{-- Uyumlu bileşenler --}}
            <section class="{{ $section }}">
                <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-white/5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Uyumlu bileşenler <span class="font-normal text-slate-500">{{ $compatibles->count() }}</span></h3>
                    <p class="text-[11px] text-slate-500">İki yönlü: karşı bileşende de görünür</p>
                </header>
                <div class="space-y-3 p-4">
                    @forelse ($compatibles as $o)
                        @php $k = CompatibilityKind::tryFrom($o->pivot->kind) ?? CompatibilityKind::DIRECT; @endphp
                        <div wire:key="cc-{{ $o->id }}" class="group flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2 dark:bg-white/5">
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-black {{ $o->type->badge() }}">{{ $o->type->short() }}</span>
                            <button type="button" wire:click="edit({{ $o->id }})" class="min-w-0 flex-1 text-left">
                                <span class="block truncate font-mono text-sm font-bold text-slate-900 hover:text-indigo-600 dark:text-white">{{ $o->code }}</span>
                                <span class="block truncate text-xs text-slate-500">{{ $o->name }}{{ $o->pivot->note ? ' · '.$o->pivot->note : '' }}</span>
                            </button>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $k->badge() }}" title="{{ $k->description() }}">{{ $k->label() }}</span>
                            <button type="button" wire:click="unlink({{ $o->id }})" class="shrink-0 rounded px-1 text-slate-400 opacity-0 hover:text-rose-600 group-hover:opacity-100" aria-label="Uyumluluğu kaldır">✕</button>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Uyumlu bileşen eklenmedi.</p>
                    @endforelse

                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-white/10" x-data="{ open: false }" @click.outside="open = false">
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Aynı tablodan uyumlu bileşen seç</p>
                        <div class="mt-2 grid gap-2 sm:grid-cols-[1fr_160px]">
                            <div class="relative">
                                <input type="search" wire:model.live.debounce.250ms="compatSearch" @focus="open = true" @input="open = true" placeholder="Kod ya da ad…" class="{{ $input }} font-mono">
                                <div x-show="open && $wire.compatSearch.length > 0" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                                    @forelse ($this->compatResults as $o)
                                        <button type="button" wire:click="link({{ $o->id }})" @click="open = false" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                                            <span class="rounded px-1 text-[9px] font-black {{ $o->type->badge() }}">{{ $o->type->short() }}</span>
                                            <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $o->code }}</span>
                                            <span class="truncate text-xs text-slate-500">{{ $o->name }}</span>
                                        </button>
                                    @empty
                                        <p class="px-3 py-3 text-xs text-slate-500">Eşleşen bileşen yok. Önce o bileşeni oluşturun.</p>
                                    @endforelse
                                </div>
                            </div>
                            <select wire:model="kind" class="{{ $input }} pr-8">
                                @foreach (CompatibilityKind::cases() as $k)<option value="{{ $k->value }}">{{ $k->label() }}</option>@endforeach
                            </select>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <input wire:model="compatNote" placeholder="Not (isteğe bağlı): örn. EMMC firmware yazılmalı" class="{{ $field }} min-w-0 flex-1">
                            <label class="inline-flex shrink-0 items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300"><input type="checkbox" wire:model.live="sameType" class="rounded border-slate-300 text-indigo-600"> Yalnızca aynı tür</label>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Stok --}}
            <section class="{{ $section }} p-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Stoktaki parçalar</h3>
                    <a href="{{ route('parts.index') }}" wire:navigate class="text-xs font-semibold text-indigo-600">Parça ve stok →</a>
                </div>
                @forelse ($cmp->parts as $part)
                    <p class="mt-2 flex items-center justify-between text-sm"><span class="truncate text-slate-700 dark:text-slate-200">{{ $part->name }}</span>
                        <span @class(['shrink-0 rounded-md px-2 py-0.5 font-mono text-xs font-bold', 'bg-rose-100 text-rose-700' => $part->stock <= 0, 'bg-emerald-100 text-emerald-700' => $part->stock > 0])>{{ $part->stock }}</span></p>
                @empty
                    <p class="mt-1 text-xs text-slate-500">Bu bileşene bağlı stok parçası yok. Parça formunda "Çapraz referans bileşeni" seçerek bağlayın.</p>
                @endforelse
            </section>
        @endif
    </div>
</div>
