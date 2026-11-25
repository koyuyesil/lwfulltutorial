@php
    $seg = 'flex-1 rounded-md px-3 py-1.5 text-xs font-semibold transition';
    $segOn = 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white';
    $segOff = 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200';
    $step = 'grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-bold';
@endphp

<div class="flex min-h-0 flex-1 flex-col">
@if ($created)
    {{-- Başarılı kayıt özeti --}}
    @php
        $printable = array_merge($created->toArray(), [
            'status' => $created->status->label(),
            'priority' => $created->priority->label(),
        ]);
    @endphp
    <div class="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-10 text-center">
        <span class="grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-3xl text-emerald-600 dark:bg-emerald-500/15">✓</span>
        <h3 class="mt-5 text-xl font-bold text-slate-900 dark:text-white">Servis kaydı açıldı</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $created->clientProduct->client->full_name }} · {{ $created->clientProduct->product->display_name }}</p>

        <div class="mt-6 w-full max-w-sm rounded-2xl border-2 border-dashed border-slate-300 p-5 dark:border-white/15" x-data="{ copied: false }">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Müşteri takip kodu</p>
            <p class="mt-1 font-mono text-3xl font-black tracking-wider text-slate-900 dark:text-white">{{ $created->tracking_code }}</p>
            <button type="button" @click="navigator.clipboard.writeText(@js($created->tracking_code)); copied = true; setTimeout(() => copied = false, 1500)"
                class="mt-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400" x-text="copied ? 'Kopyalandı ✓' : 'Kodu kopyala'"></button>
            <p class="mt-3 text-xs text-slate-500">Müşteriniz <strong>{{ str_replace(['http://', 'https://'], '', route('tracking')) }}</strong> adresinden bu kod ve telefonunun son 4 hanesiyle durumu takip edebilir.</p>
            <p class="mt-2 text-xs font-semibold text-slate-600 dark:text-slate-300">Teslim hedefi: {{ $created->due_at?->locale('tr')->translatedFormat('d F l, H:i') }}</p>
        </div>

        <div class="mt-6 grid w-full max-w-sm grid-cols-2 gap-2">
            <button type="button" x-on:click="window.printTicket(@js($printable))"
                class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/15 dark:hover:bg-white/5">Fişi yazdır</button>
            <a href="{{ route('tickets.show', $created) }}" wire:navigate
                class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/15 dark:hover:bg-white/5">Kayda git</a>
            @if ($wa = $created->clientProduct->client->whatsappNumber())
                <a target="_blank" rel="noopener"
                    href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Merhaba ' . $created->clientProduct->client->first_name . ', cihazınız servisimize kabul edildi. Takip kodunuz: ' . $created->tracking_code . ' — Durumu buradan takip edebilirsiniz: ' . route('tracking', ['kod' => $created->tracking_code])) }}"
                    class="col-span-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">Takip kodunu WhatsApp ile gönder</a>
            @endif
            <button type="button" wire:click="resetForm" class="col-span-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Yeni kayıt aç</button>
        </div>
    </div>
