<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Yeni şifre belirleyin</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Hesabınız için yeni bir şifre seçin.</p>
    </div>

    <form wire:submit="resetPassword" class="space-y-4">
        <x-ui.field label="E-posta" for="email" error="email">
            <x-ui.input wire:model="email" id="email" type="email" name="email" required autofocus autocomplete="username" :invalid="$errors->has('email')" />
        </x-ui.field>

        <x-ui.field label="Yeni şifre" for="password" error="password" hint="En az 8 karakter.">
            <x-ui.input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" :invalid="$errors->has('password')" />
        </x-ui.field>

        <x-ui.field label="Yeni şifre tekrarı" for="password_confirmation" error="password_confirmation">
            <x-ui.input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" :invalid="$errors->has('password_confirmation')" />
        </x-ui.field>

        <x-ui.button type="submit" size="lg" class="w-full" loading="resetPassword">Şifreyi sıfırla</x-ui.button>
    </form>
</div>
