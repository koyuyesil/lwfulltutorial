@php
    $field = 'block rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
    $input = 'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
@endphp

<div class="flex min-h-0 flex-1 flex-col">
    @if ($product)
        <div class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
            <div class="flex items-center gap-4">
                @if ($product->image)<img src="{{ $product->image }}" alt="" class="h-16 w-16 shrink-0 rounded-xl bg-slate-50 object-contain ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">@endif
                <div class="min-w-0">
                    <p class="text-sm text-slate-500">{{ $product->brand }}{{ $product->codename ? ' · '.$product->codename : '' }}{{ $product->model_number ? ' · '.$product->model_number : '' }}</p>
                    <p class="truncate text-xl font-black text-slate-900 dark:text-white">{{ $product->model_name }}</p>
                </div>
            </div>

            {{-- Bileşen bağla --}}
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10" x-data="{ open: false }" @click.outside="open = false">
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Kütüphaneden bileşen ekle</p>
                <div class="relative mt-2 flex gap-2">
                    <input type="search" wire:model.live.debounce.250ms="search" @focus="open = true" @input="open = true" placeholder="SoC / RAM / PMIC kodu…" class="{{ $field }} min-w-0 flex-1 font-mono">
                    <input wire:model="variant" placeholder="Varyant" class="{{ $field }} w-28 flex-none font-mono">
                    <div x-show="open && $wire.search.length > 0" x-cloak class="absolute left-0 right-0 top-full z-20 mt-1 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                        @forelse ($this->results as $c)
                            <button type="button" wire:click="attach({{ $c->id }})" @click="open = false" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                                <span class="rounded px-1 text-[9px] font-black {{ $c->type->badge() }}">{{ $c->type->short() }}</span>
                                <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $c->code }}</span>
                                <span class="truncate text-xs text-slate-500">{{ $c->name }}</span>
                            </button>
                        @empty
                            <p class="px-3 py-3 text-xs text-slate-500">Kütüphanede yok — aşağıdan yeni bileşen oluşturun.</p>
                        @endforelse
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <span class="text-xs text-slate-500">Yeni:</span>
                    @foreach ($types as $t)
                        <button type="button" x-on:click="Livewire.dispatch('create-component', { type: '{{ $t->value }}', productId: {{ $product->id }} })"
                            class="rounded-md px-2 py-0.5 text-[11px] font-bold {{ $t->badge() }} hover:brightness-95">+ {{ $t->short() }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Türe göre bileşenler --}}
            @forelse ($types as $t)
                @if ($groups->has($t->value))
                    <section>
                        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500"><span class="rounded px-1.5 py-0.5 text-[10px] {{ $t->badge() }}">{{ $t->short() }}</span>{{ $t->label() }}</h3>
                        <div class="mt-2 space-y-2">
                            @foreach ($groups[$t->value] as $c)
                                @php $compat = $c->compatibleTo->concat($c->compatibleFrom); @endphp
                                <div wire:key="dc-{{ $c->pivot->id }}" class="group rounded-xl bg-white p-3 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                                    <div class="flex items-center gap-3">
                                        <button type="button" x-on:click="Livewire.dispatch('edit-component', { id: {{ $c->id }} })" class="min-w-0 flex-1 text-left">
                                            <span class="block truncate font-mono text-sm font-bold text-slate-900 hover:text-indigo-600 dark:text-white">{{ $c->code }}</span>
                                            <span class="block truncate text-xs text-slate-500">{{ $c->name }}{{ $c->manufacturer ? ' · '.$c->manufacturer : '' }}</span>
                                        </button>
                                        @if ($c->pivot->variant)<span class="shrink-0 rounded bg-slate-100 px-1.5 font-mono text-[11px] dark:bg-white/10">{{ $c->pivot->variant }}</span>@endif
                                        <button type="button" wire:click="detach({{ $c->pivot->id }})" class="shrink-0 rounded px-1 text-slate-400 opacity-0 hover:text-rose-600 group-hover:opacity-100" aria-label="Kaldır">✕</button>
                                    </div>
                                    @if ($compat->isNotEmpty())
                                        <p class="mt-2 flex flex-wrap items-center gap-1 text-[11px] text-slate-500">
                                            <span>Uyumlu:</span>
                                            @foreach ($compat as $o)
                                                <button type="button" x-on:click="Livewire.dispatch('edit-component', { id: {{ $o->id }} })" class="rounded bg-emerald-50 px-1.5 py-0.5 font-mono font-semibold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $o->code }}</button>
                                            @endforeach
                                        </p>
                                    @endif
                                    @if ($c->specs)
                                        <p class="mt-1 text-[11px] text-slate-500">{{ collect($c->specs)->map(fn ($v, $k) => "$k: $v")->implode(' · ') }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @empty
            @endforelse
            @if ($groups->isEmpty())
                <p class="rounded-xl border-2 border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-white/10">Bu cihaza henüz bileşen eklenmedi.</p>
            @endif
        </div>
    @endif
</div>
