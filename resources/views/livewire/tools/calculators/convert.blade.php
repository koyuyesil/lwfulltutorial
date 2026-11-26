<div x-data="unitConverter" class="space-y-5">
    <x-scroll-row track="gap-1.5 py-0.5" label="Birim kategorisi">
        <template x-for="(c, key) in categories" :key="key">
            <button type="button" @click="setCat(key)" :aria-pressed="cat === key ? 'true' : 'false'"
                :class="cat === key ? 'bg-slate-900 text-white shadow-md dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300'"
                class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition" x-text="c.label"></button>
        </template>
    </x-scroll-row>

    <x-calc.stage>
        <div class="grid items-center gap-4 md:grid-cols-[1fr_auto_1fr]">
            <div class="grid grid-cols-[1fr_auto] gap-2">
                <label class="block">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Değer</span>
                    <input type="text" inputmode="decimal" x-model="value" autocomplete="off"
                        class="mt-1 block w-full rounded-xl border-0 bg-white/10 px-4 py-3 font-mono text-2xl font-black text-white ring-1 ring-inset ring-white/15 focus:ring-2 focus:ring-cyan-400">
                </label>
                <label class="block">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Birim</span>
                    <select x-model="from" class="mt-1 block rounded-xl border-0 bg-white/10 py-4 pl-3 pr-8 text-sm font-semibold text-white ring-1 ring-inset ring-white/15 focus:ring-2 focus:ring-cyan-400 [&>option]:text-slate-900">
                        <template x-for="u in units" :key="cat + u"><option :value="u" :selected="u === from" x-text="u"></option></template>
                    </select>
                </label>
            </div>
            <button type="button" @click="swap()" title="Birimleri değiştir" aria-label="Birimleri değiştir"
                class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-linear-to-br from-cyan-400 to-indigo-500 text-xl font-black text-white shadow-lg shadow-indigo-500/30 transition hover:rotate-180">⇄</button>
            <div class="grid grid-cols-[1fr_auto] gap-2">
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Sonuç</span>
                    <button type="button" @click="copy($num(convert(to), 10) + ' ' + to)" title="Kopyala"
                        class="mt-1 block w-full truncate rounded-xl bg-linear-to-br from-indigo-500 to-violet-600 px-4 py-3 text-left font-mono text-2xl font-black text-white shadow-lg shadow-indigo-500/30" x-text="$num(convert(to), 8)"></button>
                </div>
                <label class="block">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Birim</span>
                    <select x-model="to" class="mt-1 block rounded-xl border-0 bg-white/10 py-4 pl-3 pr-8 text-sm font-semibold text-white ring-1 ring-inset ring-white/15 focus:ring-2 focus:ring-cyan-400 [&>option]:text-slate-900">
                        <template x-for="u in units" :key="cat + 'to' + u"><option :value="u" :selected="u === to" x-text="u"></option></template>
                    </select>
                </label>
            </div>
        </div>
    </x-calc.stage>

    <div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Tüm birimler · tıklayınca kopyalanır</p>
        <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="u in units" :key="cat + 'all' + u">
                <button type="button" @click="copy($num(convert(u), 10) + ' ' + u)"
                    :class="u === to ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'bg-slate-50 ring-slate-200 dark:bg-white/5 dark:ring-white/10'"
                    class="flex items-baseline justify-between gap-3 rounded-lg px-3 py-2 text-left ring-1 transition hover:bg-slate-100 dark:hover:bg-white/10">
                    <span class="truncate font-mono text-sm font-semibold text-slate-900 dark:text-white" x-text="$num(convert(u), 8)"></span>
                    <span class="shrink-0 text-xs font-semibold text-slate-500" x-text="u"></span>
                </button>
            </template>
        </div>
    </div>
</div>
