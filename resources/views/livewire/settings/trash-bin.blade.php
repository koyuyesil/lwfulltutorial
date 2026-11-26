<x-ui.card title="Çöp kutusu" icon="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
    tone="rose" :description="'Silinen kayıtlar ' . $retention . ' gün burada bekler; sonra kendiliğinden kalıcı olarak silinir.'" :padding="false" class="relative">
    <x-slot:actions>
        @if ($total)
            <x-ui.button variant="danger" size="sm" wire:click="emptyTrash" loading="emptyTrash"
                wire:confirm="{{ $type === '' ? 'Çöp kutusundaki tüm kayıtlar' : 'Bu türdeki tüm kayıtlar' }} kalıcı olarak silinecek. Bu işlem geri alınamaz. Emin misiniz?">
                Çöpü boşalt
            </x-ui.button>
        @endif
    </x-slot:actions>

    <x-ui.loading target="type, restore, forceDelete, emptyTrash" />

    {{-- Tür filtresi --}}
    <div class="border-b border-slate-100 px-5 py-3 dark:border-white/5">
        <x-scroll-row track="gap-1.5">
            <button type="button" wire:click="$set('type', '')" @if ($type === '') aria-pressed="true" @endif
                @class(['shrink-0 rounded-full px-3 py-1 text-xs font-semibold transition',
                    'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => $type === '',
                    'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300' => $type !== ''])>
                Tümü <span class="opacity-60">{{ $total }}</span>
            </button>
            @foreach (\App\Support\Trash::TYPES as $key => [, $label, $emoji])
                @if ($counts[$key])
                    <button type="button" wire:click="$set('type', '{{ $key }}')" @if ($type === $key) aria-pressed="true" @endif
                        @class(['shrink-0 rounded-full px-3 py-1 text-xs font-semibold transition',
                            'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => $type === $key,
                            'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300' => $type !== $key])>
                        {{ $emoji }} {{ $label }} <span class="opacity-60">{{ $counts[$key] }}</span>
                    </button>
                @endif
            @endforeach
        </x-scroll-row>
    </div>

    @if ($items->isEmpty())
        <div class="px-6 py-14 text-center">
            <p class="text-4xl" aria-hidden="true">🗑️</p>
            <p class="mt-3 font-semibold text-slate-800 dark:text-white">Çöp kutusu boş</p>
            <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Sildiğiniz servis kayıtları, müşteriler, parçalar ve diğer kayıtlar {{ $retention }} gün boyunca burada saklanır.</p>
        </div>
    @else
        <ul class="divide-y divide-slate-100 dark:divide-white/5">
            @foreach ($items as $item)
                <li wire:key="trash-{{ $item['type'] }}-{{ $item['id'] }}" class="flex flex-wrap items-center gap-3 px-5 py-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-lg dark:bg-white/5" aria-hidden="true">{{ \App\Support\Trash::TYPES[$item['type']][2] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $item['title'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ \App\Support\Trash::label($item['type']) }} · {{ $item['deleted_at']->diffInSeconds(now()) < 60 ? 'az önce' : $item['deleted_at']->diffForHumans() }} silindi
                            · <span title="{{ $item['purge_at']->translatedFormat('d F Y') }}">{{ max(0, (int) ceil(now()->diffInDays($item['purge_at'], false))) }} gün sonra kalıcı silinir</span>
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <x-ui.button size="sm" variant="secondary" wire:click="restore('{{ $item['type'] }}', {{ $item['id'] }})"
                            icon="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3">Geri al</x-ui.button>
                        <x-ui.button size="sm" variant="danger-ghost"
                            wire:click="forceDelete('{{ $item['type'] }}', {{ $item['id'] }})"
                            wire:confirm="{{ $item['title'] }} kalıcı olarak silinecek. Bu işlem geri alınamaz. Emin misiniz?">Kalıcı sil</x-ui.button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
