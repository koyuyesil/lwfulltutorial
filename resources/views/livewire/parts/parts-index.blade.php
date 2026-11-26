@php
    $chip = 'inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold transition';
    $chipOn = 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
    $chipOff = 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10';
    $money = fn ($v) => \App\Models\Ticket::money((float) $v);
@endphp

<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Servis /</span> Parça ve stok</h2>
</x-slot>

<div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8"
    x-on:keydown.n.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName) && ! $event.metaKey && ! $event.ctrlKey) $wire.create()">

    <x-ui.page-header title="Parça ve stok" description="Servis kayıtlarına eklenen parçalar stoktan otomatik düşer; kalem silinirse geri eklenir.">
        <x-ui.button wire:click="create" icon="M12 4.5v15m7.5-7.5h-15">
            Yeni parça <kbd class="hidden rounded bg-white/20 px-1.5 text-[10px] sm:inline">N</kbd>
        </x-ui.button>
    </x-ui.page-header>

    {{-- Özet --}}
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Parça çeşidi</p>
            <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $stats['count'] }}</p>
        </div>
        <button type="button" wire:click="$set('stockFilter', '{{ $stockFilter === 'low' ? '' : 'low' }}')" @class(['rounded-xl p-4 text-left shadow-sm ring-1 transition',
            'bg-amber-50 ring-amber-300 dark:bg-amber-500/10 dark:ring-amber-500/30' => $stats['low'] > 0,
            'bg-white ring-slate-200 dark:bg-slate-800 dark:ring-white/10' => $stats['low'] === 0,
            'ring-2 ring-indigo-500' => $stockFilter === 'low'])>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kritik stok</p>
            <p @class(['mt-1 text-2xl font-black', 'text-amber-600' => $stats['low'] > 0, 'text-slate-900 dark:text-white' => $stats['low'] === 0])>{{ $stats['low'] }}</p>
            <p class="text-xs text-slate-500">{{ $stats['low'] ? 'Sipariş verilmesi gereken parçalar' : 'Tüm stoklar yeterli' }}</p>
        </button>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stok değeri (alış)</p>
            <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $money($stats['value']) }}</p>
        </div>
    </div>

    {{-- Arama ve filtreler --}}
    <div class="flex flex-col gap-3 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center dark:bg-slate-800 dark:ring-white/10">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Parça adı, stok kodu, uyumlu model, tedarikçi…"
                class="block w-full rounded-lg border-0 bg-slate-100 py-2 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-600 dark:bg-white/5 dark:text-white">
        </div>
        <div class="flex gap-1.5">
            @foreach (['' => 'Tümü', 'low' => 'Kritik', 'out' => 'Tükenen', 'ref' => 'Çapraz referans'] as $k => $l)
                <button type="button" wire:click="$set('stockFilter', '{{ $k }}')" @class([$chip, $stockFilter === $k ? $chipOn : $chipOff])>{{ $l }}</button>
            @endforeach
        </div>
    </div>
    @if ($categories->isNotEmpty())
        <x-scroll-row track="gap-1.5 py-0.5" label="Kategori">
            <button type="button" wire:click="$set('category', '')" aria-pressed="{{ $category === '' ? 'true' : 'false' }}" @class([$chip, $category === '' ? $chipOn : $chipOff])>Tüm kategoriler</button>
            @foreach ($categories as $c)
                <button type="button" wire:click="$set('category', @js($c))" aria-pressed="{{ $category === $c ? 'true' : 'false' }}" @class([$chip, $category === $c ? $chipOn : $chipOff])>{{ $c }}</button>
            @endforeach
        </x-scroll-row>
    @endif

    {{-- Liste --}}
    <div wire:loading.class="opacity-60" wire:target="search,category,stockFilter,adjust,gotoPage,nextPage,previousPage">
        @if ($parts->isEmpty())
            <x-ui.empty>
                <p class="text-4xl">🧩</p>
                <p class="mt-3 font-semibold text-slate-800 dark:text-white">{{ $search || $category || $stockFilter ? 'Bu filtreye uyan parça yok' : 'Henüz parça eklemediniz' }}</p>
                <p class="mt-1 text-sm text-slate-500">Ekran, batarya, şarj soketi gibi sık kullandığınız parçaları ekleyin; servis kaydında tek tıkla teklife eklenir.</p>
                <x-ui.button wire:click="create" class="mt-4">Parça ekle</x-ui.button>
            </x-ui.empty>
        @else
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3">Parça</th>
                            <th class="hidden px-4 py-3 md:table-cell">Cihazlar / muadiller</th>
                            <th class="hidden px-4 py-3 text-right sm:table-cell">Alış / satış</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3"><span class="sr-only">İşlemler</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($parts as $part)
                            <tr wire:key="part-{{ $part->id }}" class="group hover:bg-slate-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <button type="button" wire:click="edit({{ $part->id }})" class="flex items-center gap-2 text-left">
                                        @if ($part->type)<span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-black {{ $part->type->badge() }}" title="{{ $part->type->label() }}">{{ $part->type->short() }}</span>@endif
                                        <span class="font-semibold text-slate-900 hover:text-indigo-600 dark:text-white">{{ $part->name }}</span>
                                    </button>
                                    <p class="text-xs text-slate-500">
                                        {{ $part->category ?: 'Kategorisiz' }}
                                        @if ($part->code && $part->code !== $part->name) · <span class="font-mono">{{ $part->code }}</span> @endif
                                        @if ($part->sku) · <x-copy-code :code="$part->sku" text-class="text-slate-500" /> @endif
                                        @if ($part->items_count) · {{ $part->items_count }} kez kullanıldı @endif
                                    </p>
                                </td>
                                {{-- Bulunduğu cihazlar ile muadiller ayrı gösterilir --}}
                                <td class="hidden px-4 py-3 md:table-cell">
                                    @php $compatCount = $part->compatible_to_count + $part->compatible_from_count; @endphp
                                    <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                                        <span @class(['inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 ring-1', 'bg-sky-50 text-sky-800 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-200 dark:ring-sky-500/30' => $part->products_count, 'text-slate-400 ring-slate-200 dark:ring-white/10' => ! $part->products_count])
                                            title="Bulunduğu cihaz sayısı">📱 {{ $part->products_count }} cihaz</span>
                                        <span @class(['inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 ring-1', 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/30' => $compatCount, 'text-slate-400 ring-slate-200 dark:ring-white/10' => ! $compatCount])
                                            title="Muadil parça sayısı">⇄ {{ $compatCount }} muadil</span>
                                    </div>
                                    @if ($part->compatible)<p class="mt-1 line-clamp-1 max-w-56 text-xs text-slate-500" title="{{ $part->compatible }}">{{ $part->compatible }}</p>@endif
                                </td>
                                <td class="hidden whitespace-nowrap px-4 py-3 text-right sm:table-cell">
                                    <p class="font-mono text-xs text-slate-500">{{ $money($part->cost) }}</p>
                                    <p class="font-mono font-semibold text-slate-900 dark:text-white">{{ $money($part->price) }}</p>
                                    @if (! is_null($part->margin())) <p class="text-[11px] text-emerald-600">%{{ number_format($part->margin(), 0) }} kâr</p> @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" wire:click="adjust({{ $part->id }}, -1)" class="grid h-7 w-7 place-items-center rounded-md text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100 dark:ring-white/10 dark:hover:bg-white/10" aria-label="Stok azalt">−</button>
                                        <span @class(['min-w-12 rounded-md px-2 py-1 text-center font-mono text-sm font-black',
                                            'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' => $part->stock <= 0,
                                            'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300' => $part->stock > 0 && $part->isLow(),
                                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' => ! $part->isLow()])>{{ $part->stock }}</span>
                                        <button type="button" wire:click="adjust({{ $part->id }}, 1)" class="grid h-7 w-7 place-items-center rounded-md text-slate-500 ring-1 ring-slate-200 hover:bg-slate-100 dark:ring-white/10 dark:hover:bg-white/10" aria-label="Stok artır">+</button>
                                    </div>
                                    @if ($part->isLow()) <p class="mt-1 text-center text-[11px] font-semibold text-amber-600">min {{ $part->min_stock }}</p> @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <button type="button" wire:click="edit({{ $part->id }})" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-500/10">Düzenle</button>
                                    <button type="button" wire:click="delete({{ $part->id }})" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 opacity-0 hover:bg-rose-50 group-hover:opacity-100 focus:opacity-100 dark:hover:bg-rose-500/10">Sil</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $parts->links() }}</div>
        @endif
    </div>

    {{-- Form --}}
    <x-drawer name="part-form" :title="$editingId ? 'Parçayı düzenle' : 'Yeni parça'" subtitle="Stok ve fiyat bilgileri; çapraz referans türü ve kodu, bulunduğu cihazlar ve muadiller." width="sm:max-w-3xl">
        <div class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 overflow-y-auto">
                <form id="part-form-el" wire:submit="save" class="space-y-4 px-6 py-6">
                    <x-ui.field label="Parça adı" for="p-name" error="form.name">
                        <x-ui.input id="p-name" wire:model="form.name" placeholder="örn. Redmi Note 13 Pro ekran (OLED)" :invalid="$errors->has('form.name')" />
                    </x-ui.field>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Kategori" for="p-cat" optional>
                            <x-ui.input id="p-cat" wire:model="form.category" list="part-categories" placeholder="Ekran, Batarya…" />
                            <datalist id="part-categories">@foreach (\App\Models\Part::CATEGORIES as $c)<option value="{{ $c }}">@endforeach</datalist>
                        </x-ui.field>
                        <x-ui.field label="Stok kodu (SKU)" for="p-sku" optional>
                            <x-ui.input id="p-sku" wire:model="form.sku" class="font-mono" />
                        </x-ui.field>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Alış fiyatı (₺)" for="p-cost" error="form.cost"><x-ui.input id="p-cost" wire:model="form.cost" inputmode="decimal" class="font-mono" :invalid="$errors->has('form.cost')" /></x-ui.field>
                        <x-ui.field label="Satış fiyatı (₺)" for="p-price" error="form.price"><x-ui.input id="p-price" wire:model="form.price" inputmode="decimal" class="font-mono" :invalid="$errors->has('form.price')" /></x-ui.field>
                        <x-ui.field label="Stok" for="p-stock" error="form.stock"><x-ui.input id="p-stock" type="number" wire:model="form.stock" class="font-mono" :invalid="$errors->has('form.stock')" /></x-ui.field>
                        <x-ui.field label="Minimum stok" for="p-min" error="form.min_stock" hint="Bu sayıya inince uyarır · 0: stok takibi yok"><x-ui.input id="p-min" type="number" min="0" wire:model="form.min_stock" class="font-mono" :invalid="$errors->has('form.min_stock')" /></x-ui.field>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Tedarikçi" for="p-sup" optional><x-ui.input id="p-sup" wire:model="form.supplier" /></x-ui.field>
                        <x-ui.field label="Uyumlu modeller (serbest metin)" for="p-comp" optional hint="Arama ve servis kaydında öneri için">
                            <x-ui.input id="p-comp" wire:model="form.compatible" placeholder="garnet, Redmi Note 13 Pro 5G" />
                        </x-ui.field>
                    </div>

                    {{-- Çapraz referans kimliği --}}
                    <fieldset class="rounded-xl bg-amber-50/60 p-4 ring-1 ring-amber-200 dark:bg-amber-500/5 dark:ring-amber-500/20">
                        <legend class="px-1 text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Çapraz referans</legend>
                        <p class="-mt-1 mb-3 text-xs text-slate-500">İsteğe bağlı. Tür ve kod girilirse parça çapraz referansta listelenir; cihazlara ve muadillerine bağlanabilir.</p>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <x-ui.field label="Tür" for="p-type" error="form.type" optional>
                                <select id="p-type" wire:model="form.type" class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                                    <option value="">— Yok —</option>
                                    @foreach (\App\Enums\ComponentType::cases() as $t)<option value="{{ $t->value }}">{{ $t->label() }}</option>@endforeach
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Parça / model kodu" for="p-code" error="form.code" optional>
                                <x-ui.input id="p-code" wire:model="form.code" class="font-mono uppercase placeholder:normal-case" placeholder="SM7435" :invalid="$errors->has('form.code')" />
                            </x-ui.field>
                            <x-ui.field label="Üretici" for="p-man" error="form.manufacturer" optional>
                                <x-ui.input id="p-man" wire:model="form.manufacturer" placeholder="Qualcomm" />
                            </x-ui.field>
                        </div>
                    </fieldset>
                </form>

                @if ($editingId)
                    <div class="border-t border-slate-200 px-6 py-6 dark:border-white/10">
                        <livewire:parts.part-relations :part-id="$editingId" :key="'parts-rel-'.$editingId" />
                    </div>
                @else
                    <p class="mx-6 mb-6 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:bg-white/5 dark:text-slate-300">Parçayı kaydettikten sonra düzenleyerek bulunduğu cihazları ve muadillerini ekleyebilirsiniz.</p>
                @endif
            </div>
            <footer class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-slate-950/50">
                <x-ui.dirty target="form" />
                <x-ui.button variant="ghost" x-on:click="$dispatch('close-drawer', { name: 'part-form' })">Vazgeç</x-ui.button>
                <x-ui.button type="submit" form="part-form-el" loading="save">{{ $editingId ? 'Kaydet' : 'Parça ekle' }}</x-ui.button>
            </footer>
        </div>
    </x-drawer>
</div>
