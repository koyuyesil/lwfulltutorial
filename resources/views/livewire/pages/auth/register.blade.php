<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Hesap oluşturun</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Birkaç saniyede servisinizi dijitale taşıyın.</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <x-ui.field label="Ad soyad" for="name" error="name">
            <x-ui.input wire:model="name" id="name" type="text" name="name" required autofocus autocomplete="name" :invalid="$errors->has('name')" />
        </x-ui.field>

        <x-ui.field label="E-posta" for="email" error="email">
            <x-ui.input wire:model="email" id="email" type="email" name="email" required autocomplete="username" placeholder="ornek@firma.com" :invalid="$errors->has('email')" />
        </x-ui.field>

        <x-ui.field label="Şifre" for="password" error="password" hint="En az 8 karakter.">
            <x-ui.input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" :invalid="$errors->has('password')" />
        </x-ui.field>

        <x-ui.field label="Şifre tekrarı" for="password_confirmation" error="password_confirmation">
            <x-ui.input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" :invalid="$errors->has('password_confirmation')" />
        </x-ui.field>

        <x-ui.button type="submit" size="lg" class="w-full" loading="register">Kayıt ol</x-ui.button>

        <p class="pt-2 text-center text-sm text-slate-500 dark:text-slate-400">
            Zaten hesabınız var mı?
            <a href="{{ route('login') }}" wire:navigate class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Giriş yapın</a>
        </p>
    </form>
</div>
