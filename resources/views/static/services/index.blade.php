<x-guest-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('KoyuMavi servis hizmetleri') }}</h2>
    </x-slot>

    @php
        $services = [
            ['title' => 'Arıza teşhis ve kabul', 'text' => 'Cihazın fiziksel durumu, kullanıcı şikayeti, öncelik seviyesi ve ilk teknik bulgular kayıt altına alınır.', 'route' => route('services.diagnostics')],
            ['title' => 'Ekran ve kasa işlemleri', 'text' => 'Ekran, cam, kasa, çerçeve ve kozmetik parçalar cihaz modeline uygun servis adımlarıyla değerlendirilir.', 'route' => route('services.display')],
            ['title' => 'Güç ve şarj sorunları', 'text' => 'Batarya, şarj soketi, açılmama, hızlı tüketim ve enerji hattı sorunları kontrollü şekilde analiz edilir.', 'route' => route('services.power')],
            ['title' => 'Anakart ve mikro lehim', 'text' => 'Sıvı teması, kısa devre, kopuk hat, entegre ve komponent düzeyi onarım süreçleri teknik ölçümlerle yürütülür.', 'route' => route('services.board')],
            ['title' => 'Yazılım ve veri desteği', 'text' => 'Güncelleme, kurulum, veri aktarımı ve kullanıcı verisi odaklı destek talepleri planlı şekilde ele alınır.', 'route' => route('services.software')],
            ['title' => 'Kurumsal servis takibi', 'text' => 'Müşteri, cihaz, model, ticket ve görev kayıtları servis panelinde ilişkilendirilerek takip edilir.', 'route' => route('services.tracking')],
        ];
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <section class="rounded-[2rem] bg-gradient-to-br from-sky-50 to-white p-8 shadow-sm ring-1 ring-sky-100 dark:from-slate-900 dark:to-sky-950 dark:ring-sky-500/20">
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-sky-600 dark:text-sky-300">Servis kapsamı</p>
            <h1 class="mt-3 max-w-3xl text-4xl font-black text-gray-950 dark:text-white">Mobil iletişim cihazları için uçtan uca teknik servis süreçleri.</h1>
            <p class="mt-4 max-w-3xl text-gray-600 dark:text-gray-300">Bu sayfa, KoyuMavi Teknoloji'nin sunduğu işlemleri ayrı başlıklar altında düzenler. Her başlık kendi statik detay sayfasına yönlenir.</p>
        </section>

        <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($services as $service)
                <a href="{{ $service['route'] }}" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-sky-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800 dark:hover:border-sky-500/30">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $service['title'] }}</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $service['text'] }}</p>
                    <span class="mt-5 inline-flex text-sm font-bold text-sky-700 dark:text-sky-300">Detayları gör →</span>
                </a>
            @endforeach
        </section>
    </main>
</x-guest-layout>
