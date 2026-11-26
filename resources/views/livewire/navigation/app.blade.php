<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    /** Görev ya da servis kaydı değişince menü sayaçları yenilenir. */
    #[\Livewire\Attributes\On('tasks-changed')]
    #[\Livewire\Attributes\On('task-saved')]
    #[\Livewire\Attributes\On('ticket-created')]
    public function refreshCounts(): void
    {
    }

    /**
     * Tek kaynaktan beslenen menü. 'match' aktif durumu belirleyen route desenidir.
     */
    public function groups(): array
    {
        $overdue = Auth::user()->tickets()->overdue()->count();
        $open = Auth::user()->tickets()->open()->count();
        $taskAttention = Auth::user()->tasks()->needsAttention()->count();
        $taskOverdue = Auth::user()->tasks()->overdue()->count();

        return [
            'Genel' => [
                ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M2.25 12l8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
            ],
            'Servis' => [
                ['route' => 'tickets.index', 'match' => 'tickets.*', 'label' => 'Servis kayıtları', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z',
                    'badge' => $overdue ?: $open, 'badgeClass' => $overdue ? 'bg-red-500 text-white' : 'bg-white/10 text-slate-300', 'badgeTitle' => $overdue ? "$overdue geciken iş" : "$open açık iş"],
                ['route' => 'clients.index', 'match' => 'clients.*', 'label' => 'Müşteriler', 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                ['route' => 'parts.index', 'match' => 'parts.*', 'label' => 'Parça ve stok', 'icon' => 'M21 7.5l-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
                ['route' => 'products.index', 'match' => 'products.*', 'label' => 'Cihaz modelleri', 'icon' => 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3'],
                ['route' => 'tasks.index', 'match' => 'tasks.*', 'label' => 'Görevler', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                    'badge' => $taskAttention, 'badgeClass' => $taskOverdue ? 'bg-red-500 text-white' : 'bg-amber-400 text-amber-950',
                    'badgeTitle' => $taskOverdue ? "$taskOverdue gecikmiş, toplam $taskAttention görev dikkat bekliyor" : "$taskAttention görev bugün / hatırlatma"],
            ],
            'Araçlar' => [
                ['route' => 'tools.index', 'match' => 'tools.index', 'label' => 'Hesaplayıcılar', 'icon' => 'M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z'],
            ],
            'Premium' => [
                ['route' => 'board-ids.index', 'match' => 'board-ids.*', 'label' => 'Board ID kütüphanesi', 'premium' => true, 'icon' => 'M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z'],
                ['route' => 'cross-reference.index', 'match' => 'cross-reference.*', 'label' => 'Çapraz referans', 'premium' => true, 'icon' => 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
            ],
            'Sistem' => [
                ['route' => 'settings', 'match' => 'settings', 'label' => 'Ayarlar', 'icon' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
            ],
        ];
    }
}; ?>

<div>
    {{-- Mobil arka plan --}}
    <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden"></div>

    <aside :class="sidebarOpen && 'translate-x-0!'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-slate-950 text-slate-300 transition-[transform,width] duration-200 lg:translate-x-0 lg:group-data-[collapsed=true]/sb:w-[4.5rem]">
        {{-- Logo --}}
        <div class="flex h-16 shrink-0 items-center justify-between gap-2 px-5 lg:group-data-[collapsed=true]/sb:justify-center lg:group-data-[collapsed=true]/sb:px-0">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5">
                <x-application-logo class="h-9 w-9 shrink-0 rounded-xl shadow-lg shadow-sky-900/30" />
                <x-brand-name dark class="text-base lg:group-data-[collapsed=true]/sb:hidden" />
            </a>
            <button type="button" @click="sidebarOpen = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 lg:hidden" aria-label="Menüyü kapat">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- Menü --}}
        <nav class="flex-1 space-y-6 overflow-y-auto overflow-x-hidden px-3 py-4 lg:group-data-[collapsed=true]/sb:space-y-3 lg:group-data-[collapsed=true]/sb:px-2" aria-label="Ana menü">
            @foreach ($this->groups() as $group => $items)
                <div>
                    <p @class(['flex items-center gap-1.5 px-3 text-[11px] font-bold uppercase tracking-[0.15em] lg:group-data-[collapsed=true]/sb:hidden', 'text-amber-300/90' => $group === 'Premium', 'text-slate-500' => $group !== 'Premium'])>
                        @if ($group === 'Premium')<svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.85L10 14.9l-5.25 2.75 1-5.85L1.5 7.7l5.9-.9L10 1.5Z"/></svg>@endif
                        {{ $group }}
                    </p>
                    <div @class(['mx-2 hidden h-px lg:group-data-[collapsed=true]/sb:block', 'bg-amber-400/40' => $group === 'Premium', 'bg-white/10' => $group !== 'Premium']) aria-hidden="true"></div>
                    <ul class="mt-2 space-y-0.5">
                        @foreach ($items as $item)
                            @php $active = request()->routeIs($item['match']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}" wire:navigate @click="sidebarOpen = false" title="{{ $item['label'] }}{{ ! empty($item['badge']) ? ' · '.$item['badgeTitle'] : '' }}"
                                    @if ($active) aria-current="page" @endif
                                    @class([
                                        'group relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition lg:group-data-[collapsed=true]/sb:justify-center lg:group-data-[collapsed=true]/sb:px-0 lg:group-data-[collapsed=true]/sb:py-2.5',
                                        'bg-white/10 text-white shadow-inner' => $active,
                                        'hover:bg-white/5 hover:text-white' => ! $active,
                                    ])>
                                    <svg @class(['h-5 w-5 shrink-0', 'text-sky-400' => $active, 'text-slate-500 group-hover:text-slate-300' => ! $active])
                                        fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                                    <span class="flex-1 truncate lg:group-data-[collapsed=true]/sb:hidden">{{ $item['label'] }}</span>
                                    @if (! empty($item['premium']))
                                        <span class="rounded bg-amber-400/15 px-1.5 py-0.5 text-[10px] font-bold uppercase text-amber-300 lg:group-data-[collapsed=true]/sb:hidden">Pro</span>
                                    @endif
                                    @if (! empty($item['badge']))
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $item['badgeClass'] }} lg:group-data-[collapsed=true]/sb:hidden" title="{{ $item['badgeTitle'] }}">{{ $item['badge'] }}</span>
                                        {{-- Dar menüde rozet yerine nokta --}}
                                        <span class="absolute right-2 top-1.5 hidden h-2 w-2 rounded-full ring-2 ring-slate-950 {{ str_contains($item['badgeClass'], 'red') ? 'bg-red-500' : (str_contains($item['badgeClass'], 'amber') ? 'bg-amber-400' : 'bg-sky-400') }} lg:group-data-[collapsed=true]/sb:block"></span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        {{-- Alt bölüm: müşteri sayfası + kullanıcı --}}
        <div class="space-y-3 border-t border-white/10 p-3 lg:group-data-[collapsed=true]/sb:px-2">
            <a href="{{ route('tracking') }}" target="_blank" title="Müşteri arıza takip sayfası"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-emerald-300 transition hover:bg-emerald-500/10 lg:group-data-[collapsed=true]/sb:justify-center lg:group-data-[collapsed=true]/sb:px-0">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                <span class="lg:group-data-[collapsed=true]/sb:hidden">Müşteri arıza takip sayfası</span>
            </a>

            <div x-data="{ menu: false }" class="relative">
                <button type="button" @click="menu = ! menu" @click.outside="menu = false"
                    title="{{ auth()->user()->name }}" class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left transition hover:bg-white/5 lg:group-data-[collapsed=true]/sb:justify-center lg:group-data-[collapsed=true]/sb:px-0">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-linear-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="min-w-0 flex-1 lg:group-data-[collapsed=true]/sb:hidden">
                        <span class="block truncate text-sm font-semibold text-white" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                        <span class="block truncate text-xs text-slate-500">{{ auth()->user()->email }}</span>
                    </span>
                    <svg class="h-4 w-4 text-slate-500 lg:group-data-[collapsed=true]/sb:hidden" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 15 3.75 3.75L15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                </button>
                <div x-show="menu" x-cloak x-transition
                    class="absolute bottom-full left-0 right-0 mb-2 overflow-hidden rounded-xl bg-slate-900 py-1 text-sm shadow-2xl ring-1 ring-white/10 lg:group-data-[collapsed=true]/sb:right-auto lg:group-data-[collapsed=true]/sb:w-52">
                    <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 hover:bg-white/5 hover:text-white">Profil ve ayarlar</a>
                    <a href="{{ route('home') }}" wire:navigate class="block px-4 py-2 hover:bg-white/5 hover:text-white">Tanıtım sayfası</a>
                    <button type="button" wire:click="logout" class="block w-full px-4 py-2 text-left text-rose-300 hover:bg-rose-500/10">Çıkış yap</button>
                </div>
            </div>
        </div>
    </aside>
</div>
