<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Araçlar /</span> Board ID kütüphanesi</h2>
</x-slot>

<div>
    <x-tools-tabs />

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        x-on:keydown.n.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName) && ! $event.metaKey && ! $event.ctrlKey) { $dispatch('open-drawer', { name: 'board-form' }); Livewire.dispatch('create-board-id') }">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    Board ID kütüphanesi
                    <span class="rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold uppercase text-amber-950">Premium</span>
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Anakart kimlik dirençleri, HWID'ler ve onarım yöntemleri — canlı PCB görünümüyle.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('settings', 'board-id') }}" wire:navigate
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m-6 3.75 3 3m0 0 3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" /></svg>
                    İçe / dışa aktar
                </a>
                <button type="button"
                    x-on:click="$dispatch('open-drawer', { name: 'board-form' }); Livewire.dispatch('create-board-id')"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Yeni Board ID
                    <kbd class="hidden rounded bg-white/20 px-1.5 text-[10px] sm:inline">N</kbd>
                </button>
            </div>
        </div>

        <livewire:board-ids.board-ids-list />

        <x-drawer name="board-detail" title="Board ID" width="sm:max-w-3xl">
            <livewire:board-ids.board-ids-detail />
        </x-drawer>

        <x-drawer name="board-form" title="Board ID kaydı" subtitle="Değerleri girdikçe sağdaki PCB görünümü canlı güncellenir." width="sm:max-w-6xl">
            <livewire:board-ids.board-ids-form />
        </x-drawer>
    </div>
</div>
