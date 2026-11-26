<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section id="guvenlik" class="scroll-mt-24 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
    <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-5 dark:border-white/5">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
        </span>
        <div>
            <h2 class="font-bold text-slate-900 dark:text-white">Şifre</h2>
            <p class="text-sm text-slate-500">Hesabınızı korumak için uzun ve tahmin edilmesi zor bir şifre kullanın.</p>
        </div>
    </header>

    <form wire:submit="updatePassword" class="space-y-5 p-6" x-data="{ show: false }">
        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.field label="Mevcut şifre" for="update_password_current_password" error="current_password">
                <x-ui.input wire:model="current_password" id="update_password_current_password" name="current_password" x-bind:type="show ? 'text' : 'password'" type="password" autocomplete="current-password" :invalid="$errors->has('current_password')" />
            </x-ui.field>
            <x-ui.field label="Yeni şifre" for="update_password_password" error="password">
                <x-ui.input wire:model="password" id="update_password_password" name="password" x-bind:type="show ? 'text' : 'password'" type="password" autocomplete="new-password" :invalid="$errors->has('password')" />
            </x-ui.field>
            <x-ui.field label="Yeni şifre (tekrar)" for="update_password_password_confirmation" error="password_confirmation">
                <x-ui.input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" x-bind:type="show ? 'text' : 'password'" type="password" autocomplete="new-password" />
            </x-ui.field>
        </div>
        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-5 dark:border-white/5">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300"><input type="checkbox" x-model="show" class="rounded border-slate-300 text-indigo-600"> Şifreleri göster</label>
            <div class="flex items-center gap-3">
                <x-action-message class="text-sm font-semibold text-emerald-600" on="password-updated">✓ Şifre güncellendi</x-action-message>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500" wire:loading.attr="disabled">Şifreyi güncelle</button>
            </div>
        </div>
    </form>
</section>