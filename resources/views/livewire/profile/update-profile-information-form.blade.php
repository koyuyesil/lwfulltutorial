<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section id="hesap" class="scroll-mt-24 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
    <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-5 dark:border-white/5">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
        </span>
        <div>
            <h2 class="font-bold text-slate-900 dark:text-white">Hesap bilgileri</h2>
            <p class="text-sm text-slate-500">Giriş yaptığınız ad ve e-posta adresi.</p>
        </div>
    </header>

    <form wire:submit="updateProfileInformation" class="space-y-5 p-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.field label="Ad soyad" for="name" error="name">
                <x-ui.input wire:model="name" id="name" name="name" type="text" required autocomplete="name" :invalid="$errors->has('name')" />
            </x-ui.field>
            <x-ui.field label="E-posta" for="email" error="email">
                <x-ui.input wire:model="email" id="email" name="email" type="email" required autocomplete="username" :invalid="$errors->has('email')" />
            </x-ui.field>
        </div>

        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
            <div class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-100 dark:ring-amber-500/30">
                E-posta adresiniz doğrulanmadı.
                <button wire:click.prevent="sendVerification" class="font-semibold underline">Doğrulama bağlantısını yeniden gönder</button>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-1 font-medium text-emerald-700 dark:text-emerald-300">Yeni doğrulama bağlantısı e-posta adresinize gönderildi.</p>
                @endif
            </div>
        @endif

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 dark:border-white/5">
            <x-action-message class="text-sm font-semibold text-emerald-600" on="profile-updated">✓ Kaydedildi</x-action-message>
            <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500" wire:loading.attr="disabled">Kaydet</button>
        </div>
    </form>
</section>