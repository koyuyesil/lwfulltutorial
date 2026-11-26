<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Tekrar hoş geldiniz</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Servis panelinize erişmek için giriş yapın.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <x-ui.field label="E-posta" for="email" error="form.email">
            <x-ui.input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="ornek@firma.com" :invalid="$errors->has('form.email')" />
        </x-ui.field>

        <x-ui.field label="Şifre" for="password" error="form.password">
            <x-ui.input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" :invalid="$errors->has('form.password')" />
        </x-ui.field>

        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-white/20 dark:bg-slate-950">
                Beni hatırla
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Şifremi unuttum</a>
            @endif
        </div>

        <x-ui.button type="submit" size="lg" class="w-full" loading="login">Giriş yap</x-ui.button>

        @if (Route::has('register'))
            <p class="pt-2 text-center text-sm text-slate-500 dark:text-slate-400">
                Hesabınız yok mu?
                <a href="{{ route('register') }}" wire:navigate class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Ücretsiz kayıt olun</a>
            </p>
        @endif
    </form>
</div>
