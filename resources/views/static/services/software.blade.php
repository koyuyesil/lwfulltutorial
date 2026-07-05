<x-guest-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Yazılım ve veri desteği') }}</h2>
    </x-slot>

    <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <article class="rounded-[2rem] border border-gray-200 bg-white p-8 shadow-xl dark:border-gray-700 dark:bg-gray-800">
            <a href="{{ route('services.index') }}" class="text-sm font-bold text-sky-700 dark:text-sky-300">← Tüm servisler</a>
            <p class="mt-8 text-sm font-semibold uppercase tracking-[0.25em] text-sky-600 dark:text-sky-300">KoyuMavi Teknoloji</p>
            <h1 class="mt-3 text-4xl font-black text-gray-950 dark:text-white">Yazılım ve veri desteği</h1>
            <p class="mt-5 text-lg leading-8 text-gray-600 dark:text-gray-300">Yazılım güncelleme, kurulum, veri aktarımı ve kullanıcı verisini korumaya odaklı destek süreçleri planlanır.</p>
            <ul class="mt-8 grid gap-3 sm:grid-cols-3">
                    <li class="rounded-2xl bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-800 dark:bg-sky-500/10 dark:text-sky-100">Yazılım kontrolü ve kurulum</li>
                    <li class="rounded-2xl bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-800 dark:bg-sky-500/10 dark:text-sky-100">Veri aktarımı desteği</li>
                    <li class="rounded-2xl bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-800 dark:bg-sky-500/10 dark:text-sky-100">Kullanıcı ayarları ve teslim kontrolü</li>
            </ul>
            <div class="mt-8 rounded-3xl bg-gradient-to-br from-sky-50 to-white p-6 text-sm leading-7 text-gray-600 ring-1 ring-sky-100 dark:from-slate-900 dark:to-sky-950 dark:text-gray-300 dark:ring-sky-500/20">
                Bu sayfa bilgilendirme amaçlı statik servis sayfasıdır. İş emri, müşteri, cihaz ve ticket kayıtları yetkili kullanıcı girişinden sonra servis panelinde yönetilir.
            </div>
        </article>
    </main>
</x-guest-layout>
