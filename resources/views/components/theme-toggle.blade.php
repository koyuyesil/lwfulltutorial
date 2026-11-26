{{-- Tema seçici: Sistem / Açık / Koyu. Seçim tarayıcıda saklanır. --}}
<div x-data="{
        open: false,
        pref: localStorage.getItem('theme') || 'system',
        set(p) { this.pref = p; localStorage.setItem('theme', p); window.applyTheme(p); this.open = false; },
        get isDark() { return document.documentElement.classList.contains('dark'); },
    }" @click.outside="open = false" @keydown.escape="open = false" {{ $attributes->class('relative') }}>
    <button type="button" @click="open = ! open" :aria-expanded="open" aria-haspopup="menu"
        :title="{ system: 'Tema: sisteme göre', light: 'Tema: açık', dark: 'Tema: koyu' }[pref]"
        class="relative grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white">
        <span class="sr-only">Tema seç</span>
        {{-- Güneş (açık) --}}
        <svg x-show="pref === 'light'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
        {{-- Ay (koyu) --}}
        <svg x-show="pref === 'dark'" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
        {{-- Ekran (sistem) --}}
        <svg x-show="pref === 'system'" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" /></svg>
    </button>
    <div x-show="open" x-cloak x-transition.origin.top.right role="menu"
        class="absolute right-0 top-full z-50 mt-2 w-44 overflow-hidden rounded-xl bg-white p-1 text-sm shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
        @foreach (['system' => ['Sisteme göre', 'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25'],
                   'light' => ['Açık', 'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z'],
                   'dark' => ['Koyu', 'M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z']] as $key => [$label, $icon])
            <button type="button" role="menuitemradio" :aria-checked="pref === '{{ $key }}'" @click="set('{{ $key }}')"
                :class="pref === '{{ $key }}' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5'"
                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left font-medium">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                <span class="flex-1">{{ $label }}</span>
                <span x-show="pref === '{{ $key }}'">✓</span>
            </button>
        @endforeach
    </div>
</div>
