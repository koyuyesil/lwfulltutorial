<footer class="border-t border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-slate-950">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 text-sm sm:px-6 md:grid-cols-4 lg:px-8">
        <div class="md:col-span-2">
            <p class="text-base font-extrabold text-slate-900 dark:text-white">{{ config('app.name', 'KoyuMavi') }} <span class="text-sky-600 dark:text-sky-400">Servis</span></p>
            <p class="mt-2 max-w-md text-slate-600 dark:text-slate-400">
                Teknik servis firmaları için satış sonrası servis yönetimi: kabulden teslimata kadar tüm süreç, tek panelde.
            </p>
        </div>
        <div>
            <p class="font-semibold text-slate-900 dark:text-white">Ürün</p>
            <ul class="mt-2 space-y-1.5 text-slate-600 dark:text-slate-400">
                <li><a href="{{ route('home') }}#ozellikler" class="hover:text-sky-600">Özellikler</a></li>
                <li><a href="{{ route('home') }}#fiyatlar" class="hover:text-sky-600">Fiyatlar</a></li>
                <li><a href="{{ route('services.index') }}" wire:navigate class="hover:text-sky-600">Çözümler</a></li>
            </ul>
        </div>
        <div>
            <p class="font-semibold text-slate-900 dark:text-white">Müşteriler için</p>
            <ul class="mt-2 space-y-1.5 text-slate-600 dark:text-slate-400">
                <li><a href="{{ route('tracking') }}" wire:navigate class="hover:text-sky-600">Arıza takibi</a></li>
                @guest
                    <li><a href="{{ route('login') }}" wire:navigate class="hover:text-sky-600">Firma girişi</a></li>
                @endguest
            </ul>
        </div>
    </div>
    <div class="border-t border-slate-200 dark:border-white/10">
        <p class="mx-auto max-w-7xl px-4 py-4 text-xs text-slate-500 sm:px-6 lg:px-8 dark:text-slate-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'KoyuMavi') }}. Tüm hakları saklıdır.
        </p>
    </div>
</footer>
