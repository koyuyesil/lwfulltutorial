@use('App\Enums\TaskCategory')
@use('App\Enums\Recurrence')
@use('App\Enums\PriorityType')
@php
    $category = TaskCategory::tryFrom($form->category) ?? TaskCategory::GENERAL;
    $recurrence = Recurrence::tryFrom($form->recurrence) ?? Recurrence::NONE;
    $textarea = 'block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';
    $select = 'block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10';

    // Tekrar önizlemesi: sonraki 3 tarih
    $preview = [];
    if ($recurrence !== Recurrence::NONE && $form->due_date) {
        try {
            $cursor = \Illuminate\Support\Carbon::parse($form->due_date . ' ' . ($form->due_time ?: '09:00'));
            $anchor = $cursor->day;
            $until = $form->recurrence_until ? \Illuminate\Support\Carbon::parse($form->recurrence_until)->endOfDay() : null;
            $preview[] = $cursor->copy();
            for ($i = 0; $i < 3; $i++) {
                $cursor = $recurrence->next($cursor, max(1, (int) $form->recurrence_interval), $anchor);
                if (! $cursor || ($until && $cursor->gt($until))) break;
                $preview[] = $cursor->copy();
            }
        } catch (\Throwable) {
            $preview = [];
        }
    }
@endphp

