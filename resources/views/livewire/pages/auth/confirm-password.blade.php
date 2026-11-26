<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Şifrenizi doğrulayın</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Bu güvenli bir alan. Devam etmeden önce lütfen şifrenizi girin.</p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-4">
        <x-ui.field label="Şifre" for="password" error="password">
            <x-ui.input wire:model="password" id="password" type="password" name="password" required autofocus autocomplete="current-password" :invalid="$errors->has('password')" />
        </x-ui.field>

        <x-ui.button type="submit" size="lg" class="w-full" loading="confirmPassword">Onayla</x-ui.button>
    </form>
</div>
