<div class="relative inline-flex items-center gap-1" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
    <a href="{{ route('tickets.index', ['yeni' => 1, 'musteri' => $client->id]) }}" wire:navigate
        class="hidden whitespace-nowrap rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-600 ring-1 ring-indigo-200 transition hover:bg-indigo-50 sm:inline-flex dark:text-indigo-300 dark:ring-indigo-500/30 dark:hover:bg-indigo-500/10"
        title="Bu müşteri için servis kaydı aç">+ Servis</a>
    <button type="button" @click="menu = ! menu" :aria-expanded="menu" aria-haspopup="menu" aria-label="Diğer işlemler"
        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
    </button>
    <div x-show="menu" x-cloak x-transition.origin.top.right role="menu"
        class="absolute right-0 top-full z-20 mt-1 w-48 overflow-hidden rounded-xl bg-white py-1 text-left text-sm shadow-xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-white/10">
        <button type="button" role="menuitem" @click="menu = false; $dispatch('open-drawer', { name: 'client-detail' }); Livewire.dispatch('show-client', { id: {{ $client->id }} })" class="block w-full px-4 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">Müşteri kartı</button>
        <button type="button" role="menuitem" @click="menu = false; $dispatch('open-drawer', { name: 'client-form' }); Livewire.dispatch('edit-client', { id: {{ $client->id }} })"
            class="block w-full px-4 py-2 text-left text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">Düzenle</button>
        <a role="menuitem" href="{{ route('tickets.index', ['yeni' => 1, 'musteri' => $client->id]) }}" wire:navigate class="block px-4 py-2 text-slate-700 hover:bg-slate-50 sm:hidden dark:text-slate-200 dark:hover:bg-white/5">Servis kaydı aç</a>
        <a role="menuitem" href="{{ route('clients.vcard', ['client' => $client, 'version' => auth()->user()->setting('clients.vcard_version')]) }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">vCard indir</a>
        <a role="menuitem" href="tel:{{ preg_replace('/[^0-9+]/', '', $client->phone) }}" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">Ara</a>
        @if ($wa = $client->whatsappNumber())
            <a role="menuitem" href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">WhatsApp</a>
        @endif
        <div class="my-1 border-t border-slate-100 dark:border-white/5"></div>
        <button type="button" role="menuitem" wire:click="delete({{ $client->id }})"
            @click="menu = false" class="block w-full px-4 py-2 text-left text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">Sil</button>
    </div>
</div>
