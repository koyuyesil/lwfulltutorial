{{--
    İskelet görünüm (içerik yüklenirken). variant: lines | card | table | chart
    rows: satır sayısı (lines / table)
--}}
@props(['variant' => 'lines', 'rows' => 3])
@php $bar = 'ui-shimmer rounded-md bg-slate-200 dark:bg-white/10'; @endphp

<div {{ $attributes->class('animate-pulse') }} aria-busy="true" aria-label="Yükleniyor">
    @switch($variant)
        @case('card')
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                <div class="flex items-center gap-3">
                    <div class="{{ $bar }} h-9 w-9 rounded-xl"></div>
                    <div class="flex-1 space-y-2"><div class="{{ $bar }} h-3.5 w-1/3"></div><div class="{{ $bar }} h-2.5 w-2/3"></div></div>
                </div>
                <div class="mt-5 space-y-2.5">
                    @for ($i = 0; $i < $rows; $i++)<div class="{{ $bar }} h-3" style="width: {{ [92, 78, 85, 64, 88][$i % 5] }}%"></div>@endfor
                </div>
            </div>
            @break
        @case('table')
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                <div class="h-10 bg-slate-50 dark:bg-white/5"></div>
                @for ($i = 0; $i < $rows; $i++)
                    <div class="flex items-center gap-4 border-t border-slate-100 px-4 py-3.5 dark:border-white/5">
                        <div class="{{ $bar }} h-9 w-9 rounded-full"></div>
                        <div class="flex-1 space-y-2"><div class="{{ $bar }} h-3 w-1/3"></div><div class="{{ $bar }} h-2.5 w-1/4"></div></div>
                        <div class="{{ $bar }} hidden h-3 w-24 sm:block"></div>
                        <div class="{{ $bar }} h-6 w-16 rounded-full"></div>
                    </div>
                @endfor
            </div>
            @break
        @case('chart')
            <div class="flex h-56 items-end gap-2 px-2 pt-6">
                @foreach ([40, 65, 50, 80, 58, 72, 45, 90, 62, 76, 54, 68] as $h)<div class="{{ $bar }} flex-1 rounded-t-md rounded-b-none" style="height: {{ $h }}%"></div>@endforeach
            </div>
            @break
        @default
            <div class="space-y-2.5">
                @for ($i = 0; $i < $rows; $i++)<div class="{{ $bar }} h-3" style="width: {{ [100, 86, 72, 94, 60][$i % 5] }}%"></div>@endfor
            </div>
    @endswitch
</div>
