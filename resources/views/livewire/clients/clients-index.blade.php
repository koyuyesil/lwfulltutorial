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
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('settings', 'musteriler') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-white/5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m-6 3.75 3 3m0 0 3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" /></svg>
                İçe / dışa aktar
            </a>
            <a href="{{ route('settings', 'musteriler') }}" wire:navigate title="Müşteri ayarları" aria-label="Müşteri ayarları"
                class="rounded-lg bg-white p-2.5 text-slate-500 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 hover:text-slate-800 dark:bg-slate-800 dark:text-slate-300 dark:ring-white/10">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
            </a>
            <button type="button"
                x-on:click="$dispatch('open-drawer', { name: 'client-form' }); Livewire.dispatch('create-client')"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Yeni müşteri
                <kbd class="hidden rounded bg-white/20 px-1.5 text-[10px] sm:inline">N</kbd>
            </button>
        </div>
    </div>

    <livewire:clients.clients-list />

    <x-drawer name="client-detail" title="Müşteri kartı" width="sm:max-w-xl">
        <livewire:clients.clients-detail />
    </x-drawer>

    <x-drawer name="client-form" title="Müşteri bilgileri" subtitle="Telefon numarası, müşterinin arıza takibinde kimlik doğrulaması için kullanılır.">
        <livewire:clients.clients-form />
    </x-drawer>
</div>
