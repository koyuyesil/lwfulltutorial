<x-slot name="header">
    <h2><span class="font-medium text-slate-400">Araçlar /</span> Birim dönüştürücü</h2>
</x-slot>

<div>
    <x-tools-tabs />

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8" wire:ignore>
        <x-ui.page-header eyebrow="Araçlar" title="Birim dönüştürücü" description="Uzunluk, veri, enerji, sıcaklık, basınç ve elektriksel birimler. Bir değeri seçili kategorinin tüm birimlerine çevirir; sonuca tıklayınca kopyalanır." />

        <div x-data="{ copy(text) { window.copyText(text) } }" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6 dark:bg-slate-800/80 dark:ring-white/10">
            @include('livewire.tools.calculators.convert')
        </div>
    </div>
</div>
