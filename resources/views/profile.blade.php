@php
    $user = auth()->user();
    $company = \App\Support\Company::for($user);
    $completeness = \App\Support\Company::completeness($company);
    $sections = [
        'hesap' => ['Hesap', 'Ad ve e-posta', 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
        'isletme' => ['İşletme', 'Logo, iletişim, vergi', 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21'],
        'guvenlik' => ['Güvenlik', 'Şifre', 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
        'hesabi-sil' => ['Hesabı sil', 'Kalıcı işlem', 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2><span class="font-medium text-slate-400">Sistem /</span> Profil</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        {{-- Kimlik kartı --}}
        <section class="relative isolate overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl ring-1 ring-white/10 sm:p-8">
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-20 [background-image:linear-gradient(#334155_1px,transparent_1px),linear-gradient(90deg,#334155_1px,transparent_1px)] [background-size:28px_28px]"></div>
            <div class="pointer-events-none absolute -right-20 -top-24 -z-10 h-72 w-72 rounded-full bg-indigo-600/40 blur-3xl"></div>
            <div class="flex flex-wrap items-center gap-5">
                <span class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-2xl bg-linear-to-br from-indigo-500 to-violet-600 text-3xl font-black shadow-lg ring-4 ring-white/10">
                    @if ($company['logo'])
                        <img src="{{ $company['logo'] }}" alt="" class="h-full w-full bg-white object-contain p-2">
                    @else
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold uppercase tracking-[.25em] text-indigo-300">{{ $company['name'] }}</p>
                    <h1 class="mt-1 truncate text-2xl font-black sm:text-3xl" x-data="{ name: @js($user->name) }" x-text="name" x-on:profile-updated.window="name = $event.detail.name">{{ $user->name }}</h1>
                    <p class="text-sm text-slate-300">{{ $user->email }} · {{ $user->created_at?->locale('tr')->translatedFormat('F Y') }}'den beri üye</p>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    @foreach ([['Servis kaydı', $user->tickets()->count()], ['Müşteri', $user->clients()->count()], ['İşletme profili', '%'.$completeness]] as [$l, $v])
                        <div class="rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                            <p class="text-xl font-black">{{ $v }}</p>
                            <p class="text-[11px] text-slate-400">{{ $l }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
            {{-- Bölümler --}}
            <nav class="lg:sticky lg:top-24 lg:self-start" aria-label="Profil bölümleri"
                x-data="{ active: 'hesap' }"
                x-init="const io = new IntersectionObserver((es) => es.forEach((e) => e.isIntersecting && (active = e.target.id)), { rootMargin: '-30% 0px -60% 0px' }); ['hesap','isletme','guvenlik','hesabi-sil'].forEach((id) => document.getElementById(id) && io.observe(document.getElementById(id)))">
                <x-scroll-row track="gap-1 lg:flex-col lg:overflow-visible">
                    @foreach ($sections as $id => [$label, $hint, $icon])
                        <a href="#{{ $id }}" :aria-current="active === '{{ $id }}' ? 'true' : null"
                            :class="active === '{{ $id }}' ? 'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:text-white dark:ring-white/10' : 'text-slate-600 hover:bg-white/60 dark:text-slate-300 dark:hover:bg-white/5'"
                            class="flex shrink-0 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition">
                            <svg class="h-5 w-5 shrink-0 {{ $id === 'hesabi-sil' ? 'text-rose-500' : 'text-indigo-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                            <span class="min-w-0"><span class="block">{{ $label }}</span><span class="hidden truncate text-xs font-normal text-slate-500 lg:block">{{ $hint }}</span></span>
                        </a>
                    @endforeach
                </x-scroll-row>
            </nav>

            <div class="min-w-0 space-y-6">
                <livewire:profile.update-profile-information-form />
                <livewire:profile.business-info-form />
                <livewire:profile.update-password-form />
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-app-layout>
