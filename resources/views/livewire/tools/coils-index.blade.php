<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Araçlar /</span> Bobin hesaplayıcı</h2>
</x-slot>

<div>
    <x-tools-tabs />

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8" x-data="coilsApp(@js($initial))" wire:ignore>
        <section class="relative isolate overflow-hidden rounded-3xl bg-slate-950 px-6 py-7 text-white shadow-xl ring-1 ring-white/10 sm:px-10">
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-20 [background-image:linear-gradient(#334155_1px,transparent_1px),linear-gradient(90deg,#334155_1px,transparent_1px)] [background-size:28px_28px]"></div>
            <div class="pointer-events-none absolute -right-20 -top-24 -z-10 h-72 w-72 rounded-full bg-orange-500/30 blur-3xl"></div>
            <p class="text-xs font-bold uppercase tracking-[.25em] text-orange-300">Atölye laboratuvarı</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Bobin hesaplayıcı</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">Endüktanstan sarım sayısına ya da sarımdan endüktansa; tel uzunluğu, DC direnç ve LC rezonansı. Sarım siz yazarken 3D olarak döner. Kutularda − / + basılı tutulunca hızlanır, ↑ / ↓ tuşları da çalışır.</p>

            <nav class="mt-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-4" aria-label="Bobin türü" role="tablist">
                @foreach ($tabs as $key => [$title, $desc, $icon])
                    <button type="button" role="tab" @click="go('{{ $key }}')" :aria-selected="tab === '{{ $key }}' ? 'true' : 'false'"
                        :class="tab === '{{ $key }}' ? 'bg-white text-slate-900 shadow-lg' : 'bg-white/5 text-slate-200 ring-1 ring-white/10 hover:bg-white/10'"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-left transition">
                        <span class="text-2xl" aria-hidden="true">{{ $icon }}</span>
                        <span class="min-w-0">
                            <span class="block font-bold">{{ $title }}</span>
                            <span class="block truncate text-xs opacity-70">{{ $desc }}</span>
                        </span>
                    </button>
                @endforeach
            </nav>
        </section>

        @foreach ($tabs as $key => [$title, $desc])
            <article x-show="tab === '{{ $key }}'" @if ($key !== $initial) x-cloak @endif
                class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6 dark:bg-slate-800/80 dark:ring-white/10" role="tabpanel" aria-label="{{ $title }}">
                <header class="mb-5">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }} bobin</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $desc }}</p>
                </header>
                @include('livewire.tools.coils.'.$key)
            </article>
        @endforeach
    </div>
</div>
