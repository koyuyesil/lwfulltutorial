<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">E-posta adresinizi doğrulayın</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kaydınız için teşekkürler! Başlamadan önce e-postanıza gönderdiğimiz bağlantıya tıklayarak adresinizi doğrulayın. E-posta gelmediyse yenisini gönderebiliriz.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status class="mb-4" status="Kayıt sırasında verdiğiniz e-posta adresine yeni bir doğrulama bağlantısı gönderildi." />
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-ui.button wire:click="sendVerification" loading="sendVerification">Doğrulama e-postasını yeniden gönder</x-ui.button>
        <x-ui.button wire:click="logout" variant="ghost">Çıkış yap</x-ui.button>
    </div>
</div>
