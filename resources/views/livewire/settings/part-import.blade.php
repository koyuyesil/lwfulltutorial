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
    $card = 'rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10';
    $money = fn ($v) => \App\Models\Ticket::money((float) $v);
@endphp

<div class="space-y-6">
    {{-- Dışa aktar --}}
    <div class="{{ $card }}">
        <div class="flex items-start gap-4">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 class="font-bold text-slate-900 dark:text-white">CSV dışa aktar</h3>
                <p class="mt-1 text-sm text-slate-500"><strong>{{ $partCount }}</strong> parçanın stok kodu, kategorisi, fiyatları, stok bilgisi ve tedarikçisi. Excel'de açılabilir. Cihaz ve muadil ilişkileri <a href="{{ route('settings', 'capraz-referans') }}" wire:navigate class="font-semibold text-indigo-600">Çapraz referans JSON</a>'unda taşınır.</p>
                <x-ui.button :href="route('parts.export')" :navigate="false" class="mt-4" :aria-disabled="$partCount ? 'false' : 'true'">CSV indir</x-ui.button>
            </div>
        </div>
    </div>

    {{-- İçe aktar --}}
    <div class="{{ $card }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">CSV içe aktar</h3>
                <p class="mt-1 text-sm text-slate-500">Önce önizlemede gösterilir; <strong>İçe aktar</strong>'a basınca kaydedilir. Stok kodu, tür + parça kodu ya da ad eşleşen parçalar mevcut sayılır.</p>
            </div>
            @if ($token)
                <button type="button" wire:click="discard" class="text-sm font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white">Vazgeç</button>
            @endif
        </div>

        @unless ($token)
            <label x-data="{ over: false }" @dragover.prevent="over = true" @dragleave.prevent="over = false"
                @drop.prevent="over = false; if ($event.dataTransfer.files.length) { $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change')) }"
                :class="over ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 hover:border-slate-400 dark:border-white/15'"
                class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 text-center transition">
                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                <p class="mt-3 text-sm font-semibold text-slate-800 dark:text-white">CSV dosyasını sürükleyip bırakın ya da <span class="text-indigo-600 dark:text-indigo-300">seçin</span></p>
                <p class="mt-1 text-xs text-slate-500">Virgül, noktalı virgül ya da sekmeyle ayrılmış · en fazla {{ number_format($maxKb / 1024, 1, ',', '.') }} MB</p>
                <p wire:loading wire:target="file" class="mt-3 text-xs font-semibold text-indigo-600">Dosya inceleniyor…</p>
                <input x-ref="input" type="file" accept=".csv,text/csv" wire:model="file" class="sr-only">
            </label>
            @error('file') <p class="mt-2 text-sm font-medium text-rose-600" data-field-error>{{ $message }}</p> @enderror

            <details class="mt-5 rounded-lg bg-slate-50 p-4 text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">
                <summary class="cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200">Sütunlar</summary>
                <p class="mt-3"><code class="font-mono text-indigo-600 dark:text-indigo-300">{{ implode(', ', \App\Services\PartCatalog::COLUMNS) }}</code></p>
                <p class="mt-1">Türkçe başlıklar da tanınır: Ad, Stok kodu, Kategori, Tür, Kod, Üretici, Alış, Satış, Stok, Min stok, Tedarikçi, Uyumlu, Not. Yalnızca <strong>name / Ad</strong> zorunludur. Fiyatlarda 1250,50 ya da 1250.50 yazılabilir.</p>
            </details>
        @else
            <div class="mt-5 flex flex-wrap items-center gap-2 text-sm">
                <span class="font-semibold text-slate-900 dark:text-white">📄 {{ $fileName }}</span>
                <span class="text-slate-500">· {{ $summary['total'] }} satır</span>
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
                    <legend class="sr-only">Mevcut parçalar</legend>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ $summary['duplicate'] }} parça zaten kayıtlı.</p>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-amber-900 dark:text-amber-100">
                        <label class="inline-flex items-center gap-2"><input type="radio" value="skip" wire:model.live="strategy" class="text-indigo-600"> Atla, mevcut kaydı koru</label>
                        <label class="inline-flex items-center gap-2"><input type="radio" value="update" wire:model.live="strategy" class="text-indigo-600"> Güncelle (dosyadaki dolu alanlar yazılır)</label>
                    </div>
                </fieldset>
            @endif

            <div class="mt-4 max-h-96 overflow-auto rounded-lg ring-1 ring-slate-200 dark:ring-white/10">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="sticky top-0 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900">
                        <tr><th class="px-3 py-2">#</th><th class="px-3 py-2">Durum</th><th class="px-3 py-2">Parça</th><th class="hidden px-3 py-2 md:table-cell">Kategori</th><th class="px-3 py-2 text-right">Satış</th><th class="px-3 py-2 text-center">Stok</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-3 py-2 tabular-nums text-slate-400">{{ $row['line'] }}</td>
                                <td class="px-3 py-2"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusMeta[$row['status']][1] }}">{{ $statusMeta[$row['status']][0] }}</span></td>
                                <td class="px-3 py-2">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $row['name'] ?: '-' }}</p>
                                    <p class="font-mono text-xs text-slate-500">{{ collect([$row['sku'], $row['code']])->filter()->implode(' · ') }}</p>
                                    @foreach ($row['messages'] as $msg) <p class="text-xs text-rose-600">{{ $msg }}</p> @endforeach
                                </td>
                                <td class="hidden px-3 py-2 text-xs md:table-cell">{{ $row['category'] ?: '-' }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs">{{ $money($row['price']) }}</td>
                                <td class="px-3 py-2 text-center font-mono text-xs">{{ $row['stock'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">Bu durumda kayıt yok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-white/5">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    <strong class="text-emerald-600">{{ $willCreate }}</strong> yeni parça
                    @if ($willUpdate) · <strong class="text-amber-600">{{ $willUpdate }}</strong> güncellenecek @endif
                    · {{ $summary['total'] - $willCreate - $willUpdate }} atlanacak
                </p>
                <x-ui.button wire:click="import" loading="import" :disabled="$willCreate + $willUpdate === 0">İçe aktar</x-ui.button>
            </div>
        @endunless
    </div>
</div>