<form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
    <div class="relative flex-1 space-y-6 overflow-y-auto px-6 py-6">
        <div wire:loading.flex wire:target="editTask,newTask" class="absolute inset-0 z-10 items-center justify-center bg-white/70 dark:bg-slate-900/70">
            <x-koyu-svg-loading />
        </div>

        {{-- Hazır şablonlar --}}
        @unless ($form->isEditing())
            <section>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Hazır şablonlar</p>
                <x-scroll-row track="gap-2 px-0.5 pb-2 pt-1" class="mt-2" label="Hazır şablonlar">
                    @foreach (\App\Support\TaskTemplates::all() as $key => $tpl)
                        <button type="button" wire:click="useTemplate('{{ $key }}')" aria-pressed="{{ $form->title === $tpl['title'] ? 'true' : 'false' }}"
                            @class(['flex w-40 shrink-0 flex-col items-start rounded-xl p-3 text-left ring-1 transition hover:-translate-y-0.5',
                                'bg-indigo-50 ring-2 ring-indigo-500 dark:bg-indigo-500/10' => $form->title === $tpl['title'],
                                'bg-white ring-slate-200 hover:shadow-md dark:bg-slate-800 dark:ring-white/10' => $form->title !== $tpl['title']])>
                            <span class="text-lg">{{ $tpl['category']->emoji() }}</span>
                            <span class="mt-1 line-clamp-2 text-xs font-semibold text-slate-900 dark:text-white">{{ $tpl['title'] }}</span>
                            <span class="mt-1 text-[11px] text-slate-500">{{ $tpl['recurrence']->describe() }}</span>
                        </button>
                    @endforeach
                </x-scroll-row>
            </section>
        @endunless

        <x-ui.field label="Görev" for="tk-title" error="form.title">
            <x-ui.input id="tk-title" wire:model.blur="form.title" placeholder="Örn. Ahmet Bey'den kalan ödemeyi tahsil et" :invalid="$errors->has('form.title')" class="text-base" />
        </x-ui.field>

        {{-- Tür --}}
        <div>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Tür</p>
            <div class="mt-1.5 grid grid-cols-2 gap-2 sm:grid-cols-5">
                @foreach (TaskCategory::cases() as $c)
                    <button type="button" wire:click="$set('form.category', '{{ $c->value }}')"
                        @class(['flex items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs font-semibold ring-1 transition',
                            'ring-2 ring-indigo-500 bg-indigo-50 text-indigo-900 dark:bg-indigo-500/10 dark:text-indigo-100' => $category === $c,
                            'text-slate-600 ring-slate-200 hover:bg-slate-50 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/5' => $category !== $c])>
                        <span class="text-base leading-none">{{ $c->emoji() }}</span>
                        <span class="truncate">{{ $c->label() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Zaman --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.field label="Tarih" for="tk-date" error="form.due_date">
                <x-ui.input id="tk-date" type="date" wire:model.live="form.due_date" :invalid="$errors->has('form.due_date')" />
            </x-ui.field>
            <x-ui.field label="Saat" for="tk-time" error="form.due_time">
                <x-ui.input id="tk-time" type="time" wire:model.live="form.due_time" />
            </x-ui.field>
            <x-ui.field label="Hatırlatma" for="tk-remind" error="form.remind_before">
                <select id="tk-remind" wire:model="form.remind_before" class="{{ $select }}">
                    @foreach (\App\Models\Task::REMIND_OPTIONS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </x-ui.field>
        </div>
        <div class="-mt-3 flex flex-wrap gap-1.5">
            @foreach (['Bugün' => now(), 'Yarın' => now()->addDay(), 'Pazartesi' => now()->next(\Illuminate\Support\Carbon::MONDAY), 'Ay sonu' => now()->endOfMonth(), '1 ay sonra' => now()->addMonthNoOverflow()] as $label => $date)
                <button type="button" wire:click="$set('form.due_date', '{{ $date->format('Y-m-d') }}')"
                    @class(['rounded-full px-2.5 py-1 text-xs font-medium ring-1 transition',
                        'bg-slate-900 text-white ring-slate-900 dark:bg-white dark:text-slate-900' => $form->due_date === $date->format('Y-m-d'),
                        'text-slate-600 ring-slate-200 hover:bg-slate-50 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/5' => $form->due_date !== $date->format('Y-m-d')])>{{ $label }}</button>
            @endforeach
            @if ($form->due_date)
                <button type="button" wire:click="$set('form.due_date', '')" class="rounded-full px-2.5 py-1 text-xs font-medium text-slate-400 hover:text-slate-700">Tarihsiz</button>
            @endif
        </div>

        {{-- Tekrar --}}
        <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Tekrar</p>
            <div class="mt-2 grid grid-cols-5 gap-1 rounded-lg bg-white p-1 ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-white/10">
                @foreach (Recurrence::cases() as $r)
                    <button type="button" wire:click="$set('form.recurrence', '{{ $r->value }}')"
                        @class(['rounded-md px-2 py-1.5 text-xs font-semibold transition',
                            'bg-indigo-600 text-white shadow-sm' => $recurrence === $r,
                            'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' => $recurrence !== $r])>
                        {{ $r === Recurrence::NONE ? 'Yok' : $r->label() }}
                    </button>
                @endforeach
            </div>

            @if ($recurrence !== Recurrence::NONE)
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Sıklık" for="tk-interval" error="form.recurrence_interval">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <span>Her</span>
                            <x-ui.input id="tk-interval" type="number" min="1" max="365" wire:model.live.debounce.400ms="form.recurrence_interval" class="w-20 text-center" />
                            <span>{{ ['daily' => 'günde', 'weekly' => 'haftada', 'monthly' => 'ayda', 'yearly' => 'yılda'][$recurrence->value] }} bir</span>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Bitiş tarihi" for="tk-until" error="form.recurrence_until" optional hint="Boş bırakılırsa süresiz tekrarlar">
                        <x-ui.input id="tk-until" type="date" wire:model.live="form.recurrence_until" />
                    </x-ui.field>
                </div>
                @if ($preview)
                    <div class="mt-3 rounded-lg bg-white p-3 text-xs ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-white/10">
                        <p class="font-semibold text-slate-700 dark:text-slate-200">🔁 {{ $recurrence->describe((int) $form->recurrence_interval) }} — sıradaki tarihler:</p>
                        <ul class="mt-1.5 flex flex-wrap gap-1.5">
                            @foreach ($preview as $i => $date)
                                <li class="rounded-md bg-slate-100 px-2 py-1 font-medium text-slate-700 dark:bg-white/10 dark:text-slate-200">{{ $date->locale('tr')->translatedFormat('d M Y, D') }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-slate-500">Görev tamamlandığında bir sonraki otomatik oluşturulur.</p>
                    </div>
                @endif
            @endif
        </div>

        {{-- Detaylar --}}
        <div class="grid gap-4 sm:grid-cols-2">
            @if ($category->hasAmount())
                <x-ui.field label="Tutar (₺)" for="tk-amount" error="form.amount" optional
                    :hint="$category->isIncome() ? 'Tahsil edilecek / faturalanacak tutar' : 'Ödenecek tutar'">
                    <x-ui.input id="tk-amount" type="number" step="0.01" min="0" inputmode="decimal" wire:model.blur="form.amount" placeholder="0,00" :invalid="$errors->has('form.amount')" />
                </x-ui.field>
            @endif
            <x-ui.field label="Müşteri" for="tk-client" error="form.client_id" optional :class="$category->hasAmount() ? '' : 'sm:col-span-2'">
                <select id="tk-client" wire:model="form.client_id" class="{{ $select }}">
                    <option value="">— Müşteriye bağlı değil —</option>
                    @foreach ($this->clients as $c)
                        <option value="{{ $c->id }}">{{ $c->full_name }}{{ $c->company ? ' · ' . $c->company : '' }}</option>
                    @endforeach
                </select>
            </x-ui.field>
        </div>

        <div>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Öncelik</p>
            <div class="mt-1.5 grid grid-cols-3 gap-2">
                @foreach (PriorityType::cases() as $p)
                    <button type="button" wire:click="$set('form.priority', '{{ $p->value }}')"
                        @class(['rounded-lg px-3 py-2 text-xs font-semibold ring-1 transition',
                            'ring-2 ring-indigo-500 bg-indigo-50 text-indigo-900 dark:bg-indigo-500/10 dark:text-indigo-100' => $form->priority === $p->value,
                            'text-slate-600 ring-slate-200 hover:bg-slate-50 dark:text-slate-300 dark:ring-white/10' => $form->priority !== $p->value])>
                        {{ $p->label() }}
                    </button>
                @endforeach
            </div>
        </div>

        <x-ui.field label="Not" for="tk-desc" error="form.description" optional>
            <textarea id="tk-desc" wire:model.blur="form.description" rows="3" class="{{ $textarea }}" placeholder="Detaylar, hesap bilgisi, aranacak numara…"></textarea>
        </x-ui.field>
    </div>

    <footer class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-slate-950/50">
        <button type="button" x-on:click="$dispatch('close-drawer', { name: 'task-form' })"
            class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:text-slate-200 dark:hover:bg-white/10">Vazgeç</button>
        <button type="submit" wire:loading.attr="disabled" wire:target="save"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">
            <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Değişiklikleri kaydet' : 'Görevi ekle' }}</span>
            <span wire:loading wire:target="save">Kaydediliyor…</span>
        </button>
    </footer>
</form>
