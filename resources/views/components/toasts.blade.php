{{--
    Livewire: $this->dispatch('toast', message: 'Kaydedildi', type: 'success'|'error'|'info'|'warning')
    İsteğe bağlı: timeout (ms) ve action: ['label' => 'Geri al', 'event' => 'trash-restore', 'params' => [...]]
    Eylem düğmesine basılınca Livewire olayı gönderilir ve bildirim kapanır.
--}}
<div x-data="{
        toasts: [],
        add(detail) {
            const id = Date.now() + Math.random();
            const timeout = detail.timeout ?? (detail.action ? 8000 : 4000);
            this.toasts.push({ id, type: 'success', ...detail, timeout });
            this.toasts = this.toasts.slice(-4);
            setTimeout(() => this.remove(id), timeout);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id) },
        run(toast) {
            const a = toast.action;
            if (a?.event) window.Livewire?.dispatch(a.event, a.params ?? {});
            this.remove(toast.id);
        },
    }"
    x-on:toast.window="add($event.detail)"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-[70] flex flex-col items-center gap-2 p-4 sm:items-end"
    aria-live="polite">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            class="pointer-events-auto relative flex w-full max-w-sm items-center gap-3 overflow-hidden rounded-xl bg-slate-900 px-4 py-3 text-sm text-white shadow-2xl ring-1 ring-white/10 dark:bg-white dark:text-slate-900">
            <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full text-xs font-bold text-white"
                :class="{ 'bg-emerald-500': toast.type === 'success', 'bg-rose-500': toast.type === 'error', 'bg-sky-500': toast.type === 'info', 'bg-amber-500': toast.type === 'warning' }"
                x-text="toast.type === 'error' ? '!' : (toast.type === 'info' ? 'i' : (toast.type === 'warning' ? '!' : '✓'))"></span>
            <p class="flex-1" x-text="toast.message"></p>
            <template x-if="toast.action">
                <button type="button" @click="run(toast)" x-text="toast.action.label ?? 'Geri al'"
                    class="shrink-0 rounded-md px-2 py-1 text-sm font-bold text-indigo-300 hover:bg-white/10 hover:text-indigo-200 dark:text-indigo-600 dark:hover:bg-indigo-50"></button>
            </template>
            <button type="button" @click="remove(toast.id)" class="shrink-0 text-slate-400 hover:text-white dark:hover:text-slate-900" aria-label="Kapat">✕</button>
            {{-- Kalan süre --}}
            <template x-if="toast.action">
                <span class="absolute inset-x-0 bottom-0 h-0.5 origin-left bg-indigo-400/70"
                    x-init="$el.animate([{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }], { duration: toast.timeout, easing: 'linear', fill: 'forwards' })"></span>
            </template>
        </div>
    </template>
</div>
