<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Şifrenizi mi unuttunuz?</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">E-posta adresinizi yazın; yeni şifre belirlemeniz için bir bağlantı gönderelim.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-4">
        <x-ui.field label="E-posta" for="email" error="email">
            <x-ui.input wire:model="email" id="email" type="email" name="email" required autofocus placeholder="ornek@firma.com" :invalid="$errors->has('email')" />
        </x-ui.field>

        <x-ui.button type="submit" size="lg" class="w-full" loading="sendPasswordResetLink">Sıfırlama bağlantısı gönder</x-ui.button>

        <p class="pt-2 text-center text-sm">
            <a href="{{ route('login') }}" wire:navigate class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">← Girişe dön</a>
        </p>
    </form>
</div>
