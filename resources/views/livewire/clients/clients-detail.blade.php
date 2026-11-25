<div class="relative flex min-h-0 flex-1 flex-col">
    <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/70 dark:bg-slate-900/70">
        <x-koyu-svg-loading />
    </div>
    @if ($client)
        @include('livewire.clients.partials.detail', ['client' => $client, 'avatarClass' => $avatarClass, 'vcards' => $vcards, 'vcardVersion' => $vcardVersion])
    @else
        <div class="flex-1"></div>
    @endif
</div>
