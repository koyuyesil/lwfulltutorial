@use('App\Enums\ComponentType')
@php
    $card = 'rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10';
@endphp

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        {{-- CSV dışa aktar --}}
        <div class="{{ $card }}">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                </span>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white">CSV dışa aktar</h3>
                    <p class="mt-1 text-sm text-slate-500">Kütüphanedeki <strong>{{ $componentCount }}</strong> bileşen; cihaz bağlantıları ve uyumluluklarıyla tek dosyada. Yedek ya da başka hesaba taşımak için.</p>
                    <a href="{{ route('cross-reference.export') }}" @class(['mt-4 inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition',
                        'bg-indigo-600 text-white hover:bg-indigo-500' => $componentCount, 'pointer-events-none bg-slate-100 text-slate-400 dark:bg-white/5' => ! $componentCount])>CSV indir</a>
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
                    <p class="mt-1 text-sm text-slate-500">{{ count(\App\Support\SampleCrossReference::SOCS) }} yonga seti (Snapdragon, Dimensity, Exynos, Tensor, Kirin, Apple) ve katalogdaki cihazlara bağlantıları. Kodu doğrulanmamış olanlarda not vardır; mevcut bileşenler güncellenir, silinmez.</p>
                    <button type="button" wire:click="loadSample" wire:loading.attr="disabled" wire:target="loadSample"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">
                        <span wire:loading.remove wire:target="loadSample">Örnek veriyi yükle</span><span wire:loading wire:target="loadSample">Yükleniyor…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- CSV içe aktar --}}
    <div class="{{ $card }}">
        <h3 class="font-bold text-slate-900 dark:text-white">CSV içe aktar</h3>
        <p class="mt-1 text-sm text-slate-500">Aynı tür ve kodla kayıtlı bileşenler güncellenir; cihaz bağlantıları ve uyumluluklar eklenir.</p>

        <label x-data="{ over: false }" @dragover.prevent="over = true" @dragleave.prevent="over = false"
            @drop.prevent="over = false; if ($event.dataTransfer.files.length) { $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change')) }"
            :class="over ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 hover:border-slate-400 dark:border-white/15'"
            class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-10 text-center transition">
            <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
            <p class="mt-3 text-sm font-semibold text-slate-800 dark:text-white">CSV dosyasını sürükleyip bırakın ya da <span class="text-indigo-600 dark:text-indigo-300">seçin</span></p>
            <p class="mt-1 text-xs text-slate-500">En fazla {{ number_format($maxKb / 1024, 1, ',', '.') }} MB</p>
            <p wire:loading wire:target="file" class="mt-3 text-xs font-semibold text-indigo-600">İçe aktarılıyor…</p>
            <input x-ref="input" type="file" accept=".csv,text/csv" wire:model="file" class="sr-only">
        </label>
        @error('file') <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p> @enderror

        <label class="mt-4 flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
            <input type="checkbox" wire:model="createDevices" class="mt-0.5 rounded border-slate-300 text-indigo-600">
            <span>Katalogda olmayan cihazları "Marka &gt; Model" yazımından oluştur
                <span class="block text-xs text-slate-500">Kapalıysa eşleşmeyen cihazlar raporda listelenir. Örnek veri yüklerken de geçerlidir.</span></span>
        </label>

        @if ($report)
            <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm ring-1 ring-slate-200 dark:bg-white/5 dark:ring-white/10">
                <p class="font-semibold text-slate-900 dark:text-white">Son içe aktarma</p>
                <ul class="mt-2 space-y-0.5 text-slate-600 dark:text-slate-300">
                    <li>{{ $report['components_created'] }} yeni, {{ $report['components_updated'] }} güncellenen bileşen</li>
                    <li>{{ $report['links'] }} cihaz bağlantısı · {{ $report['compat'] }} uyumluluk{{ $report['products_created'] ? ' · '.$report['products_created'].' yeni cihaz' : '' }}</li>
                    @if ($report['invalid'])<li class="text-rose-600">{{ count($report['invalid']) }} geçersiz satır atlandı</li>@endif
                </ul>
                @if ($report['unmatched'])
                    <details class="mt-2">
                        <summary class="cursor-pointer text-xs font-semibold text-amber-700 dark:text-amber-300">{{ count($report['unmatched']) }} eşleşmeyen kayıt</summary>
                        <p class="mt-1 text-xs text-slate-500">{{ implode(' · ', array_slice($report['unmatched'], 0, 80)) }}</p>
                    </details>
                @endif
                <a href="{{ route('cross-reference.index') }}" wire:navigate class="mt-3 inline-block text-xs font-semibold text-indigo-600">Çapraz referansa git →</a>
            </div>
        @endif

        <details class="mt-5 rounded-lg bg-slate-50 p-4 text-xs text-slate-600 dark:bg-white/5 dark:text-slate-300">
            <summary class="cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200">Sütunlar</summary>
            <ul class="mt-3 space-y-1">
                <li><code class="font-mono font-semibold text-indigo-600 dark:text-indigo-300">type</code> — {{ collect(ComponentType::cases())->map->value->implode(' · ') }}</li>
                <li><code class="font-mono font-semibold text-indigo-600 dark:text-indigo-300">code</code> — parça / model kodu (zorunlu) · <code class="font-mono text-indigo-600 dark:text-indigo-300">name</code>, <code class="font-mono text-indigo-600 dark:text-indigo-300">manufacturer</code>, <code class="font-mono text-indigo-600 dark:text-indigo-300">notes</code></li>
                <li><code class="font-mono font-semibold text-indigo-600 dark:text-indigo-300">specs</code> — "Bellek tipi=LPDDR5; Kapasite=8 GB"</li>
                <li><code class="font-mono font-semibold text-indigo-600 dark:text-indigo-300">devices</code> — "Xiaomi &gt; Redmi Note 13 Pro [8/256]; garnet"</li>
                <li><code class="font-mono font-semibold text-indigo-600 dark:text-indigo-300">compatible</code> — "ram:K3LK7K70BM-BGCP:pin; ram:H58G56AK6BX069:direct" (direct | pin | software)</li>
            </ul>
        </details>
    </div>
</div>
