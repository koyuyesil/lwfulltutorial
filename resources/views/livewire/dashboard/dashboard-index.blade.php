<x-slot name="header">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-400">KoyuMavi Teknoloji</p>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('Teknik Servis Dashboard') }}
            </h2>
        </div>
        <div class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
            Mobil cihaz servis merkezi
        </div>
    </div>
</x-slot>

<div class="bg-gray-100 py-8 dark:bg-gray-900 sm:py-10">
    <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
        <section class="relative overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl sm:p-8 lg:p-10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.45),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.28),_transparent_30%)]"></div>
            <div class="relative grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-blue-100 backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Canlı servis operasyon görünümü
                    </span>
                    <h1 class="mt-5 max-w-3xl text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                        Müşteri, cihaz ve onarım kayıtlarını tek merkezden yönetin.
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        Dashboard ana sayfası mevcut Livewire liste işleyişini korurken servis yoğunluğu, aktif talepler ve hızlı işlem alanlarını daha okunabilir bir yapıda sunar.
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                        <p class="text-sm text-blue-100">Aktif servis talepleri</p>
                        <p class="mt-2 text-4xl font-black">{{ $activeTickets }}</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                        <p class="text-sm text-blue-100">Açık görevler</p>
                        <p class="mt-2 text-4xl font-black">{{ $openTasks }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                    <div class="mt-3 flex items-end justify-between gap-4">
                        <p class="text-4xl font-black text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                        <span class="rounded-2xl bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">Kayıt</span>
                    </div>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $stat['hint'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
            <div class="rounded-[2rem] border border-gray-200 bg-white p-5 shadow-xl dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                <header class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Müşteri cihazları</p>
                        <h2 class="mt-2 text-2xl font-black text-gray-900 dark:text-white">Son servis kayıtları</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Mevcut liste bileşeni lazy loading ve sayfalama davranışı korunarak gösterilir.</p>
                    </div>
                    <a href="{{ route('clients.index') }}" wire:navigate class="inline-flex items-center justify-center rounded-full bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">
                        Müşterilere git
                    </a>
                </header>
                <div class="rounded-3xl bg-gray-50 p-2 dark:bg-gray-900/50 sm:p-4">
                    <livewire:dashboard.clients-products-list lazy />
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Hızlı işlemler</p>
                    <div class="mt-5 grid gap-3">
                        <a href="{{ route('tickets.index') }}" wire:navigate class="rounded-2xl border border-gray-200 p-4 font-semibold text-gray-800 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-blue-500/10">Servis taleplerini yönet</a>
                        <a href="{{ route('products.index') }}" wire:navigate class="rounded-2xl border border-gray-200 p-4 font-semibold text-gray-800 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-blue-500/10">Ürün/model kayıtları</a>
                        <a href="{{ route('tasks.index') }}" wire:navigate class="rounded-2xl border border-gray-200 p-4 font-semibold text-gray-800 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-blue-500/10">Görev planı</a>
                    </div>
                </div>

                <div class="rounded-[2rem] bg-gradient-to-br from-blue-700 to-slate-900 p-6 text-white shadow-xl">
                    <p class="text-sm font-semibold text-blue-100">Servis standardı</p>
                    <h3 class="mt-2 text-2xl font-black">Doğru kayıt, hızlı onarım.</h3>
                    <p class="mt-3 text-sm leading-6 text-blue-100">Cihaz geçmişi, müşteri bilgisi ve teknik iş emirlerini düzenli tutarak mobil iletişim cihazlarında daha güvenilir servis deneyimi sağlayın.</p>
                </div>
            </aside>
        </section>
    </div>
</div>