@else
<form wire:submit="create" class="flex min-h-0 flex-1 flex-col">
    <div class="flex-1 space-y-8 overflow-y-auto px-6 py-6">

        {{-- 1. MÜŞTERİ --}}
        <section>
            <div class="flex items-center justify-between gap-3">
                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <span @class([$step, 'bg-emerald-500 text-white' => $this->client || $clientMode === 'new', 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => ! ($this->client || $clientMode === 'new')])>1</span>
                    Müşteri
                </h3>
                <div class="flex w-56 rounded-lg bg-slate-100 p-0.5 dark:bg-white/5">
                    <button type="button" wire:click="setClientMode('existing')" class="{{ $seg }} {{ $clientMode === 'existing' ? $segOn : $segOff }}">Kayıtlı</button>
                    <button type="button" wire:click="setClientMode('new')" class="{{ $seg }} {{ $clientMode === 'new' ? $segOn : $segOff }}">Yeni müşteri</button>
                </div>
            </div>

            <div class="mt-4">
                @if ($clientMode === 'existing')
                    @if ($this->client)
                        <div class="flex items-center gap-3 rounded-xl bg-indigo-50 p-3 ring-1 ring-indigo-200 dark:bg-indigo-500/10 dark:ring-indigo-500/30">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-indigo-600 text-sm font-bold text-white">{{ $this->client->initials }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $this->client->full_name }}</p>
                                <p class="truncate text-xs text-slate-600 dark:text-slate-300">{{ $this->client->phone }}{{ $this->client->company ? ' · ' . $this->client->company : '' }}</p>
                            </div>
                            <button type="button" wire:click="clearClient" class="text-xs font-semibold text-indigo-700 hover:underline dark:text-indigo-300">Değiştir</button>
                        </div>
                    @else
                        <div class="relative" x-data="{ focus: true }" @click.outside="focus = false" @keydown.escape.stop="focus = false">
                            <x-ui.search wire:model.live.debounce.250ms="clientSearch" @focus="focus = true" @input="focus = true" placeholder="Ad, soyad, telefon ya da firma yazın…" full />
                            @if (mb_strlen(trim($clientSearch)) >= 2)
                                <div x-show="focus" class="absolute inset-x-0 top-full z-20 mt-1 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                                    @forelse ($this->clientResults as $c)
                                        <button type="button" wire:key="cr-{{ $c->id }}" wire:click="selectClient({{ $c->id }})"
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-white/5">
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-200 text-xs font-bold text-slate-700 dark:bg-white/10 dark:text-slate-200">{{ $c->initials }}</span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $c->full_name }}</span>
                                                <span class="block truncate text-xs text-slate-500">{{ $c->phone }}{{ $c->company ? ' · ' . $c->company : '' }}</span>
                                            </span>
                                            <span class="text-xs text-slate-400">{{ $c->clients_products_count }} cihaz</span>
                                        </button>
                                    @empty
                                        <p class="px-4 py-3 text-sm text-slate-500">Eşleşen müşteri yok.</p>
                                    @endforelse
                                    <button type="button" wire:click="setClientMode('new')" class="block w-full border-t border-slate-100 px-4 py-2.5 text-left text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-white/5 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                                        + "{{ $clientSearch }}" ile yeni müşteri oluştur
                                    </button>
                                </div>
                            @endif
                        </div>
                        @error('selectedClient') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                    @endif
                @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Ad" for="tf-first" error="newClient.first_name">
                            <x-ui.input id="tf-first" wire:model.blur="newClient.first_name" :invalid="$errors->has('newClient.first_name')" />
                        </x-ui.field>
                        <x-ui.field label="Soyad" for="tf-last" error="newClient.last_name">
                            <x-ui.input id="tf-last" wire:model.blur="newClient.last_name" :invalid="$errors->has('newClient.last_name')" />
                        </x-ui.field>
                        <x-ui.field label="Telefon" for="tf-phone" error="newClient.phone" hint="Arıza takibinde son 4 hane ile doğrulanır">
                            <x-ui.input id="tf-phone" type="tel" inputmode="tel" wire:model.blur="newClient.phone"
                                x-mask:dynamic="$input.startsWith('+') ? '+99 999 999 99 99' : ($input.startsWith('0') ? '9999 999 99 99' : '999 999 99 99')"
                                :invalid="$errors->has('newClient.phone')" />
                        </x-ui.field>
                        <x-ui.field label="E-posta" for="tf-email" error="newClient.email" optional>
                            <x-ui.input id="tf-email" type="email" wire:model.blur="newClient.email" />
                        </x-ui.field>
                        <x-ui.field label="Firma" for="tf-company" error="newClient.company" optional class="sm:col-span-2">
                            <x-ui.input id="tf-company" wire:model.blur="newClient.company" />
                        </x-ui.field>
                    </div>
                @endif
            </div>
        </section>

        {{-- 2. CİHAZ --}}
        <section>
            @php $hasDevice = $selectedClientProduct || $this->product || ($productMode === 'new' && $newProduct['brand']); @endphp
            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                <span @class([$step, 'bg-emerald-500 text-white' => $hasDevice, 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => ! $hasDevice])>2</span>
                Cihaz
            </h3>

            {{-- Müşterinin kayıtlı cihazları --}}
            @if ($clientMode === 'existing' && $this->client && $this->client->clientsProducts->isNotEmpty())
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($this->client->clientsProducts as $device)
                        <button type="button" wire:key="dev-{{ $device->id }}" wire:click="selectDevice({{ $device->id }})"
                            @class(['flex items-start gap-3 rounded-xl p-3 text-left ring-1 transition',
                                'bg-indigo-50 ring-2 ring-indigo-500 dark:bg-indigo-500/10' => $selectedClientProduct === $device->id,
                                'ring-slate-200 hover:bg-slate-50 dark:ring-white/10 dark:hover:bg-white/5' => $selectedClientProduct !== $device->id])>
                            <span @class(['mt-0.5 grid h-4 w-4 shrink-0 place-items-center rounded-full border-2',
                                'border-indigo-600 bg-indigo-600' => $selectedClientProduct === $device->id,
                                'border-slate-300' => $selectedClientProduct !== $device->id])>
                                @if ($selectedClientProduct === $device->id)<span class="h-1.5 w-1.5 rounded-full bg-white"></span>@endif
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $device->product?->display_name }}</span>
                                <span class="block truncate font-mono text-xs text-slate-500">{{ $device->imei ?: ($device->serial ?: 'IMEI/seri yok') }}{{ $device->color ? ' · ' . $device->color : '' }}</span>
                            </span>
                        </button>
                    @endforeach
                    <button type="button" wire:click="selectDevice(null)"
                        @class(['flex items-center gap-3 rounded-xl border-2 border-dashed p-3 text-left text-sm font-semibold transition',
                            'border-indigo-500 text-indigo-700 dark:text-indigo-300' => ! $selectedClientProduct,
                            'border-slate-300 text-slate-500 hover:border-slate-400 dark:border-white/15' => $selectedClientProduct])>
                        + Farklı / yeni bir cihaz
                    </button>
                </div>
            @endif

            @if (! $selectedClientProduct)
                <div class="mt-4 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Model</p>
                        <div class="flex w-56 rounded-lg bg-slate-100 p-0.5 dark:bg-white/5">
                            <button type="button" wire:click="setProductMode('existing')" class="{{ $seg }} {{ $productMode === 'existing' ? $segOn : $segOff }}">Katalogdan</button>
                            <button type="button" wire:click="setProductMode('new')" class="{{ $seg }} {{ $productMode === 'new' ? $segOn : $segOff }}">Yeni model</button>
                        </div>
                    </div>

                    @if ($productMode === 'existing')
                        @if ($this->product)
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-slate-900 text-sm font-bold text-white dark:bg-white dark:text-slate-900">{{ mb_strtoupper(mb_substr($this->product->brand, 0, 1)) }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $this->product->display_name }}</p>
                                    <p class="truncate font-mono text-xs text-slate-500">{{ $this->product->model_number ?: '—' }}</p>
                                </div>
                                <button type="button" wire:click="clearProduct" class="text-xs font-semibold text-indigo-700 hover:underline dark:text-indigo-300">Değiştir</button>
                            </div>
                        @else
                            <div class="relative" x-data="{ focus: true }" @click.outside="focus = false" @keydown.escape.stop="focus = false">
                                <x-ui.search wire:model.live.debounce.250ms="productSearch" @focus="focus = true" @input="focus = true" placeholder="Marka ya da model yazın (örn. iPhone 13)…" full />
                                @if (mb_strlen(trim($productSearch)) >= 2)
                                    <div x-show="focus" class="absolute inset-x-0 top-full z-20 mt-1 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                                        @forelse ($this->productResults as $p)
                                            <button type="button" wire:key="pr-{{ $p->id }}" wire:click="selectProduct({{ $p->id }})"
                                                class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-white/5">
                                                <span class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $p->display_name }}</span>
                                                <span class="font-mono text-xs text-slate-400">{{ $p->model_number }}</span>
                                            </button>
                                        @empty
                                            <p class="px-4 py-3 text-sm text-slate-500">Katalogda eşleşen model yok.</p>
                                        @endforelse
                                        <button type="button" wire:click="setProductMode('new')" class="block w-full border-t border-slate-100 px-4 py-2.5 text-left text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-white/5 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                                            + "{{ $productSearch }}" modelini ekle
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @error('selectedProduct') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        @endif
                    @else
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.field label="Marka" for="tf-brand" error="newProduct.brand">
                                <x-ui.input id="tf-brand" wire:model.blur="newProduct.brand" placeholder="Örn. Samsung" :invalid="$errors->has('newProduct.brand')" />
                            </x-ui.field>
                            <x-ui.field label="Model" for="tf-model" error="newProduct.model_name">
                                <x-ui.input id="tf-model" wire:model.blur="newProduct.model_name" placeholder="Örn. Galaxy S23" :invalid="$errors->has('newProduct.model_name')" />
                            </x-ui.field>
                        </div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-ui.field label="IMEI" for="tf-imei" error="imei" optional hint="*#06# ile öğrenilir" class="sm:col-span-1">
                            <x-ui.input id="tf-imei" inputmode="numeric" maxlength="16" wire:model.live.debounce.500ms="imei" class="font-mono" :invalid="$errors->has('imei')" />
                        </x-ui.field>
                        <x-ui.field label="Seri no" for="tf-serial" error="serial" optional>
                            <x-ui.input id="tf-serial" wire:model.live.debounce.500ms="serial" class="font-mono uppercase" />
                        </x-ui.field>
                        <x-ui.field label="Renk" for="tf-color" error="color" optional>
                            <x-ui.input id="tf-color" list="tf-colors" wire:model.blur="color" />
                            <datalist id="tf-colors">
                                @foreach (['Siyah', 'Beyaz', 'Gri', 'Gümüş', 'Altın', 'Mavi', 'Kırmızı', 'Yeşil', 'Mor', 'Pembe'] as $c)
                                    <option value="{{ $c }}"></option>
                                @endforeach
                            </datalist>
                        </x-ui.field>
                    </div>

                    @if ($known = $this->knownDevice)
                        <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-100 dark:ring-amber-500/30">
                            <p class="font-semibold">🔁 Bu cihaz daha önce kaydedilmiş</p>
                            <p class="mt-1">{{ $known->product?->display_name }} · <strong>{{ $known->client?->full_name }}</strong> adına kayıtlı.
                                @if ($known->tickets->isNotEmpty())
                                    {{ $known->tickets->count() }} kez servise geldi; son kayıt {{ $known->tickets->first()->created_at->format('d.m.Y') }}
                                    ({{ $known->tickets->first()->status->label() }}).
                                @endif
                            </p>
                            <button type="button" wire:click="useKnownDevice" class="mt-2 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">Bu cihazı ve müşteriyi kullan</button>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        {{-- 3. ARIZA --}}
        <section>
            <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                <span @class([$step, 'bg-emerald-500 text-white' => mb_strlen(trim($problem)) >= 5, 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => mb_strlen(trim($problem)) < 5])>3</span>
                Arıza ve öncelik
            </h3>

            <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach (\App\Livewire\Tickets\TicketsForm::PROBLEM_TAGS as $tag)
                    <button type="button" wire:click="addTag(@js($tag))"
                        @class(['rounded-full px-2.5 py-1 text-xs font-medium ring-1 transition',
                            'bg-indigo-600 text-white ring-indigo-600' => str_contains(mb_strtolower($problem), mb_strtolower($tag)),
                            'text-slate-600 ring-slate-200 hover:bg-slate-50 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/5' => ! str_contains(mb_strtolower($problem), mb_strtolower($tag))])>
                        {{ $tag }}
                    </button>
                @endforeach
            </div>

            <x-ui.field label="Arıza açıklaması" for="tf-problem" error="problem" class="mt-4">
                <textarea id="tf-problem" wire:model.blur="problem" rows="4" placeholder="Müşterinin bildirdiği arıza, cihazın geliş durumu, dikkat edilecekler…"
                    @class(['block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset dark:bg-slate-950 dark:text-white',
                        'ring-slate-300 focus:ring-indigo-600 dark:ring-white/10' => ! $errors->has('problem'),
                        'ring-rose-400 focus:ring-rose-500' => $errors->has('problem')])></textarea>
            </x-ui.field>

            <div class="mt-4">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Öncelik</p>
                <div class="mt-1.5 grid grid-cols-3 gap-2">
                    @foreach (\App\Enums\PriorityType::cases() as $p)
                        <button type="button" wire:click="$set('priority', '{{ $p->value }}')"
                            @class(['rounded-xl p-3 text-left ring-1 transition',
                                'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' => $priority === $p,
                                'ring-slate-200 hover:bg-slate-50 dark:ring-white/10 dark:hover:bg-white/5' => $priority !== $p])>
                            <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $p->label() }}</span>
                            <span class="block text-xs text-slate-500">Teslim hedefi {{ \App\Models\Ticket::slaHoursFor($p, auth()->user()) }} saat</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <footer class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-slate-950/50">
        <p class="hidden text-xs text-slate-500 sm:block">Kayıt "Kabul edildi" durumuyla açılır ve takip kodu üretilir.</p>
        <div class="flex gap-3">
            <button type="button" x-on:click="$dispatch('close-drawer', { name: 'ticket-form' })"
                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:text-slate-200 dark:hover:bg-white/10">Vazgeç</button>
            <button type="submit" wire:loading.attr="disabled" wire:target="create"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">
                <span wire:loading.remove wire:target="create">Servis kaydını aç</span>
                <span wire:loading wire:target="create">Kaydediliyor…</span>
            </button>
        </div>
    </footer>
</form>
@endif
</div>
