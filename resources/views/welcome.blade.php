@php
    $sectors = [
        ['icon' => '📱', 'title' => 'Telefon & tablet', 'text' => 'Ekran, batarya, anakart ve yazılım onarımları'],
        ['icon' => '💻', 'title' => 'Bilgisayar & notebook', 'text' => 'Donanım, veri kurtarma, kurulum ve bakım'],
        ['icon' => '🧺', 'title' => 'Beyaz eşya', 'text' => 'Yerinde servis, yedek parça ve garanti işleri'],
        ['icon' => '📺', 'title' => 'TV & elektronik', 'text' => 'Panel, güç kartı ve ses sistemleri'],
        ['icon' => '🔥', 'title' => 'Kombi & klima', 'text' => 'Periyodik bakım, montaj ve arıza müdahalesi'],
        ['icon' => '🏭', 'title' => 'Endüstriyel cihaz', 'text' => 'Kart tamiri, kalibrasyon ve saha servisi'],
    ];

    $features = [
        ['title' => 'Adım adım servis iş akışı', 'text' => 'Kabul, teşhis, onay, parça, onarım, test ve teslim. Süreç atlanamaz, her adım kimin tarafından yapıldığıyla kayıt altında.', 'tag' => null, 'icon' => 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z'],
        ['title' => 'SLA ve gecikme uyarıları', 'text' => 'Önceliğe göre otomatik teslim hedefi. Riskli ve geciken işler panelde kırmızıyla öne çıkar.', 'tag' => null, 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['title' => 'Müşteriye arıza takibi', 'text' => 'Her kayda tahmin edilemeyen takip kodu. Müşteriniz durumu sizi aramadan, 7/24 kendisi görür.', 'tag' => null, 'icon' => 'm21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z'],
        ['title' => 'Zaman çizelgesi ve notlar', 'text' => 'Dahili ve müşteriye açık notlar, durum geçişleri ve her aşamada geçen süre tek ekranda.', 'tag' => null, 'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
        ['title' => 'Operasyon panosu', 'text' => 'Servis hattı dağılımı, açılan/kapanan iş grafikleri, SLA uyum oranı ve teknisyen performansı.', 'tag' => null, 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
        ['title' => 'Kabul fişi, PDF ve yazdırma', 'text' => 'Tek tıkla servis fişi; müşteri, cihaz, IMEI/seri ve arıza bilgisiyle.', 'tag' => null, 'icon' => 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z'],
        ['title' => 'Teklif ve online onay', 'text' => 'Parça ve işçilik kalemli teklif; müşteri takip sayfasından tek tıkla onaylar.', 'tag' => 'Yakında', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['title' => 'Premium teknik araçlar', 'text' => 'Board ID kütüphanesi ve elektronik hesaplayıcılar ile atölyenizi hızlandırın.', 'tag' => 'Premium', 'icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z'],
    ];

    $steps = [
        ['n' => '01', 'title' => 'Cihazı kabul edin', 'text' => 'Müşteri ve cihaz bilgisi saniyeler içinde; takip kodu otomatik oluşur.'],
        ['n' => '02', 'title' => 'Teşhis ve onay', 'text' => 'Arızayı not edin, müşteriyi bilgilendirin, onayını kaydedin.'],
        ['n' => '03', 'title' => 'Onarın ve test edin', 'text' => 'Parça bekleme, onarım ve test adımları SLA ile izlenir.'],
        ['n' => '04', 'title' => 'Teslim edin', 'text' => 'Kapanış süresi ve hedefe uyum otomatik raporlanır.'],
    ];

    $plans = [
        ['name' => 'Başlangıç', 'price' => 'Ücretsiz', 'note' => 'Tek kullanıcılı atölyeler için', 'items' => ['Servis kaydı ve iş akışı', 'Müşteri ve cihaz yönetimi', 'Arıza takip sayfası', 'PDF / yazdırma'], 'cta' => 'Hemen başlayın', 'featured' => false],
        ['name' => 'Profesyonel', 'price' => 'Aylık abonelik', 'note' => 'Büyüyen servis ekipleri için', 'items' => ['Başlangıç paketindeki her şey', 'SLA uyarıları ve raporlar', 'Operasyon grafikleri', 'Teknisyen performansı'], 'cta' => 'Ücretsiz deneyin', 'featured' => true],
        ['name' => 'Premium', 'price' => 'Ek hizmet', 'note' => 'İleri seviye onarım atölyeleri', 'items' => ['Profesyonel paketteki her şey', 'Board ID kütüphanesi', 'Elektronik hesaplama araçları', 'Öncelikli destek'], 'cta' => 'Bilgi alın', 'featured' => false],
    ];

    $faqs = [
        ['q' => 'Hangi tür servisler için uygun?', 'a' => 'Cihaz kabul eden ve onarım süreci yöneten her teknik servis için: telefon, bilgisayar, beyaz eşya, elektronik, kombi/klima ve endüstriyel cihaz servisleri.'],
        ['q' => 'Müşterim arıza durumunu nasıl görür?', 'a' => 'Kabulde verilen takip kodunu ve telefonunun son 4 hanesini "Arıza Takibi" sayfasına girer; aşamayı, tahmini teslim tarihini ve sizin paylaştığınız notları görür.'],
        ['q' => 'Verilerim diğer firmalardan ayrı mı?', 'a' => 'Evet. Müşteri, servis kaydı ve parça verileriniz yalnızca firmanızın hesabından erişilebilir.'],
        ['q' => 'Kurulum gerekiyor mu?', 'a' => 'Hayır. Tarayıcıdan kayıt olup hemen kullanmaya başlarsınız; bilgisayar, tablet ve telefonda çalışır.'],
    ];
@endphp

<x-guest-layout>
    {{-- HERO --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(60%_50%_at_10%_0%,rgba(56,189,248,0.18),transparent),radial-gradient(50%_50%_at_90%_10%,rgba(99,102,241,0.18),transparent)]"></div>
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,rgba(148,163,184,0.12)_1px,transparent_1px),linear-gradient(to_bottom,rgba(148,163,184,0.12)_1px,transparent_1px)] bg-size-[48px_48px] mask-[radial-gradient(ellipse_at_top,black_40%,transparent_75%)]"></div>

        <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 pb-20 pt-16 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:px-8 lg:pt-24">
            <div>
                <a href="#ozellikler" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5 dark:text-slate-200">
                    <span class="rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Yeni</span>
                    SLA takibi ve müşteri arıza sorgulama yayında →
                </a>
                <h1 class="mt-6 text-4xl font-black leading-[1.05] tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white">
                    Teknik servisinizi
                    <span class="bg-linear-to-r from-sky-500 via-indigo-500 to-violet-500 bg-clip-text text-transparent">tek panelden</span>
                    yönetin.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600 dark:text-slate-300">
                    Cihaz kabulünden teslimata kadar tüm satış sonrası servis sürecini kayıt altına alın, gecikmeleri önceden görün,
                    müşterilerinize "cihazım ne oldu?" diye aratmayın.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" wire:navigate
                        class="inline-flex items-center justify-center rounded-xl bg-linear-to-r from-sky-500 to-indigo-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-indigo-500/25 transition hover:-translate-y-0.5 hover:brightness-110">
                        Ücretsiz hesap oluşturun
                    </a>
                    <a href="{{ route('tracking') }}" wire:navigate
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 text-base font-semibold text-slate-800 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:hover:bg-white/10">
                        <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z"/></svg>
                        Arızamı sorgula
                    </a>
                </div>
                <dl class="mt-10 grid max-w-lg grid-cols-3 gap-6 border-t border-slate-200 pt-6 dark:border-white/10">
                    <div><dt class="text-xs text-slate-500">Servis adımı</dt><dd class="text-2xl font-black text-slate-900 dark:text-white">9</dd></div>
                    <div><dt class="text-xs text-slate-500">Müşteri sorgusu</dt><dd class="text-2xl font-black text-slate-900 dark:text-white">7/24</dd></div>
                    <div><dt class="text-xs text-slate-500">Kurulum</dt><dd class="text-2xl font-black text-slate-900 dark:text-white">0 dk</dd></div>
                </dl>
            </div>

            {{-- Ürün önizlemesi --}}
            <div class="relative" aria-hidden="true">
                <div class="absolute -inset-6 -z-10 rounded-[2.5rem] bg-linear-to-tr from-sky-400/30 to-indigo-500/30 blur-3xl"></div>
                <div class="overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-white/10">
                    <div class="flex items-center gap-1.5 border-b border-slate-100 px-4 py-3 dark:border-white/5">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        <span class="ms-3 text-xs text-slate-400">panel / dashboard</span>
                    </div>
                    <div class="grid grid-cols-[110px_1fr]">
                        <div class="space-y-1 border-e border-slate-100 bg-slate-50 p-3 text-[11px] font-medium text-slate-500 dark:border-white/5 dark:bg-slate-950/50">
                            <div class="rounded-md bg-indigo-600 px-2 py-1.5 text-white">Dashboard</div>
                            <div class="px-2 py-1.5">Servis kayıtları</div>
                            <div class="px-2 py-1.5">Müşteriler</div>
                            <div class="px-2 py-1.5">Cihazlar</div>
                            <div class="px-2 py-1.5">Araçlar</div>
                        </div>
                        <div class="space-y-3 p-4">
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg bg-red-500 p-2.5 text-white"><p class="text-[10px] opacity-80">Geciken</p><p class="text-xl font-black">2</p></div>
                                <div class="rounded-lg bg-amber-400 p-2.5 text-amber-950"><p class="text-[10px] opacity-80">Riskli</p><p class="text-xl font-black">5</p></div>
                                <div class="rounded-lg bg-emerald-500 p-2.5 text-white"><p class="text-[10px] opacity-80">SLA uyumu</p><p class="text-xl font-black">%94</p></div>
                            </div>
                            <div class="rounded-lg ring-1 ring-slate-100 p-3 dark:ring-white/5">
                                <p class="text-[10px] font-semibold text-slate-500">Son 14 gün</p>
                                <svg viewBox="0 0 200 60" class="mt-1 h-16 w-full">
                                    <defs><linearGradient id="g1" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#6366f1" stop-opacity=".35"/><stop offset="1" stop-color="#6366f1" stop-opacity="0"/></linearGradient></defs>
                                    <path d="M0 45 L15 38 L30 42 L45 30 L60 34 L75 22 L90 28 L105 18 L120 24 L135 14 L150 20 L165 10 L180 16 L200 8 L200 60 L0 60Z" fill="url(#g1)"/>
                                    <path d="M0 45 L15 38 L30 42 L45 30 L60 34 L75 22 L90 28 L105 18 L120 24 L135 14 L150 20 L165 10 L180 16 L200 8" fill="none" stroke="#6366f1" stroke-width="2"/>
                                    <path d="M0 50 L15 46 L30 48 L45 40 L60 42 L75 34 L90 38 L105 30 L120 33 L135 26 L150 30 L165 22 L180 26 L200 20" fill="none" stroke="#10b981" stroke-width="2" stroke-dasharray="4 3"/>
                                </svg>
                            </div>
                            <div class="space-y-1.5">
                                @foreach ([['Onarımda', 'w-4/5', 'bg-indigo-500'], ['Parça bekliyor', 'w-2/5', 'bg-orange-500'], ['Teslime hazır', 'w-3/5', 'bg-emerald-500']] as [$l, $w, $c])
                                    <div class="flex items-center gap-2 text-[10px] text-slate-500"><span class="w-20">{{ $l }}</span><span class="h-2 flex-1 rounded-full bg-slate-100 dark:bg-white/5"><span class="block h-full rounded-full {{ $w }} {{ $c }}"></span></span></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -left-6 hidden w-60 rounded-xl bg-white p-4 shadow-xl ring-1 ring-slate-200 sm:block dark:bg-slate-900 dark:ring-white/10">
                    <p class="text-[11px] font-semibold text-slate-500">Müşteri takip ekranı</p>
                    <p class="mt-1 font-mono text-sm font-bold">KM-7XQ4P9TA</p>
                    <div class="mt-2 flex gap-1">
                        @foreach (range(1, 8) as $i)
                            <span @class(['h-1.5 flex-1 rounded-full', 'bg-emerald-500' => $i <= 5, 'bg-slate-200 dark:bg-white/10' => $i > 5])></span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs font-semibold text-indigo-600 dark:text-indigo-300">Onarımda · Tahmini teslim yarın</p>
                </div>
            </div>
        </div>
    </section>

    {{-- SEKTÖRLER --}}
    <section id="sektorler" class="scroll-mt-20 border-y border-slate-200 bg-slate-50 py-16 dark:border-white/10 dark:bg-slate-900/40">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">Her tür teknik servis için</p>
            <h2 class="mt-2 text-center text-3xl font-black tracking-tight text-slate-900 dark:text-white">Cihaz kabul eden her işletmeye uyar</h2>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sectors as $sector)
                    <div class="flex items-start gap-4 rounded-2xl bg-white p-5 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-slate-900 dark:ring-white/10">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-slate-100 text-2xl dark:bg-white/5">{{ $sector['icon'] }}</span>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $sector['title'] }}</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $sector['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ÖZELLİKLER --}}
    <section id="ozellikler" class="scroll-mt-20 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400">Özellikler</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl dark:text-white">Servis sürecinin tamamı, tek yerde</h2>
                <p class="mt-4 text-slate-600 dark:text-slate-400">Excel tabloları, defterler ve telefon trafiği yerine; ölçülebilir, denetlenebilir ve müşteriye şeffaf bir süreç.</p>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($features as $feature)
                    <div class="group relative rounded-2xl bg-white p-6 ring-1 ring-slate-200 transition hover:shadow-xl hover:ring-indigo-200 dark:bg-slate-900 dark:ring-white/10 dark:hover:ring-indigo-500/40">
                        @if ($feature['tag'])
                            <span @class(['absolute right-4 top-4 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase',
                                'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => $feature['tag'] === 'Premium',
                                'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300' => $feature['tag'] !== 'Premium'])>{{ $feature['tag'] }}</span>
                        @endif
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-linear-to-br from-sky-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" /></svg>
                        </span>
                        <h3 class="mt-5 font-bold text-slate-900 dark:text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $feature['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- NASIL ÇALIŞIR --}}
    <section class="bg-slate-950 py-20 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-black tracking-tight">Dört adımda kontrol sizde</h2>
            <ol class="mt-10 grid gap-6 md:grid-cols-4">
                @foreach ($steps as $step)
                    <li class="relative rounded-2xl border border-white/10 bg-white/5 p-6">
                        <span class="font-mono text-sm font-bold text-sky-400">{{ $step['n'] }}</span>
                        <h3 class="mt-3 text-lg font-bold">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ARIZA TAKİBİ --}}
    <section class="py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-10 overflow-hidden rounded-3xl bg-linear-to-br from-emerald-500 to-teal-600 p-8 text-white shadow-2xl shadow-emerald-500/20 sm:p-12 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-100">Cihazınız serviste mi?</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight">Arıza durumunuzu hemen sorgulayın</h2>
                    <p class="mt-3 text-emerald-50">Servis fişinizdeki takip kodunu girin; cihazınızın hangi aşamada olduğunu ve tahmini teslim tarihini görün.</p>
                </div>
                <form action="{{ route('tracking') }}" method="GET" class="flex flex-col gap-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/20 backdrop-blur sm:flex-row">
                    <label for="home-track-code" class="sr-only">Takip kodu</label>
                    <input id="home-track-code" name="kod" required placeholder="Takip kodu (örn. KM-7XQ4P9TA)"
                        class="flex-1 rounded-xl border-0 bg-white px-4 py-3 font-mono text-slate-900 placeholder:font-sans placeholder:text-slate-400 focus:ring-2 focus:ring-emerald-300">
                    <button class="rounded-xl bg-slate-950 px-6 py-3 font-semibold text-white transition hover:bg-slate-800">Sorgula</button>
                </form>
            </div>
        </div>
    </section>

    {{-- FİYATLAR --}}
    <section id="fiyatlar" class="scroll-mt-20 border-t border-slate-200 bg-slate-50 py-20 dark:border-white/10 dark:bg-slate-900/40">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400">Paketler</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl dark:text-white">İşletmenizin ölçeğine göre</h2>
            </div>
            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    <div @class([
                        'relative flex flex-col rounded-3xl p-8',
                        'bg-slate-950 text-white shadow-2xl ring-2 ring-indigo-500 lg:-translate-y-3' => $plan['featured'],
                        'bg-white ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-white/10' => ! $plan['featured'],
                    ])>
                        @if ($plan['featured'])
                            <span class="absolute -top-3 left-8 rounded-full bg-indigo-500 px-3 py-1 text-xs font-bold">En çok tercih edilen</span>
                        @endif
                        <p class="font-bold">{{ $plan['name'] }}</p>
                        <p class="mt-3 text-3xl font-black">{{ $plan['price'] }}</p>
                        <p @class(['mt-1 text-sm', 'text-slate-400' => $plan['featured'], 'text-slate-500' => ! $plan['featured']])>{{ $plan['note'] }}</p>
                        <ul class="mt-6 flex-1 space-y-3 text-sm">
                            @foreach ($plan['items'] as $item)
                                <li class="flex gap-2"><span class="text-emerald-500">✓</span> {{ $item }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" wire:navigate @class([
                            'mt-8 rounded-xl px-4 py-3 text-center text-sm font-semibold transition',
                            'bg-indigo-500 text-white hover:bg-indigo-400' => $plan['featured'],
                            'bg-slate-900 text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900' => ! $plan['featured'],
                        ])>{{ $plan['cta'] }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- SSS --}}
    <section class="py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-3xl font-black tracking-tight text-slate-900 dark:text-white">Sık sorulan sorular</h2>
            <div class="mt-10 divide-y divide-slate-200 rounded-2xl ring-1 ring-slate-200 dark:divide-white/10 dark:ring-white/10" x-data="{ open: 0 }">
                @foreach ($faqs as $i => $faq)
                    <div>
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left font-semibold text-slate-900 dark:text-white">
                            {{ $faq['q'] }}
                            <svg class="h-5 w-5 shrink-0 transition" :class="open === {{ $i }} && 'rotate-45'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak class="px-6 pb-5 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $faq['a'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- SON ÇAĞRI --}}
    <section class="px-4 pb-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl rounded-3xl bg-linear-to-r from-sky-500 to-indigo-600 px-8 py-14 text-center text-white shadow-2xl shadow-indigo-500/25">
            <h2 class="text-3xl font-black tracking-tight sm:text-4xl">Servisinizi bugün dijitalleştirin</h2>
            <p class="mx-auto mt-3 max-w-xl text-sky-50">Kurulum yok, kredi kartı yok. Kayıt olun, ilk servis kaydınızı dakikalar içinde açın.</p>
            <a href="{{ route('register') }}" wire:navigate class="mt-8 inline-flex rounded-xl bg-white px-6 py-3.5 font-semibold text-indigo-700 shadow-lg transition hover:-translate-y-0.5">Ücretsiz hesap oluşturun</a>
        </div>
    </section>
</x-guest-layout>
