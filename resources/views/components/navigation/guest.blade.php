@php
    $links = [
        ['route' => 'home', 'label' => __('Anasayfa'), 'active' => 'home'],
        ['route' => 'services.index', 'label' => __('Servisler'), 'active' => 'services.*'],
    ];
@endphp

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @foreach ($links as $link)
                        <x-nav-link :href="route($link['route'])" :active="request()->routeIs($link['active'])" wire:navigate.hover>
                            {{ $link['label'] }}
                        </x-nav-link>
                    @endforeach
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate.hover>
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Account Links -->
            <div class="hidden sm:flex sm:items-center sm:gap-3 sm:ms-6">
                @auth
                    <a href="{{ route('profile') }}" wire:navigate class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                        {{ auth()->user()->name }}
                    </a>
                @else
                    <a href="{{ route('login') }}" wire:navigate
                        @class([
                            'rounded-full px-4 py-2 text-sm font-semibold transition',
                            'text-sky-700 dark:text-sky-300' => request()->routeIs('login'),
                            'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white' => ! request()->routeIs('login'),
                        ])>
                        {{ __('Giriş yap') }}
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" wire:navigate class="rounded-full bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                            {{ __('Kayıt ol') }}
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Hamburger Menü -->
            <div class="-me-2 flex items-center sm:hidden">
                <button type="button" @click="open = ! open" aria-label="{{ __('Menüyü aç/kapat') }}" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($links as $link)
                <x-responsive-nav-link :href="route($link['route'])" :active="request()->routeIs($link['active'])" wire:navigate>
                    {{ $link['label'] }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        <!-- Responsive Account Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ auth()->user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                </div>
            @else
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ __('Üyelik') }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ __('Servis paneli için giriş yapın veya kayıt olun') }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')" :active="request()->routeIs('login')" wire:navigate>
                        {{ __('Giriş yap') }}
                    </x-responsive-nav-link>
                    @if (Route::has('register'))
                        <x-responsive-nav-link :href="route('register')" :active="request()->routeIs('register')" wire:navigate>
                            {{ __('Kayıt ol') }}
                        </x-responsive-nav-link>
                    @endif
                </div>
            @endauth
        </div>
    </div>
</nav>
