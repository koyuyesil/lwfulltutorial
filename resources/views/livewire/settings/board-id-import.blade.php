@php
    $statusMeta = [
        'new' => ['Yeni', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'],
        'duplicate' => ['Mevcut', 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300'],
        'repeat' => ['Dosyada tekrar', 'bg-slate-200 text-slate-700 dark:bg-white/10 dark:text-slate-300'],
        'invalid' => ['Hatalı', 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300'],
    ];
    $rows = collect($preview)->when($previewFilter !== 'all', fn ($c) => $c->where('status', $previewFilter));
    $willCreate = $summary['new'] ?? 0;
    $willUpdate = $strategy === 'update' ? ($summary['duplicate'] ?? 0) : 0;
    $ohm = fn ($v) => $v === null ? '—' : \App\Support\Si::format($v);
@endphp

<div class="space-y-6">
    {{-- Dışa aktar + örnek --}}
    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                </span>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white">CSV dışa aktar</h3>
                    <p class="mt-1 text-sm text-slate-500">Kütüphanenizdeki <strong>{{ $boardCount }}</strong> Board ID kaydı; dirençler, orijinal değerler, HWID'ler, yöntemler ve notlarla tek dosyada.</p>
                    <a href="{{ route('board-ids.export') }}" @class(['mt-4 inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition',
                        'bg-indigo-600 text-white hover:bg-indigo-500' => $boardCount,
                        'pointer-events-none bg-slate-100 text-slate-400 dark:bg-white/5' => ! $boardCount])>
                        CSV indir
                    </a>
                </div>
            </div>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                </span>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white">Örnek veri</h3>
                    <p class="mt-1 text-sm text-slate-500">Servis direnç tablosundan dönüştürülmüş <strong>{{ $sampleCount }}</strong> gerçek kayıt (apollo, corot, garnet, vayu, zircon…). Hepsi dahili pull-up devresi olarak gelir.</p>
                    <a href="{{ route('board-ids.sample') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-300 hover:bg-emerald-50 dark:text-emerald-300 dark:ring-emerald-500/30 dark:hover:bg-emerald-500/10">
                        Örnek CSV indir
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- İçe aktar --}}
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">CSV içe aktar</h3>
                <p class="mt-1 text-sm text-slate-500">Kayıtlar kod adıyla modelinize bağlanır; modeli olmayan kod adları için model otomatik oluşturulur. Aynı model + build + HWID + R2 olan kayıtlar mükerrer sayılır.</p>
            </div>
            @if ($token)
                <button type="button" wire:click="discard" class="text-sm font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Vazgeç</button>
            @endif
        </div>

        @unless ($token)
            <label for="board-file"
                x-data="{ over: false, uploading: false, progress: 0 }"
                x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false; progress = 0"
                x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress"
                @dragover.prevent="over = true" @dragleave.prevent="over = false"
                @drop.prevent="over = false; if ($event.dataTransfer.files.length) { $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change')) }"
                :class="over ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 hover:border-slate-400 dark:border-white/15'"
                class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 text-center transition">
                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                <p class="mt-3 text-sm font-semibold text-slate-800 dark:text-white">CSV dosyasını sürükleyip bırakın ya da <span class="text-indigo-600 dark:text-indigo-300">seçin</span></p>
                <p class="mt-1 text-xs text-slate-500">En fazla {{ number_format($maxKb / 1024, 1, ',', '.') }} MB · {{ number_format(\App\Services\BoardIdCatalog::MAX_ROWS, 0, ',', '.') }} satır</p>
                <div x-show="uploading" x-cloak class="mt-4 h-1.5 w-56 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                    <div class="h-full bg-indigo-600 transition-all" :style="{ width: progress + '%' }"></div>
                </div>
                <p wire:loading wire:target="file" class="mt-3 text-xs font-semibold text-indigo-600">Dosya inceleniyor…</p>
                <input id="board-file" x-ref="input" type="file" accept=".csv,text/csv" wire:model="file" class="sr-only">
            </label>
            @error('file') <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror
            @foreach ($parseErrors as $err)
                <p class="mt-2 text-sm font-medium text-rose-600">{{ $err }}</p>
            @endforeach

            <details class="mt-5 rounded-lg bg-slate-50 p-4 text-sm dark:bg-white/5">
                <summary class="cursor-pointer font-semibold text-slate-700 dark:text-slate-200">Sütunlar</summary>
                <div class="mt-3 grid gap-x-6 gap-y-1 text-xs text-slate-600 sm:grid-cols-2 dark:text-slate-300">
                    @foreach ([
                        'codename' => 'Kod adı (ör. garnet) — model eşleştirme',
                        'brand / model_name' => 'Marka ve model adı (kod adı yoksa zorunlu)',
                        'build_name' => 'Build adı (boşsa kod adı)',
                        'divider_type' => 'adc · logic · pullup (boşsa pullup)',
                        'chip' => 'pmic · soc (boşsa pmic)',
                        'vref' => 'Referans gerilim, V (boşsa 1.8)',
                        'r1 / r2' => 'Kartta takılı direnç: 10k, 4.7k, 1M · open = takılı değil',
                        'r1_original / r2_original' => 'Onarım öncesi orijinal değer',
                        'mass_production_hwid' => 'Seri üretim HWID (ör. CN-MP-17.9.0)',
                        'pre_production_hwid' => 'Direnç değişimi sonrası HWID',
                        'repair_methods' => 'resistor|tmp_root|patch … (| ile ayrılır)',
                        'description' => 'Açıklama / servis notu',
                    ] as $col => $desc)
                        <p><code class="font-mono font-semibold text-indigo-600 dark:text-indigo-300">{{ $col }}</code> — {{ $desc }}</p>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-500">"22-27k" gibi aralıklarda alt değer alınır, yazılan hali açıklamaya eklenir. Bozuk Türkçe karakterler (ör. "s�kmeden") otomatik onarılır.</p>
            </details>
        @else
            <div class="mt-5 flex flex-wrap items-center gap-2 text-sm">
                <span class="font-semibold text-slate-900 dark:text-white">📄 {{ $fileName }}</span>
                <span class="text-slate-500">· {{ $summary['total'] }} kayıt · {{ $summary['models'] }} kod adı</span>
            </div>
            @foreach ($parseWarnings as $warn)
                <p class="mt-2 text-xs font-medium text-amber-700 dark:text-amber-300">{{ $warn }}</p>
            @endforeach
            @foreach ($parseErrors as $err)
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $err }}</p>
            @endforeach

            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach (['new' => 'emerald', 'duplicate' => 'amber', 'repeat' => 'slate', 'invalid' => 'rose'] as $status => $tone)
                    <button type="button" wire:click="$set('previewFilter', '{{ $previewFilter === $status ? 'all' : $status }}')"
                        @class(['rounded-xl p-3 text-left ring-1 transition', 'ring-2 ring-indigo-500' => $previewFilter === $status, 'ring-slate-200 hover:bg-slate-50 dark:ring-white/10 dark:hover:bg-white/5' => $previewFilter !== $status])>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $statusMeta[$status][0] }}</p>
                        <p @class(['text-2xl font-black', 'text-emerald-600' => $tone === 'emerald', 'text-amber-600' => $tone === 'amber', 'text-slate-600 dark:text-slate-300' => $tone === 'slate', 'text-rose-600' => $tone === 'rose'])>{{ $summary[$status] }}</p>
                    </button>
                @endforeach
            </div>

            @if ($summary['duplicate'])
                <fieldset class="mt-4 rounded-lg bg-amber-50 p-4 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:ring-amber-500/30">
                    <legend class="sr-only">Mükerrer kayıtlar</legend>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ $summary['duplicate'] }} kayıt kütüphanenizde zaten var. Ne yapılsın?</p>
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
                            <th class="px-3 py-2">Satır</th><th class="px-3 py-2">Durum</th><th class="px-3 py-2">Kod adı</th>
                            <th class="px-3 py-2 text-right">R2</th><th class="hidden px-3 py-2 text-right md:table-cell">R2 orijinal</th>
                            <th class="hidden px-3 py-2 lg:table-cell">HWID → sonrası</th><th class="px-3 py-2 text-center">Yöntem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-3 py-2 tabular-nums text-slate-400">{{ $row['line'] ?? '—' }}</td>
                                <td class="px-3 py-2"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusMeta[$row['status']][1] }}">{{ $statusMeta[$row['status']][0] }}</span></td>
                                <td class="px-3 py-2">
                                    <p class="font-mono font-semibold text-slate-900 dark:text-white">{{ $row['codename'] ?: '—' }}</p>
                                    @if ($row['build'] !== $row['codename'])<p class="text-xs text-slate-500">{{ $row['build'] }}</p>@endif
                                    @foreach ($row['errors'] as $msg)
                                        <p class="text-xs text-rose-600">{{ $msg }}</p>
                                    @endforeach
                                </td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums">{{ $ohm($row['r2']) }}</td>
                                <td class="hidden px-3 py-2 text-right font-mono text-xs tabular-nums text-slate-500 md:table-cell">{{ $ohm($row['r2_org']) }}</td>
                                <td class="hidden px-3 py-2 font-mono text-xs lg:table-cell">
                                    {{ $row['hw'] ?: '—' }} <span class="text-slate-400">→</span> {{ $row['after'] ?: '—' }}
                                </td>
                                <td class="px-3 py-2 text-center tabular-nums text-xs">{{ $row['methods'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">Bu durumda satır yok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($summary['total'] > count($preview))
                <p class="mt-2 text-xs text-slate-500">Önizlemede ilk {{ count($preview) }} satır gösteriliyor; içe aktarmada tümü işlenir.</p>
            @endif

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-white/5">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    <strong class="text-emerald-600">{{ $willCreate }}</strong> yeni kayıt eklenecek
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
