@php
    $statusMeta = [
        'new' => ['Yeni', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'],
        'duplicate' => ['Mevcut', 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300'],
        'repeat' => ['Dosyada tekrar', 'bg-slate-200 text-slate-700 dark:bg-white/10 dark:text-slate-300'],
        'invalid' => ['Hatalı', 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300'],
    ];
    $versionInfo = [
        '2.1' => 'En geniş uyumluluk: eski telefonlar, Outlook 2007 öncesi. Türkçe karakterler Quoted-Printable kodlanır.',
        '3.0' => 'Önerilen: iPhone, Android, Google Kişiler, macOS ve Outlook ile sorunsuz.',
        '4.0' => 'En yeni standart (RFC 6350): tel: URI, KIND ve UID alanları. Bazı eski uygulamalar desteklemez.',
    ];
    $rows = collect($preview)->when($previewFilter !== 'all', fn ($c) => $c->where('status', $previewFilter));
    $willCreate = $summary['new'] ?? 0;
    $willUpdate = $strategy === 'update' ? ($summary['duplicate'] ?? 0) : 0;
    $card = 'rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10';
@endphp

<div class="space-y-6">
    {{-- Dışa aktar --}}
    <div class="{{ $card }}">
        <div class="flex items-start gap-4">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="font-bold text-slate-900 dark:text-white">Dışa aktar</h3>
                <p class="mt-1 text-sm text-slate-500"><strong>{{ $clientCount }}</strong> müşterinin adı, telefonu, e-postası, firması ve adresi.</p>

                <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_auto]">
                    <fieldset>
                        <legend class="text-sm font-medium text-slate-700 dark:text-slate-200">vCard sürümü</legend>
                        <div class="mt-2 grid gap-2 sm:grid-cols-3">
                            @foreach (\App\Services\ClientContacts::VCARD_VERSIONS as $v)
                                <label @class(['cursor-pointer rounded-xl p-3 ring-1 transition',
                                    'bg-indigo-50 ring-2 ring-indigo-500 dark:bg-indigo-500/10' => $version === $v,
                                    'ring-slate-200 hover:bg-slate-50 dark:ring-white/10 dark:hover:bg-white/5' => $version !== $v])>
                                    <input type="radio" wire:model.live="version" value="{{ $v }}" class="sr-only">
                                    <span class="flex items-center justify-between">
                                        <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">vCard {{ $v }}</span>
                                        @if ($v === '3.0') <span class="rounded bg-emerald-100 px-1.5 text-[10px] font-bold uppercase text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Önerilen</span> @endif
                                    </span>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $versionInfo[$v] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Seçim kaydedilir; müşteri kartındaki vCard indirme ve QR kodu da bu sürümle başlar.</p>
                    </fieldset>
                    <div class="flex flex-row gap-2 lg:flex-col lg:justify-end">
                        <a href="{{ route('clients.export', ['format' => 'vcard', 'version' => $version]) }}"
                            @class(['inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition',
                                'bg-indigo-600 text-white hover:bg-indigo-500' => $clientCount, 'pointer-events-none bg-slate-100 text-slate-400 dark:bg-white/5' => ! $clientCount])>
                            vCard {{ $version }} indir (.vcf)
                        </a>
                        <a href="{{ route('clients.export', ['format' => 'csv']) }}"
                            @class(['inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold ring-1 transition',
                                'text-slate-700 ring-slate-300 hover:bg-slate-50 dark:text-slate-200 dark:ring-white/15 dark:hover:bg-white/5' => $clientCount, 'pointer-events-none text-slate-400 ring-slate-200' => ! $clientCount])>
                            CSV indir
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- İçe aktar --}}
    <div class="{{ $card }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">İçe aktar</h3>
                <p class="mt-1 text-sm text-slate-500">CSV ya da vCard (.vcf). Biçim ve vCard sürümü (2.1 / 3.0 / 4.0) otomatik algılanır; tek dosyada farklı sürümler karışık olabilir. Telefon ya da e-posta eşleşen müşteriler mükerrer sayılır.</p>
            </div>
            @if ($token)
                <button type="button" wire:click="discard" class="text-sm font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Vazgeç</button>
            @endif
        </div>

        @unless ($token)
            <label for="contacts-file"
                x-data="{ over: false, uploading: false, progress: 0 }"
                x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false; progress = 0"
                x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress"
                @dragover.prevent="over = true" @dragleave.prevent="over = false"
                @drop.prevent="over = false; if ($event.dataTransfer.files.length) { $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change')) }"
                :class="over ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 hover:border-slate-400 dark:border-white/15'"
                class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 text-center transition">
                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" /></svg>
                <p class="mt-3 text-sm font-semibold text-slate-800 dark:text-white">.vcf ya da .csv dosyasını sürükleyip bırakın ya da <span class="text-indigo-600 dark:text-indigo-300">seçin</span></p>
                <p class="mt-1 text-xs text-slate-500">iPhone / Android / Google Kişiler / Outlook dışa aktarımları desteklenir · en fazla {{ number_format($maxKb / 1024, 1, ',', '.') }} MB</p>
                <div x-show="uploading" x-cloak class="mt-4 h-1.5 w-56 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                    <div class="h-full bg-indigo-600 transition-all" :style="`width: ${progress}%`"></div>
                </div>
                <p wire:loading wire:target="file" class="mt-3 text-xs font-semibold text-indigo-600">Dosya inceleniyor…</p>
                <input id="contacts-file" x-ref="input" type="file" accept=".vcf,.vcard,.csv,text/vcard,text/csv" wire:model="file" class="sr-only">
            </label>
            @error('file') <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
            @foreach ($parseErrors as $err)
                <p class="mt-2 text-sm font-medium text-rose-600">{{ $err }}</p>
            @endforeach

            <details class="mt-5 rounded-lg bg-slate-50 p-4 text-sm dark:bg-white/5">
                <summary class="cursor-pointer font-semibold text-slate-700 dark:text-slate-200">Desteklenen biçimler</summary>
                <ul class="mt-3 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                    <li><strong>vCard 2.1</strong> — CHARSET ve Quoted-Printable kodlaması, çıplak parametreler (TEL;CELL).</li>
                    <li><strong>vCard 3.0</strong> — TYPE parametreleri, satır katlama, grup önekleri (item1.TEL).</li>
                    <li><strong>vCard 4.0</strong> — tel: / mailto: URI değerleri, çoklu TYPE.</li>
                    <li><strong>CSV</strong> — <code class="font-mono">first_name, last_name, phone, email, company, address</code>; Türkçe başlıklar (ad, soyad, telefon, e-posta, firma, adres) ile Google Kişiler ve Outlook dışa aktarım başlıkları da tanınır.</li>
                </ul>
                <p class="mt-3 text-xs text-slate-500">Telefonu olmayan kişiler atlanır (arıza takibi telefonla doğrulanır). Birden fazla telefon varsa cep telefonu tercih edilir.</p>
            </details>
        @else
            <div class="mt-5 flex flex-wrap items-center gap-2 text-sm">
                <span class="font-semibold text-slate-900 dark:text-white">📇 {{ $fileName }}</span>
                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">Algılanan: {{ $detected }}</span>
                <span class="text-slate-500">· {{ $summary['total'] }} kişi</span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach (['new' => 'text-emerald-600', 'duplicate' => 'text-amber-600', 'repeat' => 'text-slate-600 dark:text-slate-300', 'invalid' => 'text-rose-600'] as $status => $color)
                    <button type="button" wire:click="$set('previewFilter', '{{ $previewFilter === $status ? 'all' : $status }}')"
                        @class(['rounded-xl p-3 text-left ring-1 transition', 'ring-2 ring-indigo-500' => $previewFilter === $status, 'ring-slate-200 hover:bg-slate-50 dark:ring-white/10 dark:hover:bg-white/5' => $previewFilter !== $status])>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $statusMeta[$status][0] }}</p>
                        <p class="text-2xl font-black {{ $color }}">{{ $summary[$status] }}</p>
                    </button>
                @endforeach
            </div>

            @if ($summary['duplicate'])
                <fieldset class="mt-4 rounded-lg bg-amber-50 p-4 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:ring-amber-500/30">
                    <legend class="sr-only">Mükerrer kayıtlar</legend>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ $summary['duplicate'] }} kişi müşteri listenizde zaten var (telefon ya da e-posta eşleşti).</p>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-amber-900 dark:text-amber-100">
                        <label class="inline-flex items-center gap-2"><input type="radio" value="skip" wire:model.live="strategy" class="text-indigo-600"> Atla, mevcut kaydı koru</label>
                        <label class="inline-flex items-center gap-2"><input type="radio" value="update" wire:model.live="strategy" class="text-indigo-600"> Güncelle (dosyadaki dolu alanlar yazılır)</label>
                    </div>
                </fieldset>
            @endif

            <div class="mt-4 max-h-96 overflow-auto rounded-lg ring-1 ring-slate-200 dark:ring-white/10">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="sticky top-0 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900">
                        <tr>
                            <th class="px-3 py-2">#</th><th class="px-3 py-2">Durum</th><th class="px-3 py-2">Kişi</th>
                            <th class="px-3 py-2">Telefon</th><th class="hidden px-3 py-2 md:table-cell">E-posta</th><th class="hidden px-3 py-2 lg:table-cell">Firma</th>
                            @if (str_starts_with((string) $detected, 'vCard')) <th class="px-3 py-2">Sürüm</th> @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-3 py-2 tabular-nums text-slate-400">{{ $row['line'] }}</td>
                                <td class="px-3 py-2"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusMeta[$row['status']][1] }}">{{ $statusMeta[$row['status']][0] }}</span></td>
                                <td class="px-3 py-2">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $row['name'] ?: '—' }}</p>
                                    @foreach ($row['messages'] as $msg) <p class="text-xs text-rose-600">{{ $msg }}</p> @endforeach
                                </td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $row['phone'] ?: '—' }}</td>
                                <td class="hidden px-3 py-2 text-xs md:table-cell">{{ $row['email'] ?: '—' }}</td>
                                <td class="hidden px-3 py-2 text-xs lg:table-cell">{{ $row['company'] ?: '—' }}</td>
                                @if (str_starts_with((string) $detected, 'vCard')) <td class="px-3 py-2 font-mono text-xs">{{ $row['version'] }}</td> @endif
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">Bu durumda kayıt yok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-white/5">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    <strong class="text-emerald-600">{{ $willCreate }}</strong> yeni müşteri
                    @if ($willUpdate) · <strong class="text-amber-600">{{ $willUpdate }}</strong> güncellenecek @endif
                    · {{ $summary['total'] - $willCreate - $willUpdate }} atlanacak
                </p>
                <button type="button" wire:click="import" wire:loading.attr="disabled" wire:target="import" @disabled($willCreate + $willUpdate === 0)
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                    <span wire:loading.remove wire:target="import">İçe aktar</span>
                    <span wire:loading wire:target="import">Aktarılıyor…</span>
                </button>
            </div>
        @endunless
    </div>
</div>
