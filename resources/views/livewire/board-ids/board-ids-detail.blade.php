<div class="relative flex min-h-0 flex-1 flex-col">
    <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/70 dark:bg-slate-900/70"><x-koyu-svg-loading /></div>

    @if ($board)
        @php
            $props = [
                'type' => $board->divider_type?->value ?? 'adc',
                'chip' => $board->chip?->value ?? 'pmic',
                'vref' => $board->vref,
                'r1' => $board->r1(),
                'r2' => $board->r2(),
                'name' => $board->build_name,
            ];
            $type = $board->divider_type ?? \App\Enums\DividerType::ADC;
        @endphp
        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
            <div class="flex items-start gap-4">
                @if ($board->product?->image)
                    <img src="{{ $board->product->image }}" alt="" class="h-16 w-16 shrink-0 rounded-xl bg-slate-50 object-contain ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                @endif
                <div class="min-w-0">
                    <p class="text-sm text-slate-500">{{ $board->product?->brand }} {{ $board->product?->model_name }}{{ $board->product?->codename ? ' · ' . $board->product->codename : '' }}</p>
                    <p class="font-mono text-2xl font-black text-slate-900 dark:text-white">{{ $board->build_name }}</p>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        <span class="rounded px-2 py-0.5 text-xs font-bold {{ $type->badge() }}">{{ $type->label() }}</span>
                        @foreach ($board->methods() as $m)
                            <span class="rounded px-2 py-0.5 text-xs font-medium {{ $m->color() }}">{{ $m->label() }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div wire:key="pcb-{{ $board->id }}-{{ $board->updated_at?->timestamp }}">
                <x-board-pcb :source="'() => (' . json_encode($props) . ')'" />
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                    <dt class="text-xs text-slate-500">Seri üretim HWID</dt>
                    <dd class="mt-0.5 break-all font-mono font-semibold text-slate-900 dark:text-white">{{ $board->mass_production_hwid ?: '—' }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                    <dt class="text-xs text-slate-500">Ön üretim HWID</dt>
                    <dd class="mt-0.5 break-all font-mono font-semibold text-slate-900 dark:text-white">{{ $board->pre_production_hwid ?: '—' }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                    <dt class="text-xs text-slate-500">R1 {{ $type->internalR1() ? '(dahili)' : '' }}</dt>
                    <dd class="mt-0.5 font-mono font-semibold text-slate-900 dark:text-white">{{ \App\Support\Si::format($board->r1()) }} <span class="text-xs text-slate-500">{{ $board->r1() !== null && ! $type->internalR1() ? '· SMD ' . \App\Support\Si::smdCode($board->r1()) : '' }}</span></dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                    <dt class="text-xs text-slate-500">R2</dt>
                    <dd class="mt-0.5 font-mono font-semibold text-slate-900 dark:text-white">{{ \App\Support\Si::format($board->r2()) }} <span class="text-xs text-slate-500">{{ $board->r2() !== null ? '· SMD ' . \App\Support\Si::smdCode($board->r2()) : '' }}</span></dd>
                </div>
            </dl>

            @if ($board->description)
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Notlar</h3>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $board->description }}</p>
                </div>
            @endif
        </div>

        <footer class="flex items-center gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-slate-950/50">
            <button type="button" x-on:click="$dispatch('close-drawer', { name: 'board-detail' }); $dispatch('open-drawer', { name: 'board-form' }); Livewire.dispatch('edit-board-id', { id: {{ $board->id }} })"
                class="ms-auto rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Düzenle</button>
        </footer>
    @else
        <div class="flex-1"></div>
    @endif
</div>
