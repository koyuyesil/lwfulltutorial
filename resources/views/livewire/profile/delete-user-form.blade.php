<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section id="hesabi-sil" class="scroll-mt-24 rounded-2xl bg-white shadow-sm ring-1 ring-rose-200 dark:bg-slate-800 dark:ring-rose-500/30">
    <div class="flex flex-wrap items-center gap-4 p-6">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
        </span>
        <div class="min-w-0 flex-1">
            <h2 class="font-bold text-slate-900 dark:text-white">Hesabı sil</h2>
            <p class="text-sm text-slate-500">Hesabınız silindiğinde tüm müşteri, cihaz ve servis kayıtlarınız kalıcı olarak silinir. Önce dışa aktarmak istediğiniz verileri indirin.</p>
        </div>
        <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="rounded-lg px-4 py-2.5 text-sm font-semibold text-rose-600 ring-1 ring-rose-300 hover:bg-rose-50 dark:ring-rose-500/40 dark:hover:bg-rose-500/10">Hesabı sil…</button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Hesabınızı silmek istediğinize emin misiniz?</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Bu işlem geri alınamaz. Onaylamak için şifrenizi girin.</p>
            <div class="mt-5">
                <x-ui.field label="Şifre" for="password" error="password">
                    <x-ui.input wire:model="password" id="password" name="password" type="password" placeholder="Şifreniz" :invalid="$errors->has('password')" />
                </x-ui.field>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10">Vazgeç</button>
                <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Hesabı kalıcı olarak sil</button>
            </div>
        </form>
    </x-modal>
</section>