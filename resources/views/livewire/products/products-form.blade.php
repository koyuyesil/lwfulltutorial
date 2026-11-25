<form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
    <div class="relative flex-1 space-y-6 overflow-y-auto px-6 py-6">
        <div wire:loading.flex wire:target="edit" class="absolute inset-0 z-10 items-center justify-center bg-white/70 dark:bg-slate-900/70">
            <x-koyu-svg-loading />
        </div>

        <datalist id="pf-brands">
            @foreach ($this->suggestions['brands'] as $value)
                <option value="{{ $value }}"></option>
            @endforeach
        </datalist>
        <datalist id="pf-manufacturers">
            @foreach ($this->suggestions['manufacturers'] as $value)
                <option value="{{ $value }}"></option>
            @endforeach
        </datalist>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:col-span-2">Model</legend>
            <x-ui.field label="Marka" for="pf-brand" error="form.brand">
                <x-ui.input id="pf-brand" list="pf-brands" wire:model.live.debounce.400ms="form.brand" placeholder="Örn. Apple, Samsung, Arçelik" :invalid="$errors->has('form.brand')" />
            </x-ui.field>
            <x-ui.field label="Model adı" for="pf-model" error="form.model_name">
                <x-ui.input id="pf-model" wire:model.live.debounce.400ms="form.model_name" placeholder="Örn. iPhone 15 Pro" :invalid="$errors->has('form.model_name')" />
            </x-ui.field>
            <x-ui.field label="Model numarası" for="pf-number" error="form.model_number" optional hint="Örn. A3102, SM-S918B">
                <x-ui.input id="pf-number" wire:model.blur="form.model_number" class="font-mono" />
            </x-ui.field>
            <x-ui.field label="Üretici" for="pf-manufacturer" error="form.manufacturer" optional hint="Boş bırakılırsa marka kullanılır">
                <x-ui.input id="pf-manufacturer" list="pf-manufacturers" wire:model.blur="form.manufacturer" />
            </x-ui.field>

            @if ($this->duplicate)
                <div class="flex items-start gap-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-800 ring-1 ring-amber-200 sm:col-span-2 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30">
                    <span class="text-lg leading-none">⚠️</span>
                    <p><strong>{{ $this->duplicate->display_name }}</strong> kataloğunuzda zaten var.
                        <button type="button" wire:click="edit({{ $this->duplicate->id }})" class="font-semibold underline">Mevcut kaydı düzenleyin</button>.</p>
                </div>
            @endif
        </fieldset>

        <fieldset>
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">Teknik notlar</legend>
            <x-ui.field label="Açıklama" for="pf-desc" error="form.description" optional hint="Sık görülen arızalar, parça uyumluluğu, dikkat edilecek noktalar…">
                <textarea id="pf-desc" wire:model.blur="form.description" rows="5"
                    class="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10"></textarea>
            </x-ui.field>
        </fieldset>
    </div>

    <footer class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-slate-950/50">
        <button type="button" x-on:click="$dispatch('close-drawer', { name: 'product-form' })"
            class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:text-slate-200 dark:hover:bg-white/10">Vazgeç</button>
        <button type="submit" wire:loading.attr="disabled" wire:target="save"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">
            <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Değişiklikleri kaydet' : 'Modeli ekle' }}</span>
            <span wire:loading wire:target="save">Kaydediliyor…</span>
        </button>
    </footer>
</form>
