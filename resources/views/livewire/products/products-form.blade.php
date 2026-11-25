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

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:col-span-2">Teknik bilgiler</legend>
            <x-ui.field label="Kod adı (codename)" for="pf-codename" error="form.codename" optional hint="Örn. e3q, houji, husky">
                <x-ui.input id="pf-codename" wire:model.blur="form.codename" class="font-mono" />
            </x-ui.field>
            <x-ui.field label="Cihaz türü" for="pf-type" error="form.device_type">
                <select id="pf-type" wire:model="form.device_type" class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                    @foreach (\App\Enums\DeviceType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Yonga seti / işlemci" for="pf-chipset" error="form.chipset" optional>
                <x-ui.input id="pf-chipset" wire:model.blur="form.chipset" placeholder="Örn. Snapdragon 8 Gen 3" />
            </x-ui.field>
            <x-ui.field label="Çıkış yılı" for="pf-year" error="form.release_year" optional>
                <x-ui.input id="pf-year" type="number" min="1990" max="{{ date('Y') + 2 }}" wire:model.blur="form.release_year" :invalid="$errors->has('form.release_year')" />
            </x-ui.field>
        </fieldset>

        <fieldset>
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">Görsel</legend>
            <div class="flex items-center gap-4">
                <div class="relative grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                    @if ($form->image)
                        <img src="{{ $form->image }}" alt="" class="h-full w-full object-contain">
                    @else
                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    @endif
                    <div wire:loading.flex wire:target="photo" class="absolute inset-0 items-center justify-center bg-white/80 dark:bg-slate-900/80"><x-koyu-svg-loading /></div>
                </div>
                <div class="space-y-2">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/15 dark:hover:bg-white/5">
                        {{ $form->image ? 'Değiştir' : 'Görsel yükle' }}
                        <input type="file" wire:model="photo" accept="image/png,image/jpeg,image/webp,image/gif" class="sr-only">
                    </label>
                    @if ($form->image)
                        <button type="button" wire:click="removeImage" class="ms-2 text-sm font-semibold text-rose-600 hover:text-rose-700">Kaldır</button>
                    @endif
                    <p class="text-xs text-slate-500">PNG, JPEG, WebP · en fazla 2 MB. {{ \App\Services\DeviceCatalog::IMAGE_MAX_SIZE }} px'e küçültülüp base64 olarak saklanır.</p>
                    @error('photo') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
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
