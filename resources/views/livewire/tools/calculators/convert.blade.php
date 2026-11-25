<div x-data="unitConverter" class="space-y-6">
    <div class="-mx-1 flex gap-1.5 overflow-x-auto px-1 pb-1">
        <template x-for="(c, key) in categories" :key="key">
            <button type="button" @click="setCat(key)"
                :class="cat === key ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-300'"
                class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition" x-text="c.label"></button>
        </template>
    </div>

    <div class="grid items-end gap-3 md:grid-cols-[1fr_auto_1fr]">
        <div class="grid grid-cols-[1fr_auto] gap-2">
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Değer</span>
                <input type="text" inputmode="decimal" x-model="value" autocomplete="off"
                    class="mt-1.5 block w-full rounded-lg border-0 bg-white py-2.5 px-3 font-mono text-lg shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
            </label>
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Birim</span>
                <select x-model="from" class="mt-1.5 block rounded-lg border-0 bg-white py-3 pl-3 pr-8 text-sm font-semibold shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                    <template x-for="u in units" :key="cat + u"><option :value="u" :selected="u === from" x-text="u"></option></template>
                </select>
            </label>
        </div>
        <button type="button" @click="swap()" title="Birimleri değiştir" aria-label="Birimleri değiştir"
            class="mx-auto mb-1 grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 transition hover:rotate-180 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-200">⇄</button>
        <div class="grid grid-cols-[1fr_auto] gap-2">
            <div class="min-w-0 rounded-lg bg-indigo-600 px-4 py-2.5 text-white">
                <p class="text-xs text-indigo-100">Sonuç</p>
                <p class="truncate font-mono text-lg font-black" x-text="$num(convert(to), 8)"></p>
            </div>
            <label class="block">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Birim</span>
                <select x-model="to" class="mt-1.5 block rounded-lg border-0 bg-white py-3 pl-3 pr-8 text-sm font-semibold shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                    <template x-for="u in units" :key="cat + 'to' + u"><option :value="u" :selected="u === to" x-text="u"></option></template>
                </select>
            </label>
        </div>
    </div>

    <div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Tüm birimler</p>
        <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="u in units" :key="cat + 'all' + u">
                <button type="button" @click="copy($num(convert(u), 10) + ' ' + u)"
                    :class="u === to ? 'ring-2 ring-indigo-500' : 'ring-slate-200 dark:ring-white/10'"
                    class="flex items-baseline justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 text-left ring-1 transition hover:bg-slate-100 dark:bg-white/5 dark:hover:bg-white/10">
                    <span class="truncate font-mono text-sm font-semibold text-slate-900 dark:text-white" x-text="$num(convert(u), 8)"></span>
                    <span class="shrink-0 text-xs font-semibold text-slate-500" x-text="u"></span>
                </button>
            </template>
        </div>
        <p class="mt-2 text-xs text-slate-500">Bir değere tıklayınca kopyalanır.</p>
    </div>
</div>
