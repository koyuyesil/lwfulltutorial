@use('App\Enums\ComponentType')
@php
    $card = 'rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10';
    $statusMeta = [
        'new' => ['Yeni', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'],
        'existing' => ['Mevcut', 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300'],
        'repeat' => ['Dosyada tekrar', 'bg-slate-200 text-slate-700 dark:bg-white/10 dark:text-slate-300'],
        'invalid' => ['Hatalı', 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300'],
    ];
@endphp

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        {{-- JSON dışa aktar --}}
        <div class="{{ $card }}">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                </span>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white">JSON dışa aktar</h3>
                    <p class="mt-1 text-sm text-slate-500"><strong>{{ $partCount }}</strong> çapraz referans parçası; bulundukları cihazlar (yeni hesapta cihazı oluşturacak kadar bilgiyle) ve muadilleriyle tek dosyada.</p>
                    <x-ui.button :href="route('cross-reference.export')" :navigate="false" class="mt-4" :aria-disabled="$partCount ? 'false' : 'true'">JSON indir</x-ui.button>
                </div>
            </div>
        </div>
        {{-- Örnek veri --}}
        <div class="{{ $card }}">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" /></svg>
                </span>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white">Örnek veri</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ count(\App\Support\SampleCrossReference::SOCS) }} yonga seti (Snapdragon, Dimensity, Exynos, Tensor, Kirin, Apple) ve kullanıldıkları cihazlar. Önce önizlemede gösterilir.</p>
                    <x-ui.button variant="warning" wire:click="loadSample" loading="loadSample" class="mt-4">Örnek veriyi yükle</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- JSON içe aktar --}}
    <div class="{{ $card }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">JSON içe aktar</h3>
                <p class="mt-1 text-sm text-slate-500">Dosya önce aşağıda tablo olarak gösterilir; <strong>İçe aktar</strong>'a basınca kaydedilir. Aynı cihaz (marka + model ya da kod adı) ve aynı parça (tür + kod) yeniden eklenmez; eksik bağlantıları eklenir.</p>
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
                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
                <p class="mt-3 text-sm font-semibold text-slate-800 dark:text-white">.json dosyasını sürükleyip bırakın ya da <span class="text-indigo-600 dark:text-indigo-300">seçin</span></p>
                <p class="mt-1 text-xs text-slate-500">Bu uygulamanın çapraz referans dışa aktarımı · en fazla {{ number_format($maxKb / 1024, 1, ',', '.') }} MB</p>
                <p wire:loading wire:target="file" class="mt-3 text-xs font-semibold text-indigo-600">Dosya inceleniyor…</p>
                <input x-ref="input" type="file" accept=".json,application/json" wire:model="file" class="sr-only">
            </label>
            @error('file') <p class="mt-2 text-sm font-medium text-rose-600" data-field-error>{{ $message }}</p> @enderror
        @else
            <div class="mt-5 flex flex-wrap items-center gap-2 text-sm">
                <span class="font-semibold text-slate-900 dark:text-white">📄 {{ $fileName }}</span>
                <span class="text-slate-500">· {{ $summary['total'] }} parça · {{ count($previewDevices) }} cihaz</span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ([['Yeni parça', $summary['parts_new'], 'text-emerald-600'], ['Mevcut parça', $summary['parts_existing'], 'text-amber-600'],
                    ['Yeni cihaz', $summary['devices_new'], 'text-sky-600'], ['Bağlantı / muadil', $summary['links'].' / '.$summary['compat'], 'text-slate-700 dark:text-slate-200']] as [$l, $v, $color])
                    <div class="rounded-xl p-3 ring-1 ring-slate-200 dark:ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $l }}</p>
                        <p class="text-2xl font-black {{ $color }}">{{ $v }}</p>
                    </div>
                @endforeach
            </div>
            @if ($summary['parts_invalid'])
                <p class="mt-2 text-sm font-medium text-rose-600">{{ $summary['parts_invalid'] }} hatalı satır atlanacak.</p>
            @endif

            @if ($summary['parts_existing'])
                <fieldset class="mt-4 rounded-lg bg-amber-50 p-4 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:ring-amber-500/30">
                    <legend class="sr-only">Mevcut parçalar</legend>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ $summary['parts_existing'] }} parça zaten kayıtlı. Yeniden eklenmez; dosyadaki cihaz ve muadil bağlantıları eklenir.</p>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-amber-900 dark:text-amber-100">
                        <label class="inline-flex items-center gap-2"><input type="radio" value="skip" wire:model.live="strategy" class="text-indigo-600"> Parça bilgilerini koru</label>
                        <label class="inline-flex items-center gap-2"><input type="radio" value="update" wire:model.live="strategy" class="text-indigo-600"> Dosyadaki bilgilerle güncelle</label>
                    </div>
                </fieldset>
            @endif

            {{-- Önizleme sekmeleri --}}
            <div class="mt-4 inline-flex rounded-lg bg-slate-100 p-1 dark:bg-white/5">
                @foreach (['parts' => 'Parçalar ('.count($preview).')', 'devices' => 'Cihazlar ('.count($previewDevices).')'] as $k => $l)
                    <button type="button" wire:click="$set('previewTab', '{{ $k }}')" @class(['rounded-md px-3 py-1.5 text-sm font-semibold', 'bg-white shadow-sm dark:bg-slate-700 dark:text-white' => $previewTab === $k, 'text-slate-500' => $previewTab !== $k])>{{ $l }}</button>
                @endforeach
            </div>

            <div class="mt-3 max-h-[28rem] overflow-auto rounded-lg ring-1 ring-slate-200 dark:ring-white/10">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    @if ($previewTab === 'parts')
                        <thead class="sticky top-0 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900">
                            <tr><th class="px-3 py-2">Durum</th><th class="px-3 py-2">Parça</th><th class="px-3 py-2">Bulunduğu cihazlar</th><th class="px-3 py-2 text-center">Muadil</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($preview as $row)
                                @php $t = ComponentType::tryFrom((string) $row['type']); @endphp
                                <tr>
                                    <td class="px-3 py-2"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusMeta[$row['status']][1] }}">{{ $statusMeta[$row['status']][0] }}</span></td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            @if ($t)<span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-black {{ $t->badge() }}">{{ $t->short() }}</span>@endif
                                            <span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $row['code'] ?? $row['name'] }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $row['code'] ? $row['name'] : '' }}{{ $row['manufacturer'] ? ' · '.$row['manufacturer'] : '' }}</p>
                                        @foreach ($row['errors'] as $err)<p class="text-xs text-rose-600">{{ $err }}</p>@endforeach
                                    </td>
                                    <td class="max-w-md px-3 py-2 text-xs text-slate-600 dark:text-slate-300">{{ $row['devices'] ? implode(', ', $row['devices']) : '-' }}</td>
                                    <td class="px-3 py-2 text-center text-xs">{{ $row['compat'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @else
                        <thead class="sticky top-0 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900">
                            <tr><th class="px-3 py-2">Durum</th><th class="px-3 py-2">Cihaz</th><th class="px-3 py-2">Kod adı</th><th class="hidden px-3 py-2 md:table-cell">Model no</th><th class="hidden px-3 py-2 md:table-cell">Yonga seti</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @foreach ($previewDevices as $row)
                                <tr>
                                    <td class="px-3 py-2"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusMeta[$row['status']][1] }}">{{ $row['status'] === 'existing' ? 'Katalogda var' : $statusMeta[$row['status']][0] }}</span></td>
                                    <td class="px-3 py-2 font-semibold text-slate-900 dark:text-white">{{ $row['brand'] }} {{ $row['model_name'] }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $row['codename'] ?: '-' }}</td>
                                    <td class="hidden px-3 py-2 font-mono text-xs md:table-cell">{{ $row['model_number'] ?: '-' }}</td>
                                    <td class="hidden px-3 py-2 text-xs md:table-cell">{{ $row['chipset'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-white/5">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    <strong class="text-emerald-600">{{ $summary['parts_new'] }}</strong> yeni parça ·
                    <strong class="text-sky-600">{{ $summary['devices_new'] }}</strong> yeni cihaz ·
                    {{ $summary['links'] }} cihaz bağlantısı · {{ $summary['compat'] }} muadillik
                </p>
                <x-ui.button wire:click="import" loading="import">İçe aktar</x-ui.button>
            </div>
        @endunless

        <details class="mt-5 rounded-lg bg-slate-50 p-4 text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">
            <summary class="cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200">Dosya biçimi</summary>
            <pre class="mt-3 overflow-x-auto rounded-lg bg-slate-900 p-3 font-mono text-[11px] leading-relaxed text-slate-100">{
  "format": "{{ \App\Services\CrossReferenceCatalog::FORMAT }}", "version": 1,
  "devices": [{ "key": "d1", "brand": "Xiaomi", "model_name": "Redmi Note 13 Pro",
                "model_number": "2312DRA50G", "codename": "garnet", "release_year": 2024,
                "chipset": "Snapdragon 7s Gen 2", "device_type": "phone" }],
  "parts": [{ "key": "p1", "type": "soc", "code": "SM7435", "name": "Snapdragon 7s Gen 2",
              "manufacturer": "Qualcomm", "specs": { "Üretim süreci": "4 nm" },
              "devices": [{ "device": "d1", "variant": "8/256" }],
              "compatible": [{ "part": "p2", "kind": "pin", "note": "…" }] }]
}</pre>
            <p class="mt-2">type: {{ collect(ComponentType::cases())->map->value->implode(' · ') }} · kind: direct · pin · software</p>
        </details>
    </div>
</div>
