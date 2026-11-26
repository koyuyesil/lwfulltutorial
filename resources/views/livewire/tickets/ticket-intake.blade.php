@use('App\Models\Ticket')
@php
    $chipOn = 'bg-slate-900 text-white ring-slate-900 dark:bg-white dark:text-slate-900 dark:ring-white';
    $chipOff = 'text-slate-600 ring-slate-200 hover:bg-slate-50 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/5';
    $done = collect([$ticket->signed_at, $ticket->photos->isNotEmpty(), ! empty($ticket->intake)])->filter()->count();
@endphp

<section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10" x-data="{ open: {{ $ticket->signed_at ? 'false' : 'true' }} }">
    <header class="flex flex-wrap items-center gap-3 px-5 py-4">
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
        </span>
        <div class="min-w-0 flex-1">
            <h3 class="font-bold text-slate-900 dark:text-white">Dijital kabul formu</h3>
            <p class="text-xs text-slate-500">Hasar fotoğrafları, aksesuarlar, ekran kilidi ve müşteri imzası — fişe ve PDF'e işlenir.</p>
        </div>
        <span @class(['rounded-full px-2.5 py-1 text-xs font-semibold ring-1',
            'bg-emerald-100 text-emerald-800 ring-emerald-300 dark:bg-emerald-500/15 dark:text-emerald-200 dark:ring-emerald-500/30' => $ticket->signed_at,
            'bg-amber-100 text-amber-800 ring-amber-300 dark:bg-amber-500/15 dark:text-amber-200 dark:ring-amber-500/30' => ! $ticket->signed_at])>
            {{ $ticket->signed_at ? '✓ İmzalandı' : 'İmza bekleniyor' }}
        </span>
        <button type="button" @click="open = ! open" class="rounded-lg px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10" x-text="open ? 'Gizle' : 'Aç'"></button>
    </header>

    <div x-show="open" x-collapse class="space-y-6 border-t border-slate-100 p-5 dark:border-white/5">
        {{-- Fotoğraflar --}}
        <div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Kabul fotoğrafları <span class="font-normal normal-case">({{ $ticket->photos->count() }}/{{ \App\Livewire\Tickets\TicketIntake::MAX_PHOTOS }})</span></p>
                <p class="text-[11px] text-slate-400">Ön, arka, yanlar ve hasarlı bölgeler</p>
            </div>
            <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-6" x-data="{ zoom: null }">
                @foreach ($ticket->photos as $photo)
                    <figure wire:key="photo-{{ $photo->id }}" class="group relative overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                        <button type="button" @click="zoom = @js($photo->url())" class="block w-full"><img src="{{ $photo->url() }}" alt="{{ $photo->caption }}" loading="lazy" class="aspect-square w-full object-cover transition group-hover:scale-105"></button>
                        <input value="{{ $photo->caption }}" wire:change="caption({{ $photo->id }}, $event.target.value)" placeholder="Açıklama…"
                            class="absolute inset-x-0 bottom-0 border-0 bg-black/55 px-2 py-1 text-[11px] text-white placeholder:text-white/60 focus:ring-0">
                        <button type="button" wire:click="deletePhoto({{ $photo->id }})" wire:confirm="Fotoğraf silinsin mi?"
                            class="absolute right-1 top-1 grid h-6 w-6 place-items-center rounded-full bg-black/60 text-xs text-white opacity-0 transition group-hover:opacity-100" aria-label="Fotoğrafı sil">✕</button>
                    </figure>
                @endforeach
                @if ($ticket->photos->count() < \App\Livewire\Tickets\TicketIntake::MAX_PHOTOS)
                    <label x-data="photoUploader" @dragover.prevent="over = true" @dragleave.prevent="over = false" @drop.prevent="over = false; pick($event.dataTransfer.files)"
                        :class="over ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 hover:border-slate-400 dark:border-white/15'"
                        class="relative grid aspect-square cursor-pointer place-items-center rounded-xl border-2 border-dashed text-center transition">
                        <span x-show="! busy" class="px-2">
                            <span class="block text-2xl">📷</span>
                            <span class="mt-1 block text-[11px] font-semibold text-slate-600 dark:text-slate-300">Fotoğraf çek / ekle</span>
                        </span>
                        <span x-show="busy" x-cloak class="w-3/4">
                            <span class="block text-[11px] font-semibold text-indigo-600">Yükleniyor %<span x-text="progress"></span></span>
                            <span class="mt-1 block h-1 overflow-hidden rounded-full bg-slate-200"><span class="block h-full bg-indigo-600 transition-all" :style="{ width: progress + '%' }"></span></span>
                        </span>
                        <input type="file" accept="image/*" capture="environment" multiple class="sr-only" @change="pick($event.target.files); $event.target.value = ''">
                    </label>
                @endif

                {{-- Büyük görünüm --}}
                <template x-teleport="body">
                    <div x-show="zoom" x-cloak @click="zoom = null" @keydown.escape.window="zoom = null" class="fixed inset-0 z-[60] grid place-items-center bg-black/80 p-6">
                        <img :src="zoom" alt="" class="max-h-full max-w-full rounded-xl shadow-2xl">
                    </div>
                </template>
            </div>
            @error('uploads') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            @error('uploads.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <form wire:submit="save" class="space-y-5">
            <div class="grid gap-5 lg:grid-cols-2">
                <fieldset>
                    <legend class="text-xs font-bold uppercase tracking-wider text-slate-500">Teslim alınan aksesuarlar</legend>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach (Ticket::ACCESSORIES as $a)
                            <label class="cursor-pointer">
                                <input type="checkbox" wire:model.live="accessories" value="{{ $a }}" class="peer sr-only">
                                <span @class(['inline-block rounded-full px-3 py-1 text-xs font-semibold ring-1 transition', in_array($a, $accessories, true) ? $chipOn : $chipOff])>{{ in_array($a, $accessories, true) ? '✓ ' : '' }}{{ $a }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset>
                    <legend class="text-xs font-bold uppercase tracking-wider text-slate-500">Kabuldeki dış durum (hasar kaydı)</legend>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach (Ticket::CONDITIONS as $c)
                            <label class="cursor-pointer">
                                <input type="checkbox" wire:model.live="conditions" value="{{ $c }}" class="sr-only">
                                <span @class(['inline-block rounded-full px-3 py-1 text-xs font-semibold ring-1 transition',
                                    'bg-rose-600 text-white ring-rose-600' => in_array($c, $conditions, true), $chipOff => ! in_array($c, $conditions, true)])>{{ in_array($c, $conditions, true) ? '⚠ ' : '' }}{{ $c }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <fieldset>
                    <legend class="text-xs font-bold uppercase tracking-wider text-slate-500">Ekran kilidi</legend>
                    <div class="mt-2 inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
                        @foreach (['none' => 'Yok', 'pin' => 'PIN', 'pattern' => 'Desen', 'password' => 'Şifre'] as $k => $l)
                            <button type="button" wire:click="$set('lockType', '{{ $k }}')" @class(['rounded-md px-3 py-1 text-xs font-semibold', 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' => $lockType === $k, 'text-slate-500' => $lockType !== $k])>{{ $l }}</button>
                        @endforeach
                    </div>
                    @if (in_array($lockType, ['pin', 'password'], true))
                        <div class="relative mt-2 max-w-xs" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" wire:model="lockSecret" autocomplete="off" @if ($lockType === 'pin') inputmode="numeric" @endif
                                placeholder="{{ $lockType === 'pin' ? '4–12 rakam' : 'Şifre' }}"
                                class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-16 font-mono text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                            <button type="button" @click="show = ! show" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-semibold text-indigo-600" x-text="show ? 'Gizle' : 'Göster'"></button>
                        </div>
                    @elseif ($lockType === 'pattern')
                        <div class="mt-2 flex items-center gap-4" x-data="patternLock(@js($lockSecret))" @pattern-change="$wire.set('lockSecret', $event.detail, false)">
                            <svg x-ref="grid" viewBox="0 0 120 120" class="h-32 w-32 touch-none select-none rounded-xl bg-slate-900 p-1"
                                @pointerdown="down($event)" @pointermove="add($event)" @pointerup="up()" @pointerleave="drawing && up()">
                                <path :d="path" fill="none" stroke="#818cf8" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                                @foreach (range(1, 9) as $n)
                                    <circle cx="{{ 20 + (($n - 1) % 3) * 40 }}" cy="{{ 20 + intdiv($n - 1, 3) * 40 }}" r="6" :fill="seq.includes({{ $n }}) ? '#a5b4fc' : '#475569'" />
                                @endforeach
                            </svg>
                            <div class="text-xs text-slate-500">
                                <p>Deseni kutuda parmağınızla / farenizle çizin.</p>
                                <p class="mt-1 font-mono text-slate-700 dark:text-slate-200" x-text="value || '—'"></p>
                                <button type="button" @click="reset()" class="mt-1 font-semibold text-indigo-600">Temizle</button>
                            </div>
                        </div>
                    @endif
                    @error('lockSecret') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-[11px] text-slate-400">Şifreli saklanır; fişte yalnızca kilit türü yazar.</p>
                </fieldset>
                <x-ui.field label="Kabul notu" for="in-notes" optional>
                    <textarea id="in-notes" wire:model="notes" rows="3" placeholder="örn. Sağ üst köşede çatlak, kılıf içinde teslim alındı"
                        class="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10"></textarea>
                </x-ui.field>
            </div>

            <div class="flex justify-end">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900" wire:loading.attr="disabled" wire:target="save">Kabul bilgilerini kaydet</button>
            </div>
        </form>

        {{-- İmza --}}
        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Müşteri imzası</p>
                @if ($ticket->signed_at)
                    <p class="text-xs text-emerald-600">✓ {{ $ticket->signed_at->format('d.m.Y H:i') }} tarihinde imzalandı ·
                        <button type="button" wire:click="clearSignature" wire:confirm="İmza silinip yeniden alınsın mı?" class="font-semibold underline">yeniden al</button></p>
                @endif
            </div>
            <p class="mt-1 text-xs text-slate-500">"Cihazımı yukarıda belirtilen durum ve aksesuarlarla teslim ettim; servis koşullarını kabul ediyorum."</p>
            @if ($ticket->signature)
                <img src="{{ $ticket->signature }}" alt="Müşteri imzası" class="mt-3 h-24 rounded-lg bg-white px-4 ring-1 ring-slate-200 dark:ring-white/10">
            @else
                <div x-data="signaturePad" class="mt-3">
                    <canvas x-ref="canvas" class="h-40 w-full touch-none rounded-xl bg-white ring-1 ring-slate-300 dark:ring-white/10"
                        @pointerdown="start($event)" @pointermove="move($event)" @pointerup="end()" @pointerleave="end()"></canvas>
                    <div class="mt-2 flex items-center justify-between">
                        <button type="button" @click="clear()" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Temizle</button>
                        <button type="button" :disabled="empty" @click="$wire.saveSignature(dataUrl())"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-40">İmzayı kaydet</button>
                    </div>
                </div>
            @endif
            @error('signature') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</section>
