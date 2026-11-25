<form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
    <div class="relative flex-1 space-y-6 overflow-y-auto px-6 py-6">
        <div wire:loading.flex wire:target="edit" class="absolute inset-0 z-10 items-center justify-center bg-white/70 dark:bg-slate-900/70">
            <x-koyu-svg-loading />
        </div>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:col-span-2">Kişi</legend>
            <x-ui.field label="Ad" for="cf-first" error="form.first_name">
                <x-ui.input id="cf-first" wire:model.blur="form.first_name" autocomplete="given-name" :invalid="$errors->has('form.first_name')" />
            </x-ui.field>
            <x-ui.field label="Soyad" for="cf-last" error="form.last_name">
                <x-ui.input id="cf-last" wire:model.blur="form.last_name" autocomplete="family-name" :invalid="$errors->has('form.last_name')" />
            </x-ui.field>
        </fieldset>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:col-span-2">İletişim</legend>
            <x-ui.field label="Telefon" for="cf-phone" error="form.phone" hint="Örn. 0532 123 45 67">
                <x-ui.input id="cf-phone" type="tel" inputmode="tel" wire:model.live.debounce.400ms="form.phone"
                    x-mask:dynamic="$input.startsWith('+') ? '+99 999 999 99 99' : ($input.startsWith('0') ? '9999 999 99 99' : '999 999 99 99')"
                    autocomplete="tel" placeholder="05__ ___ __ __" :invalid="$errors->has('form.phone')" />
            </x-ui.field>
            <x-ui.field label="E-posta" for="cf-email" error="form.email" optional>
                <x-ui.input id="cf-email" type="email" wire:model.blur="form.email" autocomplete="email" :invalid="$errors->has('form.email')" />
            </x-ui.field>

            @if ($duplicate)
                <div class="flex items-start gap-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-800 ring-1 ring-amber-200 sm:col-span-2 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30">
                    <span class="text-lg leading-none">⚠️</span>
                    <p>Bu telefon numarası <strong>{{ $duplicate['name'] }}</strong> adına zaten kayıtlı. Mükerrer kayıt oluşturmamak için
                        <button type="button" wire:click="edit({{ $duplicate['id'] }})" class="font-semibold underline">mevcut kaydı açın</button>.</p>
                </div>
            @endif
        </fieldset>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:col-span-2">Firma ve adres</legend>
            <x-ui.field label="Firma" for="cf-company" error="form.company" optional hint="Kurumsal müşteriler için" class="sm:col-span-2">
                <x-ui.input id="cf-company" wire:model.blur="form.company" autocomplete="organization" />
            </x-ui.field>
            <x-ui.field label="Adres" for="cf-address" error="form.address" optional class="sm:col-span-2">
                <textarea id="cf-address" wire:model.blur="form.address" rows="3" autocomplete="street-address"
                    class="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10"></textarea>
            </x-ui.field>
        </fieldset>
    </div>

    <footer class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-slate-950/50">
        <button type="button" x-on:click="$dispatch('close-drawer', { name: 'client-form' })"
            class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:text-slate-200 dark:hover:bg-white/10">Vazgeç</button>
        <button type="submit" wire:loading.attr="disabled" wire:target="save"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">
            <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Değişiklikleri kaydet' : 'Müşteriyi ekle' }}</span>
            <span wire:loading wire:target="save">Kaydediliyor…</span>
        </button>
    </footer>
</form>
