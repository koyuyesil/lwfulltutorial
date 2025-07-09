{{-- Batarya Değişimi Rehberi --}}

<div class="space-y-10 text-gray-900 dark:text-gray-100">

    <div class="text-center space-y-2">
        <h1 class="text-3xl font-extrabold text-yellow-700 dark:text-yellow-300">🔋 Batarya Değişimi Rehberi</h1>
        <p class="text-lg text-gray-700 dark:text-gray-300">
            Orijinal ve yan sanayi bataryaların farklarını karşılaştırmalı olarak öğrenin
        </p>
    </div>

    <p class="text-base leading-relaxed">
        Batarya performansı cihazın günlük kullanım kalitesini doğrudan etkiler.
        Aşağıdaki rehber, orijinal ve yan sanayi bataryaların avantaj ve dezavantajlarını açıklar.
    </p>

    {{-- Avantajlar / Dezavantajlar --}}
    <div class="space-y-6">

        @foreach ([
            ['title' => '✅ Orijinal Batarya - Avantajları', 'color' => 'green', 'items' => [
                'Uzun ömürlüdür ve stabil performans sağlar.',
                'Cihazla tam uyumlu çalışır.',
                'Şişme riski düşüktür.',
                'Üretici garantisiyle gelir.',
                'Hızlı şarj desteği tam kapasiteyle çalışır.'
            ]],
            ['title' => '❌ Orijinal Batarya - Dezavantajları', 'color' => 'red', 'items' => [
                'Maliyeti yüksektir.',
                'Yetkili servis dışında bulunması zordur.'
            ]],
            ['title' => '✅ Yan Sanayi Batarya - Avantajları', 'color' => 'green', 'items' => [
                'Daha ekonomiktir.',
                'Kolayca temin edilebilir.',
                'Bazı modellerde hızlı şarj desteği bulunabilir.'
            ]],
            ['title' => '❌ Yan Sanayi Batarya - Dezavantajları', 'color' => 'red', 'items' => [
                'Performans dalgalanmaları yaşanabilir.',
                'Çabuk şarj kaybı yaşanabilir.',
                'Şişme veya aşırı ısınma riski daha yüksektir.',
                'Kapasite etiketiyle gerçek değer farklı olabilir.'
            ]]
        ] as $section)
            <div>
                <h3 class="text-2xl font-bold text-{{ $section['color'] }}-700 dark:text-{{ $section['color'] }}-400 mb-4">{{ $section['title'] }}</h3>
                <ul class="list-disc list-inside space-y-2">
                    @foreach ($section['items'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach

    </div>

    {{-- Tavsiye Tablosu --}}
    <div>
        <h3 class="text-2xl font-bold text-yellow-700 dark:text-yellow-400 mb-6">🎯 Kullanım Durumuna Göre Tavsiyeler</h3>
        <div class="overflow-x-auto rounded-lg">
            <table class="w-full text-sm text-left border border-gray-300 dark:border-gray-600">
                <thead class="bg-gray-100 dark:bg-gray-700 font-semibold">
                    <tr>
                        <th class="py-3 px-4 border-b">Kullanım Durumu</th>
                        <th class="py-3 px-4 border-b">Tavsiye Edilen Batarya</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800">
                    @foreach ([
                        ['Yoğun günlük kullanım', 'Orijinal batarya'],
                        ['Uygun fiyatlı geçici çözüm', 'Yan sanayi batarya'],
                        ['Uzun süreli güvenli kullanım', 'Orijinal batarya'],
                        ['Sadece cihazı bir süre daha kullanmak isteyen', 'Kaliteli yan sanayi batarya']
                    ] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 border-b">{{ $row[0] }}</td>
                            <td class="py-3 px-4 border-b">{{ $row[1] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sonuç --}}
    <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 dark:border-yellow-600 p-6 rounded-lg">
        <h4 class="text-lg font-bold text-yellow-800 dark:text-yellow-200 mb-2">📌 Sonuç</h4>
        <p class="leading-relaxed">
            Batarya değişimi cihaz sağlığı için kritik bir adımdır. Orijinal bataryalar uzun ömür ve güvenlik sağlar;
            ancak ekonomik sınıf bataryalar da kısa vadeli çözümler sunabilir. Montaj işleminin uzman kişilerce yapılması önerilir.
        </p>
    </div>

</div>
