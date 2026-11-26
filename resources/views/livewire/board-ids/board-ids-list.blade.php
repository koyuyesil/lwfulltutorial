@use('App\Enums\DividerType')
@use('App\Support\Si')
@php
    $chip = 'inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition';
    $chipOff = 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/5';
    $chipOn = 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
    $isFiltered = $search !== '' || $type !== '';
@endphp

<div class="mt-6 space-y-4">
    <div class="flex flex-wrap items-center gap-3 rounded-xl bg-white p-3 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
        <x-ui.search wire:model.live.debounce.300ms="search" placeholder="Model, kod adı, build, HWID…" />
        <div class="flex flex-wrap gap-1.5">
            <button type="button" wire:click="$set('type', '')" @class([$chip, $type === '' ? $chipOn : $chipOff])>Tümü</button>
            @foreach (DividerType::cases() as $t)
                <button type="button" wire:click="$set('type', '{{ $t->value }}')" @class([$chip, $type === $t->value ? $chipOn : $chipOff])>
                    {{ $t->label() }} <span class="opacity-60">{{ $typeCounts[$t->value] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div wire:loading.class="opacity-60" wire:target="search,type,gotoPage,nextPage,previousPage">
        @if ($boards->isEmpty())
            <x-ui.empty>
                <p class="text-4xl">🔌</p>
                <p class="mt-3 font-semibold text-slate-800 dark:text-white">{{ $isFiltered ? 'Bu filtreye uyan kayıt yok' : 'Kütüphane boş' }}</p>
                <p class="mt-1 text-sm text-slate-500">Anakart kimlik dirençlerini kaydedin; onarımda doğru değeri saniyeler içinde bulun.</p>
                @unless ($isFiltered)
                    <x-ui.button x-on:click="$dispatch('open-drawer', { name: 'board-form' }); Livewire.dispatch('create-board-id')" class="mt-4">Yeni Board ID</x-ui.button>
                @endunless
            </x-ui.empty>
        @else
            <div data-list class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($boards as $board)
                    @php
                        $bt = $board->divider_type ?? DividerType::ADC;
                        $props = ['type' => $bt->value, 'chip' => $board->chip?->value ?? 'pmic', 'vref' => $board->vref, 'r1' => $board->r1(), 'r2' => $board->r2(), 'r1_org' => $board->r1Original(), 'r2_org' => $board->r2Original(), 'name' => $board->build_name];
                        $vout = $board->vout();
                    @endphp
                    <article wire:key="board-{{ $board->id }}-{{ $board->updated_at?->timestamp }}"
                        class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-slate-800 dark:ring-white/10">
                        <button type="button" class="block" title="Detayı aç"
                            x-on:click="$dispatch('open-drawer', { name: 'board-detail' }); Livewire.dispatch('show-board-id', { id: {{ $board->id }} })">
                            <x-board-pcb compact :source="'() => (' . json_encode($props) . ')'" class="pointer-events-none" />
                        </button>
                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-xs text-slate-500">{{ $board->product?->brand }} {{ $board->product?->model_name }}{{ $board->product?->codename ? ' · ' . $board->product->codename : '' }}</p>
                                    <p class="truncate font-mono text-base font-bold text-slate-900 dark:text-white">{{ $board->build_name }}</p>
                                </div>
                                <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $bt->badge() }}">{{ $bt->short() }}</span>
                            </div>

                            <dl class="mt-3 grid grid-cols-3 gap-2 text-center">
                                <div class="rounded-lg bg-slate-50 py-1.5 dark:bg-white/5">
                                    <dt class="text-[10px] font-semibold uppercase text-slate-500">R1{{ $bt->internalR1() ? ' · iç' : '' }}</dt>
                                    <dd class="font-mono text-sm font-bold text-slate-900 dark:text-white">{{ $board->r1() === null ? 'NC' : Si::format($board->r1()) }}</dd>
                                </div>
                                <div class="rounded-lg bg-slate-50 py-1.5 dark:bg-white/5">
                                    <dt class="text-[10px] font-semibold uppercase text-slate-500">R2</dt>
                                    <dd class="font-mono text-sm font-bold text-slate-900 dark:text-white">{{ $board->r2() === null ? 'NC' : Si::format($board->r2()) }}</dd>
                                </div>
                                <div class="rounded-lg bg-amber-50 py-1.5 dark:bg-amber-500/10">
                                    <dt class="text-[10px] font-semibold uppercase text-amber-700 dark:text-amber-300">{{ $bt === DividerType::LOGIC ? 'Seviye' : 'Vout' }}</dt>
                                    <dd class="font-mono text-sm font-bold text-amber-800 dark:text-amber-200">
                                        @if ($bt === DividerType::LOGIC)
                                            {{ $board->logicLevel() === null ? '?' : $board->logicLevel() }}
                                        @else
                                            {{ $vout === null ? '—' : Si::format($vout, 'V') }}
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            @if ($board->mass_production_hwid || $board->pre_production_hwid)
                                <p class="mt-3 truncate font-mono text-[11px] text-slate-500" title="MP: {{ $board->mass_production_hwid }} · PP: {{ $board->pre_production_hwid }}">
                                    MP {{ $board->mass_production_hwid ?: '—' }} · PP {{ $board->pre_production_hwid ?: '—' }}
                                </p>
                            @endif

                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach ($board->methods() as $m)
                                    <span class="rounded px-1.5 py-0.5 text-[11px] font-medium {{ $m->color() }}">{{ $m->label() }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex border-t border-slate-100 text-xs font-semibold dark:border-white/5">
                            <button type="button" x-on:click="$dispatch('open-drawer', { name: 'board-detail' }); Livewire.dispatch('show-board-id', { id: {{ $board->id }} })"
                                class="flex-1 py-2.5 text-slate-600 transition hover:bg-slate-50 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-white/5">Detay · 3D</button>
                            <button type="button" x-on:click="$dispatch('open-drawer', { name: 'board-form' }); Livewire.dispatch('edit-board-id', { id: {{ $board->id }} })"
                                class="flex-1 border-s border-slate-100 py-2.5 text-slate-600 transition hover:bg-slate-50 hover:text-indigo-600 dark:border-white/5 dark:text-slate-300 dark:hover:bg-white/5">Düzenle</button>
                            <button type="button" wire:click="delete({{ $board->id }})" class="flex-1 border-s border-slate-100 py-2.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:border-white/5 dark:hover:bg-rose-500/10">Sil</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div>{{ $boards->links() }}</div>
</div>
