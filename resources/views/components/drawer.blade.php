{{--
    Sağdan açılan panel.
    Açmak:  $dispatch('open-drawer', { name: '...' })  ya da Livewire: $this->dispatch('open-drawer', name: '...')
    Kapatmak: $dispatch('close-drawer', { name: '...' })
--}}
@props(['name', 'title' => '', 'subtitle' => null, 'width' => 'sm:max-w-2xl', 'initial' => false])

<div x-data="{ open: @js((bool) $initial) }"
    x-on:open-drawer.window="if ($event.detail?.name === @js($name)) open = true"
    x-on:close-drawer.window="if (! $event.detail?.name || $event.detail.name === @js($name)) open = false"
    x-on:keydown.escape.window="open = false"
    {{ $attributes }}>
    <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="{{ $title }}">
        <div x-show="open" x-transition.opacity.duration.200ms @click="open = false" class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]"></div>

        <div x-show="open"
            x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            x-trap.inert.noscroll="open"
            class="absolute inset-y-0 right-0 flex w-full {{ $width }} flex-col bg-white shadow-2xl dark:bg-slate-900">
            <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-4 dark:border-white/10">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h2>
                    @if ($subtitle)
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                    @endif
                </div>
                <button type="button" @click="open = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/10" aria-label="Kapat">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </header>
            <div class="relative flex min-h-0 flex-1 flex-col">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
