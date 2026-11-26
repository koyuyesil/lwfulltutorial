@php
    $field = 'block rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
    $th = 'px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500';
    $newT = \App\Enums\ComponentType::tryFrom($newType);
@endphp

<div class="flex min-h-0 flex-1 flex-col">
    @if ($product)
        <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
            <div class="flex items-center gap-4">
                @if ($product->image)<img src="{{ $product->image }}" alt="" class="h-16 w-16 shrink-0 rounded-xl bg-slate-50 object-contain ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">@endif
                <div class="min-w-0">
                    <p class="text-sm text-slate-500">{{ $product->brand }}{{ $product->codename ? ' · '.$product->codename : '' }}{{ $product->model_number ? ' · '.$product->model_number : '' }}</p>
                    <p class="truncate text-xl font-black text-slate-900 dark:text-white">{{ $product->model_name }}</p>
                    @if ($product->chipset)<p class="text-xs text-slate-500">{{ $product->chipset }}</p>@endif
                </div>
            </div>

            {{-- Parça bağla / yeni parça --}}
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                <div x-data="{ open: false }" @click.outside="open = false">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Kütüphaneden parça ekle</p>
                    <div class="relative mt-2 flex gap-2">
                        <input type="search" wire:model.live.debounce.250ms="search" @focus="open = true" @input="open = true" placeholder="Parça kodu, adı ya da stok kodu" class="{{ $field }} min-w-0 flex-1 font-mono">
                        <input wire:model="variant" placeholder="Varyant" class="{{ $field }} w-28 flex-none font-mono">
                        <div x-show="open && $wire.search.length > 0" x-cloak class="absolute left-0 right-0 top-full z-20 mt-1 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                            @forelse ($this->results as $c)
                                <button type="button" wire:click="attach({{ $c->id }})" @click="open = false" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                                    @if ($c->type)<span class="rounded px-1 text-[9px] font-black {{ $c->type->badge() }}">{{ $c->type->short() }}</span>@endif
                                    <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $c->ref() }}</span>
                                    <span class="truncate text-xs text-slate-500">{{ $c->code ? $c->name : '' }}</span>
                                </button>
                            @empty
                                <p class="px-3 py-3 text-xs text-slate-500">Kütüphanede yok - aşağıdaki tür düğmeleriyle yeni parça oluşturun.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-1.5">
                    <span class="text-xs text-slate-500">Yeni parça:</span>
                    @foreach ($types as $t)
                        <button type="button" wire:click="openNew('{{ $t->value }}')" aria-expanded="{{ $newType === $t->value ? 'true' : 'false' }}"
                            @class(['rounded-md px-2 py-0.5 text-[11px] font-bold transition', $t->badge(), 'ring-2 ring-indigo-500 ring-offset-1 dark:ring-offset-slate-900' => $newType === $t->value, 'hover:brightness-95' => $newType !== $t->value])>+ {{ $t->short() }}</button>
                    @endforeach
                </div>

                {{-- Katlanır yeni parça formu (aynı panelde açılır) --}}
                <div x-show="$wire.newType !== ''" x-collapse x-cloak>
                    @if ($newT)
                        <form wire:submit="createPart" class="mt-4 space-y-3 rounded-xl bg-white p-4 ring-1 ring-indigo-200 dark:bg-slate-900 dark:ring-indigo-500/30">
                            <div class="flex items-center justify-between">
                                <p class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-black {{ $newT->badge() }}">{{ $newT->short() }}</span> Yeni {{ $newT->label() }}
                                </p>
                                <button type="button" wire:click="closeNew" class="rounded p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white" aria-label="Formu kapat">✕</button>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <x-ui.field label="Parça / model kodu" for="dp-code" error="newCode">
                                    <x-ui.input id="dp-code" wire:model="newCode" class="font-mono uppercase placeholder:normal-case" placeholder="ör. SM7435" x-init="$el.focus()" :invalid="$errors->has('newCode')" />
                                </x-ui.field>
                                <x-ui.field label="Ticari ad" for="dp-name" optional>
                                    <x-ui.input id="dp-name" wire:model="newName" placeholder="ör. Snapdragon 7s Gen 2" />
                                </x-ui.field>
                                <x-ui.field label="Üretici" for="dp-man" optional>
                                    <x-ui.input id="dp-man" wire:model="newManufacturer" placeholder="Qualcomm, Samsung, Micron" />
                                </x-ui.field>
                                <x-ui.field label="Varyant" for="dp-var" optional>
                                    <x-ui.input id="dp-var" wire:model="newVariant" class="font-mono" placeholder="8/256, Global" />
                                </x-ui.field>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] text-slate-500">Aynı tür ve kodla kayıtlı parça varsa yenisi açılmaz, o bağlanır.</p>
                                <x-ui.button type="submit" size="sm" loading="createPart">Oluştur ve bağla</x-ui.button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Eşleştirme tablosu: cihaz → parça → muadil --}}
            <section class="overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-white/10">
                <header class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-white/5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Telefonun parçaları <span class="font-normal text-slate-500">{{ $product->parts->count() }}</span></h3>
                    <p class="text-[11px] text-slate-500">Parçaya tıklayın: bulunduğu cihazlar ve muadilleri</p>
                </header>
                @if ($product->parts->isEmpty())
                    <p class="px-4 py-8 text-center text-sm text-slate-500">Bu cihaza henüz parça eklenmedi.</p>
                @else
                    <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-white/5">
                        <thead class="bg-slate-50/60 dark:bg-white/[.02]"><tr><th class="{{ $th }}">Tür</th><th class="{{ $th }}">Parça</th><th class="{{ $th }}">Varyant</th><th class="{{ $th }}">Muadiller</th><th class="{{ $th }} text-center!">Stok</th><th class="w-10"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($product->parts as $c)
                                @php $compat = $c->compatibleTo->concat($c->compatibleFrom); @endphp
                                <tr wire:key="dp-{{ $c->pivot->id }}" class="group align-top">
                                    <td class="px-3 py-2.5">
                                        @if ($c->type)<span class="rounded px-1.5 py-0.5 text-[10px] font-black {{ $c->type->badge() }}" title="{{ $c->type->label() }}">{{ $c->type->short() }}</span>@else<span class="text-xs text-slate-400">-</span>@endif
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <button type="button" x-on:click="Livewire.dispatch('open-part', { id: {{ $c->id }} })" class="text-left">
                                            <span class="block font-mono text-sm font-bold text-slate-900 hover:text-indigo-600 dark:text-white">{{ $c->ref() }}</span>
                                            @if ($c->code && $c->name !== $c->code)<span class="block text-xs text-slate-500">{{ $c->name }}{{ $c->manufacturer ? ' · '.$c->manufacturer : '' }}</span>@endif
                                        </button>
                                    </td>
                                    <td class="px-3 py-2.5 font-mono text-xs">{{ $c->pivot->variant ?: '-' }}</td>
                                    <td class="px-3 py-2.5">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($compat as $o)
                                                <button type="button" x-on:click="Livewire.dispatch('open-part', { id: {{ $o->id }} })" class="rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $o->ref() }}</button>
                                            @empty
                                                <span class="text-xs text-slate-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-center"><span @class(['rounded px-1.5 font-mono text-[11px] font-bold', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $c->stock > 0, 'bg-slate-100 text-slate-500 dark:bg-white/10' => $c->stock <= 0])>{{ $c->stock }}</span></td>
                                    <td class="px-2 py-2.5 text-right">
                                        <button type="button" wire:click="detach({{ $c->pivot->id }})" class="rounded px-1.5 text-slate-400 opacity-0 hover:text-rose-600 focus:opacity-100 group-hover:opacity-100" aria-label="Parçayı cihazdan kaldır">✕</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </div>
    @endif
</div>
