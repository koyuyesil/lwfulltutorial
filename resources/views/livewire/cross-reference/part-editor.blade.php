@use('App\Enums\ComponentType')
@php
    $input = 'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
    $field = 'block rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
@endphp

<div class="flex min-h-0 flex-1 flex-col">
    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
        {{-- Bilgiler --}}
        <form wire:submit="save" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-[200px_1fr]">
                <x-ui.field label="Tür" for="pe-type" error="type">
                    <select id="pe-type" wire:model.live="type" class="{{ $input }} pr-8">
                        @foreach (ComponentType::cases() as $t)<option value="{{ $t->value }}">{{ $t->label() }}</option>@endforeach
                    </select>
                </x-ui.field>
                <x-ui.field label="Parça / model kodu" for="pe-code" error="code" hint="Entegre üzerindeki ya da datasheet'teki kod">
                    <x-ui.input id="pe-code" wire:model="code" class="font-mono uppercase placeholder:normal-case" placeholder="ör. SM8550, K3LK7K70BM-BGCP, PM8550" :invalid="$errors->has('code')" />
                </x-ui.field>
                <x-ui.field label="Ticari ad / açıklama" for="pe-name" optional>
                    <x-ui.input id="pe-name" wire:model="name" placeholder="Snapdragon 8 Gen 2 · 12 GB LPDDR5X" />
                </x-ui.field>
                <x-ui.field label="Üretici" for="pe-man" optional>
                    <x-ui.input id="pe-man" wire:model="manufacturer" list="pe-makers" placeholder="Qualcomm, Samsung, SK hynix, Micron" />
                    <datalist id="pe-makers">@foreach (['Qualcomm', 'MediaTek', 'Samsung', 'SK hynix', 'Micron', 'Kioxia', 'Western Digital', 'Apple', 'Google', 'HiSilicon', 'Unisoc', 'Texas Instruments', 'Richtek'] as $m)<option value="{{ $m }}">@endforeach</datalist>
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
            <x-ui.field label="Not" for="pe-notes" optional>
                <textarea id="pe-notes" wire:model="notes" rows="2" class="{{ $input }}" placeholder="Lehim notu, dikkat edilecekler, kaynak"></textarea>
            </x-ui.field>
            <div class="flex items-center justify-end gap-3">
                <x-ui.dirty target="type, code, name, manufacturer, notes, specs" />
                <x-ui.button type="submit" loading="save">{{ $part ? 'Bilgileri kaydet' : 'Parçayı oluştur' }}</x-ui.button>
            </div>
        </form>

        @if (! $part)
            <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:bg-white/5 dark:text-slate-300">Parçayı oluşturduktan sonra bulunduğu cihazları ve muadillerini ekleyebilirsiniz.</p>
        @else
            {{-- Stok özeti --}}
            <div class="flex flex-wrap items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm dark:bg-white/5">
                <span class="text-slate-600 dark:text-slate-300">Stok</span>
                <span @class(['rounded-md px-2 py-0.5 font-mono text-sm font-black', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $part->stock > 0, 'bg-slate-200 text-slate-600 dark:bg-white/10 dark:text-slate-300' => $part->stock <= 0])>{{ $part->stock }}</span>
                @if ((float) $part->price > 0)<span class="font-mono text-slate-600 dark:text-slate-300">{{ \App\Models\Ticket::money((float) $part->price) }}</span>@endif
                <a href="{{ route('parts.index', ['q' => $part->code ?: $part->name]) }}" wire:navigate class="ms-auto text-xs font-semibold text-indigo-600">Fiyat ve stoku düzenle →</a>
            </div>

            <livewire:parts.part-relations :part-id="$part->id" :key="'pe-rel-'.$part->id" />
        @endif
    </div>
</div>
