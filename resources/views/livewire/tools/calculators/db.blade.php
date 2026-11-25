<div x-data="dbCalc" class="space-y-8">
    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-semibold text-slate-900 dark:text-white">Oran → dB</h3>
            <div class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
                <button type="button" @click="mode = 'power'" :class="mode === 'power' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1 text-xs font-semibold">Güç (10 log)</button>
                <button type="button" @click="mode = 'voltage'" :class="mode === 'voltage' ? 'bg-white shadow-sm dark:bg-slate-700' : 'text-slate-500'" class="rounded-md px-3 py-1 text-xs font-semibold">Gerilim (20 log)</button>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <x-calc.input label="Giriş" model="a" />
            <x-calc.input label="Çıkış" model="b" />
            <x-calc.result label="Kazanç" value="$num(ratioDb, 4) + ' dB'" accent />
        </div>
    </section>
    <section class="space-y-4 border-t border-slate-100 pt-6 dark:border-white/5">
        <h3 class="font-semibold text-slate-900 dark:text-white">dBm ↔ güç</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.input label="dBm" model="dbm" unit="dBm" />
                <x-calc.result label="Güç" value="$si(fromDbm, 'W')" />
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <x-calc.input label="mW" model="mw" unit="mW" />
                <x-calc.result label="dBm" value="$num(fromMw, 4) + ' dBm'" />
            </div>
        </div>
        <p class="text-xs text-slate-500">Telefon sinyal gücü referansı: −50 dBm mükemmel, −90 dBm zayıf, −110 dBm sinyal yok sayılır.</p>
    </section>
</div>
