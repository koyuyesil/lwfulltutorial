@use('App\Enums\DividerType')
@use('App\Enums\RepairMethod')
@php
    $type = DividerType::tryFrom($form->divider_type) ?? DividerType::ADC;
    $select = 'block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
@endphp

<form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
    <div class="relative grid min-h-0 flex-1 overflow-y-auto lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        <div wire:loading.flex wire:target="edit,newBoard" class="absolute inset-0 z-10 items-center justify-center bg-white/70 dark:bg-slate-900/70">
            <x-koyu-svg-loading />
        </div>

        {{-- Sol: form --}}
        <div class="space-y-6 px-6 py-6">
            <fieldset class="grid gap-4 sm:grid-cols-2">
                <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:col-span-2">Cihaz</legend>
                <x-ui.field label="Model" for="bf-product" error="form.product_id" class="sm:col-span-2">
                    <select id="bf-product" wire:model.live="form.product_id" class="{{ $select }}">
                        <option value="">— Model seçin —</option>
                        @foreach ($this->products as $p)
                            <option value="{{ $p->id }}">{{ $p->brand }} {{ $p->model_name }}{{ $p->codename ? ' (' . $p->codename . ')' : '' }}{{ $p->model_number ? ' · ' . $p->model_number : '' }}</option>
                        @endforeach
                    </select>
                    @if ($this->products->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">Önce <a href="{{ route('products.index') }}" wire:navigate class="font-semibold underline">cihaz modeli</a> ekleyin.</p>
                    @endif
                </x-ui.field>
                <x-ui.field label="Build / kart adı" for="bf-build" error="form.build_name" hint="Model seçilince kod adından (Android ürün adı) dolar">
                    <x-ui.input id="bf-build" wire:model.live.debounce.300ms="form.build_name" placeholder="örn. vili, garnet" class="font-mono" :invalid="$errors->has('form.build_name')" />
                </x-ui.field>
                <x-ui.field label="Referans gerilim (VREF)" for="bf-vref" error="form.vref" hint="Çoğu kartta 1,8 V">
                    <div class="relative">
                        <x-ui.input id="bf-vref" type="number" step="0.01" min="0.5" max="5" wire:model.live.debounce.300ms="form.vref" class="pr-8" />
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">V</span>
                    </div>
                </x-ui.field>
            </fieldset>

            {{-- Gerilim bölücü tipi --}}
            <fieldset>
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500">Gerilim bölücü tipi</legend>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach (DividerType::cases() as $t)
                        @php($active = $type === $t)
                        <button type="button" wire:click="setType('{{ $t->value }}')" @class(['rounded-xl p-3 text-left ring-1 transition',
                            'bg-indigo-50 ring-2 ring-indigo-500 dark:bg-indigo-500/10' => $active,
                            'ring-slate-200 hover:bg-slate-50 dark:ring-white/10 dark:hover:bg-white/5' => ! $active])>
                            <span class="flex items-center gap-2">
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $t->badge() }}">{{ $t->short() }}</span>
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $t->label() }}</span>
                            </span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $t->description() }}</span>
                        </button>
                    @endforeach
                </div>
            </fieldset>

            {{-- Dirençler --}}
            <fieldset class="grid gap-4 sm:grid-cols-2">
                <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:col-span-2">Dirençler</legend>
                @if ($type === DividerType::LOGIC)
                    <div class="flex gap-2 sm:col-span-2">
                        <button type="button" wire:click="setLogicLevel(1)" class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold ring-1 {{ $form->r1 !== '' && $form->r2 === '' ? 'bg-emerald-600 text-white ring-emerald-600' : 'text-slate-600 ring-slate-300 dark:text-slate-300 dark:ring-white/15' }}">1 · HIGH (yalnız R1 → VREF)</button>
                        <button type="button" wire:click="setLogicLevel(0)" class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold ring-1 {{ $form->r2 !== '' && $form->r1 === '' ? 'bg-slate-700 text-white ring-slate-700' : 'text-slate-600 ring-slate-300 dark:text-slate-300 dark:ring-white/15' }}">0 · LOW (yalnız R2 → GND)</button>
                    </div>
                @endif
                <x-ui.field :label="$type->internalR1() ? 'R1 — dahili pull-up (entegre içinde)' : 'R1 (VREF tarafı)'" for="bf-r1" error="form.r1"
                    :hint="$type->internalR1() ? 'Genelde 100k · bilinmiyorsa aşağıdan ölçerek hesaplayın' : 'Boş = takılı değil (NC)'">
                    <x-ui.input id="bf-r1" wire:model.live.debounce.250ms="form.r1" placeholder="100k" class="font-mono" :invalid="$errors->has('form.r1')" />
                </x-ui.field>
                <x-ui.field label="R2 (GND tarafı)" for="bf-r2" error="form.r2" hint="Boş = takılı değil (NC)">
                    <x-ui.input id="bf-r2" wire:model.live.debounce.250ms="form.r2" placeholder="47k" class="font-mono" :invalid="$errors->has('form.r2')" />
                </x-ui.field>

                {{-- Not: R1 ölçümle nasıl bulunur --}}
                <div class="rounded-xl bg-sky-50 p-4 text-xs leading-relaxed text-sky-950 ring-1 ring-sky-200 sm:col-span-2 dark:bg-sky-500/10 dark:text-sky-100 dark:ring-sky-500/20">
                    <p class="flex items-center gap-1.5 font-bold">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                        R1 değeri nasıl bulunur? (datasheet gerekmez)
                    </p>
                    <ol class="mt-2 list-decimal space-y-1 pl-5">
                        <li>R2'yi <strong>sökmeden</strong>, kart çalışırken ölçüm noktasında (R2'nin ID hattına bağlı ucu, <span class="font-mono">Vout</span>) GND'ye göre gerilimi ölçün.</li>
                        <li>Besleme tarafı bilinir: <span class="font-mono">Vin</span> = VREF (çoğu kartta 1,8 V). Gerekirse R1'in besleme ucundan da ölçün.</li>
                        <li>Gerilim bölücüden: <span class="whitespace-nowrap rounded bg-white/70 px-1.5 py-0.5 font-mono font-semibold dark:bg-white/10">R1 = R2 × (Vin − Vout) / Vout</span> — R2 olarak kartta takılı <strong>orijinal</strong> direncin değerini kullanın.</li>
                    </ol>
                    <p class="mt-2">Böylece R1 (dahili pull-up dahil) datasheet'e bakmadan bilinir. Bulunan R1 ile yeni R2 takıldığında beklenen gerilim hesaplanır; <strong>tamir sonrası</strong> aynı noktadan ölçüp değişimin doğru yapıldığını kontrol edebilirsiniz.</p>
                </div>

                {{-- Opsiyonel R1 hesaplama --}}
                <details class="group rounded-xl ring-1 ring-slate-200 sm:col-span-2 dark:ring-white/10"
                    wire:key="r1calc-{{ $form->boardId?->id ?? 'new' }}" wire:ignore
                    x-data="r1Calc(() => ({ vref: $wire.form.vref, r2: $wire.form.r2, r2_org: $wire.form.r2_org }))">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-3 text-sm font-semibold text-slate-800 dark:text-slate-100">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" /></svg>
                            R1 hesaplama
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-500 dark:bg-white/10">opsiyonel</span>
                        </span>
                        <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </summary>
                    <div class="border-t border-slate-100 px-4 pb-4 pt-3 dark:border-white/5">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Vin (VREF)
                                <div class="relative mt-1">
                                    <input type="text" inputmode="decimal" x-model="vin" class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-7 font-mono text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">V</span>
                                </div>
                            </label>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Ölçülen Vout
                                <div class="relative mt-1">
                                    <input type="text" inputmode="decimal" x-model="vout" placeholder="örn. 0,164" class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-7 font-mono text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">V</span>
                                </div>
                            </label>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Orijinal R2
                                <input type="text" x-model="r2" placeholder="örn. 10k" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2 font-mono text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                            </label>
                        </div>

                        <p x-show="error" x-text="error" class="mt-2 text-xs font-medium text-rose-600"></p>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2.5 dark:bg-white/5">
                            <div class="text-sm">
                                <span class="text-slate-500">R1 =</span>
                                <span class="font-mono text-lg font-black text-slate-900 dark:text-white" x-text="label(r1)"></span>
                                <template x-if="ready && $wire.form.r2">
                                    <p class="text-xs text-slate-500">Tamir sonrası beklenen (R2 = <span class="font-mono" x-text="$wire.form.r2"></span>):
                                        <span class="font-mono font-bold text-amber-600 dark:text-amber-300" x-text="volt(expected($wire.form.r2))"></span></p>
                                </template>
                            </div>
                            <button type="button" x-bind:disabled="! ready" x-on:click="$wire.set('form.r1', short())"
                                class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-40">
                                R1 alanına yaz
                            </button>
                        </div>
                    </div>
                </details>
            </fieldset>

            <fieldset class="grid gap-4 sm:grid-cols-2">
                <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500 sm:col-span-2">Donanım kimliği</legend>
                <x-ui.field label="Seri üretim HWID" for="bf-mp" error="form.mass_production_hwid" optional>
                    <x-ui.input id="bf-mp" wire:model.blur="form.mass_production_hwid" class="font-mono" />
                </x-ui.field>
                <x-ui.field label="Ön üretim HWID" for="bf-pp" error="form.pre_production_hwid" optional>
                    <x-ui.input id="bf-pp" wire:model.blur="form.pre_production_hwid" class="font-mono" />
                </x-ui.field>
            </fieldset>

            <fieldset>
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500">Onarım yöntemleri</legend>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach (RepairMethod::cases() as $m)
                        <label @class(['cursor-pointer rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition',
                            $m->color() . ' ring-transparent' => in_array($m->value, $form->repair_methods, true),
                            'text-slate-600 ring-slate-300 hover:bg-slate-50 dark:text-slate-300 dark:ring-white/15' => ! in_array($m->value, $form->repair_methods, true)])>
                            <input type="checkbox" wire:model.live="form.repair_methods" value="{{ $m->value }}" class="sr-only">
                            {{ in_array($m->value, $form->repair_methods, true) ? '✓ ' : '' }}{{ $m->label() }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <x-ui.field label="Açıklama / notlar" for="bf-desc" error="form.description" optional>
                <textarea id="bf-desc" wire:model.blur="form.description" rows="3"
                    class="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10"
                    placeholder="Direnç konumu, test noktası, dikkat edilecekler…"></textarea>
            </x-ui.field>
        </div>

        {{-- Sağ: canlı PCB önizleme --}}
        <div class="border-t border-slate-200 bg-slate-50 px-6 py-6 lg:border-l lg:border-t-0 dark:border-white/10 dark:bg-slate-950/40">
            <div class="lg:sticky lg:top-0" wire:ignore>
                <div class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Canlı PCB görünümü</p>
                    {{-- Entegre seçimi yalnızca görselde (üç tipte de) --}}
                    <div class="inline-flex rounded-lg bg-white p-0.5 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10" role="group" aria-label="Entegre">
                        <button type="button" wire:click="setChip('pmic')" :class="$wire.form.chip === 'pmic' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500'" class="rounded-md px-2.5 py-1 font-mono text-xs font-bold">PMIC</button>
                        <button type="button" wire:click="setChip('soc')" :class="$wire.form.chip === 'soc' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500'" class="rounded-md px-2.5 py-1 font-mono text-xs font-bold">CPU/SoC</button>
                    </div>
                </div>
                <x-board-pcb source="() => ({ type: $wire.form.divider_type, chip: $wire.form.chip, vref: $wire.form.vref, r1: $wire.form.r1, r2: $wire.form.r2, r1_org: $wire.form.r1_org, r2_org: $wire.form.r2_org, name: $wire.form.build_name })" />
            </div>
        </div>
    </div>

    <footer class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-slate-950/50">
        <button type="button" x-on:click="$dispatch('close-drawer', { name: 'board-form' })"
            class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:text-slate-200 dark:hover:bg-white/10">Vazgeç</button>
        <button type="submit" wire:loading.attr="disabled" wire:target="save"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">
            <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Değişiklikleri kaydet' : 'Board ID ekle' }}</span>
            <span wire:loading wire:target="save">Kaydediliyor…</span>
        </button>
    </footer>
</form>
