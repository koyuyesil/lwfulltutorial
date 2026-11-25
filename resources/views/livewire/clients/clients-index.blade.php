<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Servis /</span> Müşteriler</h2>
</x-slot>

<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
    x-on:keydown.n.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName) && ! $event.metaKey && ! $event.ctrlKey) { $dispatch('open-drawer', { name: 'client-form' }); Livewire.dispatch('create-client') }">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Müşteriler</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Müşteri kartları, cihazları ve servis geçmişi tek yerde.</p>
        </div>
        <button type="button"
            x-on:click="$dispatch('open-drawer', { name: 'client-form' }); Livewire.dispatch('create-client')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Yeni müşteri
            <kbd class="hidden rounded bg-white/20 px-1.5 text-[10px] sm:inline">N</kbd>
        </button>
    </div>

    <livewire:clients.clients-list />

    <x-drawer name="client-detail" title="Müşteri kartı" width="sm:max-w-xl">
        <livewire:clients.clients-detail />
    </x-drawer>

    <x-drawer name="client-form" title="Müşteri bilgileri" subtitle="Telefon numarası, müşterinin arıza takibinde kimlik doğrulaması için kullanılır.">
        <livewire:clients.clients-form />
    </x-drawer>
</div>
