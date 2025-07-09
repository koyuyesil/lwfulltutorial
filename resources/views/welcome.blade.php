<x-guest-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Guest') }}
        </h2>
    </x-slot>
<div class="py-12 flex justify-center">
        <div class="max-w-6xl w-full bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-6">

            <!-- Başlık -->
            <header>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Teknik Rehberler') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Ekran ve batarya değişimi hakkında karşılaştırmalı rehber bilgileri.') }}
                </p>
            </header>

            <!-- Butonlar (üstte) -->
            <div class="flex justify-center gap-4">
                <button id="prevBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    ⬅️ Önceki
                </button>
                <button id="nextBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Sonraki ➡️
                </button>
            </div>

            <!-- Slayt Sistemi -->
            <div id="slider" class="p-14 relative">
                <div class="slide">
                    @include('partials.ekran-degisimi-rehberi')
                </div>
                <div class="slide hidden">
                    @include('partials.batarya-degisimi-rehberi')
                </div>
            </div>

        </div>
    </div>

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
