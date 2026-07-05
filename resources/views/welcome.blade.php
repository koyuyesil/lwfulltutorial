<x-guest-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-400">KoyuMavi Teknoloji</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('Mobil iletişim cihazları teknik servis merkezi') }}
                </h2>
            </div>
            <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center justify-center rounded-full bg-blue-700 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800">
                Servis paneli girişi
            </a>
        </div>
    </x-slot>

    <div class="relative overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.45),_transparent_36%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.32),_transparent_32%)]"></div>
        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-gray-100 to-transparent dark:from-gray-900"></div>

        <section class="relative mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-24">
            <div class="flex flex-col justify-center">
                <span class="mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-blue-300/30 bg-white/10 px-4 py-2 text-sm font-medium text-blue-100 backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Hızlı kayıt, doğru teşhis, şeffaf takip
                </span>
                <h1 class="max-w-3xl text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">
                    Akıllı telefon, tablet ve mobil cihaz onarımlarında modern teknik servis deneyimi.
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                    KoyuMavi Teknoloji; ekran, batarya, anakart, soket ve yazılım arızalarında servis akışını düzenleyen, müşteriyi ve cihaz geçmişini tek ekranda takip etmeye odaklanan profesyonel bir teknik servis platformudur.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-base font-bold text-white shadow-xl shadow-blue-600/30 transition hover:-translate-y-0.5 hover:bg-blue-500">
                        Teknik servis paneline gir
                    </a>
                    <a href="#rehberler" class="inline-flex items-center justify-center rounded-2xl border border-white/20 bg-white/10 px-6 py-3 text-base font-bold text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/15">
                        Rehberleri incele
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-[2rem] bg-blue-500/20 blur-3xl"></div>
                <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/10 p-6 shadow-2xl backdrop-blur-xl">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-100">Servis odağı</p>
                            <h3 class="text-2xl font-bold">Mobil iletişim cihazları</h3>
                        </div>
                        <div class="rounded-2xl bg-emerald-400/15 px-3 py-2 text-sm font-semibold text-emerald-200">Aktif</div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ([['Ekran değişimi', 'OLED/LCD test ve montaj'], ['Batarya değişimi', 'Sağlık testi ve kalibrasyon'], ['Anakart onarımı', 'Mikro lehim ve ölçüm'], ['Veri odaklı takip', 'Müşteri, cihaz ve ticket kaydı']] as [$title, $text])
                            <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-5">
                                <div class="mb-4 h-10 w-10 rounded-2xl bg-blue-500/20 ring-1 ring-blue-300/30"></div>
                                <h4 class="font-bold">{{ $title }}</h4>
                                <p class="mt-2 text-sm text-slate-300">{{ $text }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ([['Uzman teşhis', 'Arıza kayıtları ve ürün bilgileri servis sürecini hızlandırır.'], ['Şeffaf süreç', 'Müşteri cihazları ve servis talepleri düzenli olarak izlenir.'], ['Teknik kaynak', 'Ekran ve batarya değişimi rehberleriyle ekip standardı korunur.']] as [$title, $text])
                <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section id="rehberler" class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="rounded-[2rem] border border-gray-200 bg-white p-5 shadow-xl dark:border-gray-700 dark:bg-gray-800 sm:p-8">
            <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-400">Teknik Rehberler</p>
                    <h2 class="mt-2 text-3xl font-black text-gray-900 dark:text-white">Servis ekibi için hızlı başvuru</h2>
                    <p class="mt-2 max-w-2xl text-sm text-gray-600 dark:text-gray-300">
                        Ekran ve batarya değişimi hakkında karşılaştırmalı rehber bilgileri mevcut slayt işleyişi korunarak modern bir alana taşındı.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button id="prevBtn" class="rounded-full border border-gray-200 px-5 py-2 text-sm font-bold text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">⬅️ Önceki</button>
                    <button id="nextBtn" class="rounded-full bg-blue-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Sonraki ➡️</button>
                </div>
            </header>

            <div id="slider" class="relative rounded-3xl bg-gray-50 p-4 dark:bg-gray-900/60 sm:p-8">
                <div class="slide">
                    @include('partials.ekran-degisimi-rehberi')
                </div>
                <div class="slide hidden">
                    @include('partials.batarya-degisimi-rehberi')
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slides = document.querySelectorAll('#slider .slide');
            let currentIndex = 0;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('hidden', i !== index);
                });
            }

            document.getElementById('prevBtn').addEventListener('click', function () {
                currentIndex = (currentIndex === 0) ? slides.length - 1 : currentIndex - 1;
                showSlide(currentIndex);
            });

            document.getElementById('nextBtn').addEventListener('click', function () {
                currentIndex = (currentIndex === slides.length - 1) ? 0 : currentIndex + 1;
                showSlide(currentIndex);
            });

            showSlide(currentIndex);
        });
    </script>
</x-guest-layout>
