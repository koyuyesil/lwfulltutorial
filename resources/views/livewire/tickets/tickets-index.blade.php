<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Servis /</span> Servis kayıtları</h2>
</x-slot>

<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
    x-on:keydown.n.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName) && ! $event.metaKey && ! $event.ctrlKey) { $dispatch('open-drawer', { name: 'ticket-form' }); Livewire.dispatch('create-ticket') }">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Servis kayıtları</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kabulden teslimata tüm işler; en acil hedef her zaman en üstte.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('tickets.board') }}" wire:navigate
            class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" /></svg>
            İş panosu
        </a>
        <button type="button"
            x-on:click="$dispatch('open-drawer', { name: 'ticket-form' }); Livewire.dispatch('create-ticket')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Yeni servis kaydı
            <kbd class="hidden rounded bg-white/20 px-1.5 text-[10px] sm:inline">N</kbd>
        </button>
        </div>
    </div>

    <livewire:tickets.tickets-list />

    <x-drawer name="ticket-form" title="Yeni servis kaydı" subtitle="Müşteri → cihaz → arıza. Kayıtlı bilgiler otomatik tamamlanır." width="sm:max-w-3xl" :initial="$openNew">
        <livewire:tickets.tickets-form />
    </x-drawer>
</div>
