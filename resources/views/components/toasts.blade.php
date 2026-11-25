{{-- Livewire: $this->dispatch('toast', message: 'Kaydedildi', type: 'success'|'error'|'info') --}}
<div x-data="{
        toasts: [],
        add(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type: 'success', ...detail });
            setTimeout(() => this.remove(id), detail.timeout ?? 4000);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id) },
    }"
    x-on:toast.window="add($event.detail)"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-[70] flex flex-col items-center gap-2 p-4 sm:items-end"
    aria-live="polite">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl bg-slate-900 px-4 py-3 text-sm text-white shadow-2xl ring-1 ring-white/10 dark:bg-white dark:text-slate-900">
            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full text-xs font-bold"
                :class="{ 'bg-emerald-500 text-white': toast.type === 'success', 'bg-rose-500 text-white': toast.type === 'error', 'bg-sky-500 text-white': toast.type === 'info' }"
                x-text="toast.type === 'error' ? '!' : (toast.type === 'info' ? 'i' : '✓')"></span>
            <p class="flex-1" x-text="toast.message"></p>
            <button type="button" @click="remove(toast.id)" class="text-slate-400 hover:text-white dark:hover:text-slate-900" aria-label="Kapat">✕</button>
        </div>
    </template>
</div>
