{{-- Tembel yüklenen (lazy) liste bileşenlerinin yer tutucusu --}}
<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="animate-pulse space-y-2">
        <div class="ui-shimmer h-7 w-48 rounded-md bg-slate-200 dark:bg-white/10"></div>
        <div class="ui-shimmer h-3.5 w-72 rounded-md bg-slate-200 dark:bg-white/10"></div>
    </div>
    <x-ui.skeleton variant="table" :rows="6" />
</div>
