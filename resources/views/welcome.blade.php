<x-guest-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-sky-600 dark:text-sky-300">KoyuMavi Teknoloji</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('Mobil iletişim cihazları teknik servis merkezi') }}
                </h2>
            </div>
            <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center justify-center rounded-full bg-sky-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-600/20 transition hover:-translate-y-0.5 hover:bg-sky-700">
                Servis paneli girişi
            </a>
        </div>
    </x-slot>

    @php
        $services = [
            ['title' => 'Arıza teşhis ve kabul', 'text' => 'Cihaz kabul, ön kontrol, arıza notu ve servis önceliği kayıt altına alınır.', 'route' => route('services.diagnostics')],
            ['title' => 'Ekran ve kasa işlemleri', 'text' => 'Ekran, kasa, cam, çerçeve ve kozmetik parça işlemleri kontrollü süreçle yürütülür.', 'route' => route('services.display')],
            ['title' => 'Güç ve şarj sorunları', 'text' => 'Batarya, şarj soketi, enerji tüketimi ve açılmama sorunları analiz edilir.', 'route' => route('services.power')],
            ['title' => 'Anakart ve mikro lehim', 'text' => 'Sıvı teması, kısa devre, entegre ve hat onarımları teknik ölçümle ele alınır.', 'route' => route('services.board')],
            ['title' => 'Yazılım ve veri desteği', 'text' => 'Güncelleme, kurulum, veri aktarımı ve kullanıcı verisi odaklı destek sağlanır.', 'route' => route('services.software')],
            ['title' => 'Kurumsal takip', 'text' => 'Müşteri, cihaz, ürün modeli, ticket ve görev kayıtları servis panelinden izlenir.', 'route' => route('services.tracking')],
        ];
    @endphp

    <section class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-sky-50 to-white text-gray-950 dark:from-slate-950 dark:via-slate-900 dark:to-sky-950 dark:text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.20),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(125,211,252,0.16),_transparent_30%)]"></div>
        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-24">
            <div class="flex flex-col justify-center">
                <span class="mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-sky-200 bg-white/70 px-4 py-2 text-sm font-medium text-sky-800 shadow-sm backdrop-blur dark:border-sky-400/20 dark:bg-white/10 dark:text-sky-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Teknik servis, kayıt ve takip süreçleri tek çatı altında
                </span>
                <h1 class="max-w-3xl text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">
                    Mobil iletişim cihazları için güvenilir servis ve operasyon yönetimi.
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-gray-600 dark:text-slate-300">
                    KoyuMavi Teknoloji; arıza kabulünden teslimata kadar telefon, tablet ve mobil cihaz servis süreçlerini düzenli kayıt, uzman teknik değerlendirme ve şeffaf takip anlayışıyla yönetir.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('services.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-sky-600 px-6 py-3 text-base font-bold text-white shadow-xl shadow-sky-600/20 transition hover:-translate-y-0.5 hover:bg-sky-700">
                        Tüm servisleri incele
                    </a>
                    <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center justify-center rounded-2xl border border-sky-200 bg-white/75 px-6 py-3 text-base font-bold text-sky-800 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white dark:border-white/15 dark:bg-white/10 dark:text-white dark:hover:bg-white/15">
                        Servis paneline gir
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-[2rem] bg-sky-300/20 blur-3xl dark:bg-sky-500/20"></div>
                <div class="relative overflow-hidden rounded-[2rem] border border-sky-100 bg-white/85 p-6 shadow-2xl backdrop-blur-xl dark:border-white/10 dark:bg-white/10">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-sky-700 dark:text-sky-100">Servis kapsamı</p>
                            <h3 class="text-2xl font-bold">Uçtan uca teknik servis</h3>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-200">Aktif</div>
                    </div>
                    <div class="grid gap-3">
                        @foreach (array_slice($services, 0, 4) as $service)
                            <a href="{{ $service['route'] }}" class="group rounded-3xl border border-sky-100 bg-slate-50 p-4 transition hover:-translate-y-0.5 hover:border-sky-300 hover:bg-sky-50 dark:border-white/10 dark:bg-slate-900/70 dark:hover:bg-sky-950/60">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h4 class="font-bold text-gray-950 dark:text-white">{{ $service['title'] }}</h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-slate-300">{{ $service['text'] }}</p>
                                    </div>
                                    <span class="text-sky-600 transition group-hover:translate-x-1 dark:text-sky-300">→</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-sky-600 dark:text-sky-300">Hizmetler</p>
                <h2 class="mt-2 text-3xl font-black text-gray-900 dark:text-white">Tüm işlemler için net servis yönlendirmesi</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-300">Anasayfa tek bir işlem türüne odaklanmak yerine servis kapsamını tanıtır; detaylar ayrı statik sayfalarda açıklanır.</p>
            </div>
            <a href="{{ route('services.index') }}" class="inline-flex items-center justify-center rounded-full border border-sky-200 px-5 py-2 text-sm font-bold text-sky-700 transition hover:bg-sky-50 dark:border-sky-400/20 dark:text-sky-200 dark:hover:bg-sky-500/10">Servis sayfasına git</a>
        </div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($services as $service)
                <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-sky-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800 dark:hover:border-sky-500/30">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $service['title'] }}</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $service['text'] }}</p>
                    <a href="{{ $service['route'] }}" class="mt-5 inline-flex text-sm font-bold text-sky-700 hover:text-sky-900 dark:text-sky-300 dark:hover:text-sky-100">Detay sayfası →</a>
                </article>
            @endforeach
        </div>
    </section>
</x-guest-layout>
