@php
    $money = fn ($v) => \App\Models\Ticket::money((float) $v);
    $profit = $total - $cost;
    $quoteBadge = [
        'pending' => ['Müşteri onayı bekleniyor', 'bg-amber-100 text-amber-800 ring-amber-300 dark:bg-amber-500/15 dark:text-amber-200 dark:ring-amber-500/30'],
        'approved' => ['Onaylandı', 'bg-emerald-100 text-emerald-800 ring-emerald-300 dark:bg-emerald-500/15 dark:text-emerald-200 dark:ring-emerald-500/30'],
        'rejected' => ['Reddedildi', 'bg-rose-100 text-rose-800 ring-rose-300 dark:bg-rose-500/15 dark:text-rose-200 dark:ring-rose-500/30'],
    ];
@endphp

<section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
    <header class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-5 py-4 dark:border-white/5">
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">₺</span>
        <div class="min-w-0 flex-1">
            <h3 class="font-bold text-slate-900 dark:text-white">Teklif · parça ve işçilik</h3>
            <p class="text-xs text-slate-500">Müşteri teklifi takip sayfasından tek tıkla onaylar; onayda iş otomatik onarıma geçer.</p>
        </div>
        @if ($ticket->quote_status)
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $quoteBadge[$ticket->quote_status][1] }}">{{ $quoteBadge[$ticket->quote_status][0] }}</span>
        @endif
    </header>

    <div class="space-y-4 p-5">
        {{-- Kalemler --}}
        @if ($ticket->items->isNotEmpty())
            <div class="overflow-hidden rounded-xl ring-1 ring-slate-200 dark:ring-white/10">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-white/5">
                        <tr><th class="px-3 py-2">Kalem</th><th class="w-20 px-2 py-2 text-center">Adet</th><th class="w-28 px-2 py-2 text-right">Birim ₺</th><th class="w-28 px-3 py-2 text-right">Tutar</th><th class="w-8"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($ticket->items as $item)
                            <tr wire:key="item-{{ $item->id }}" class="group">
                                <td class="px-3 py-2">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $item->name }}</p>
                                    <p class="text-[11px] text-slate-500">
                                        {{ $item->kind === 'labor' ? 'İşçilik' : 'Parça' }}
                                        @if ($item->part) · stok {{ $item->part->stock }} @endif
                                        @if ((float) $item->unit_cost > 0) · alış {{ $money($item->unit_cost) }} @endif
                                    </p>
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" min="1" value="{{ $item->qty }}" wire:change="setQty({{ $item->id }}, $event.target.value)"
                                        class="w-full rounded-md border-0 bg-slate-50 px-2 py-1 text-center font-mono text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 dark:bg-white/5 dark:ring-white/10">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" inputmode="decimal" value="{{ rtrim(rtrim(number_format((float) $item->unit_price, 2, '.', ''), '0'), '.') }}" wire:change="setPrice({{ $item->id }}, $event.target.value)"
                                        class="w-full rounded-md border-0 bg-slate-50 px-2 py-1 text-right font-mono text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-indigo-500 dark:bg-white/5 dark:ring-white/10">
                                </td>
                                <td class="px-3 py-2 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ $money($item->total()) }}</td>
                                <td class="pr-2 text-right">
                                    <button type="button" wire:click="remove({{ $item->id }})" class="rounded p-1 text-slate-400 opacity-0 hover:bg-rose-50 hover:text-rose-600 group-hover:opacity-100 focus:opacity-100 dark:hover:bg-rose-500/10" aria-label="Kalemi sil">✕</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-white/5">
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-right text-xs text-slate-500">
                                Kâr <span @class(['font-mono font-semibold', 'text-emerald-600' => $profit >= 0, 'text-rose-600' => $profit < 0])>{{ $money($profit) }}</span>
                                @if ($total > 0) <span class="text-slate-400">(%{{ number_format($profit / $total * 100, 0) }})</span> @endif
                            </td>
                            <td class="px-3 py-2 text-right font-mono text-base font-black text-slate-900 dark:text-white">{{ $money($total) }}</td><td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- Ekle --}}
        <div class="grid gap-3 lg:grid-cols-2">
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <label class="text-xs font-semibold text-slate-500">Stoktan parça ekle</label>
                <input type="search" wire:model.live.debounce.250ms="partSearch" @focus="open = true" @input="open = true" placeholder="Parça adı, kod ya da model…"
                    class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-white/10">
                    @forelse ($this->partResults as $part)
                        <button type="button" wire:click="addPart({{ $part->id }})" @click="open = false" class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                            <span class="min-w-0"><span class="block truncate font-medium text-slate-900 dark:text-white">{{ $part->name }}</span>
                                <span class="block truncate text-[11px] text-slate-500">{{ $part->category }}{{ $part->compatible ? ' · '.$part->compatible : '' }}</span></span>
                            <span class="shrink-0 text-right"><span class="block font-mono text-xs font-semibold">{{ $money($part->price) }}</span>
                                <span @class(['block text-[11px]', 'text-rose-600' => $part->stock <= 0, 'text-slate-500' => $part->stock > 0])>stok {{ $part->stock }}</span></span>
                        </button>
                    @empty
                        <p class="px-3 py-3 text-xs text-slate-500">{{ mb_strlen($partSearch) >= 2 ? 'Eşleşen parça yok.' : 'Uyumlu parça bulunamadı; aramak için yazın.' }}
                            <a href="{{ route('parts.index') }}" wire:navigate class="font-semibold text-indigo-600">Parça kataloğu →</a></p>
                    @endforelse
                </div>
            </div>
            <form wire:submit="addLabor">
                <label class="text-xs font-semibold text-slate-500">İşçilik / hizmet ekle</label>
                <div class="mt-1 flex gap-2">
                    <input wire:model="laborName" placeholder="örn. Ekran değişimi işçiliği" list="labor-presets"
                        class="min-w-0 flex-1 rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                    <datalist id="labor-presets">
                        @foreach (['Arıza tespiti', 'Ekran değişimi işçiliği', 'Batarya değişimi işçiliği', 'Şarj soketi değişimi', 'Anakart onarımı (mikro lehim)', 'Yazılım yükleme', 'Sıvı teması temizliği', 'Veri yedekleme'] as $p)<option value="{{ $p }}">@endforeach
                    </datalist>
                    <input wire:model="laborPrice" inputmode="decimal" placeholder="₺"
                        class="w-24 rounded-lg border-0 bg-white px-3 py-2 text-right font-mono text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 dark:bg-slate-950 dark:text-white dark:ring-white/10">
                    <button class="rounded-lg bg-slate-900 px-3 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900">Ekle</button>
                </div>
                @error('laborName') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                @error('laborPrice') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </form>
        </div>

        @error('quote') <p class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">{{ $message }}</p> @enderror

        {{-- Teklif durumu ve eylemler --}}
        <div class="flex flex-wrap items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-white/5">
            <div class="min-w-0 flex-1 text-sm">
                @switch($ticket->quote_status)
                    @case('pending')
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $money($ticket->quote_total) }} teklif {{ $ticket->quote_sent_at->locale('tr')->diffForHumans() }} gönderildi.</p>
                        <p class="text-xs text-slate-500">Müşteri takip sayfasında onay / ret butonlarını görür. Telefonla onay aldıysanız aşağıdan işaretleyin.</p>
                        @break
                    @case('approved')
                        <p class="font-semibold text-emerald-700 dark:text-emerald-300">✓ {{ $money($ticket->quote_total) }} teklif {{ $ticket->quote_decided_at->format('d.m.Y H:i') }} tarihinde {{ $ticket->quote_decided_by === 'customer' ? 'müşteri tarafından online' : 'servis tarafından (müşteri adına)' }} onaylandı.</p>
                        @if ($ticket->quote_decision_ip) <p class="text-[11px] text-slate-500">Onay IP: {{ $ticket->quote_decision_ip }}</p> @endif
                        @break
                    @case('rejected')
                        <p class="font-semibold text-rose-700 dark:text-rose-300">✗ Teklif {{ $ticket->quote_decided_at->format('d.m.Y H:i') }} tarihinde reddedildi.</p>
                        @break
                    @default
                        <p class="text-slate-600 dark:text-slate-300">Kalemleri ekleyip teklifi müşteriye gönderin.</p>
                @endswitch
                @if ($ticket->quote_customer_note) <p class="mt-1 rounded-md bg-white px-2 py-1 text-xs text-slate-700 ring-1 ring-slate-200 dark:bg-white/5 dark:text-slate-200 dark:ring-white/10">💬 {{ $ticket->quote_customer_note }}</p> @endif
                @if ($outdated) <p class="mt-1 text-xs font-semibold text-amber-600">Kalemler teklif gönderildikten sonra değişti; güncel tutar {{ $money($total) }}. Yeniden gönderin.</p> @endif
            </div>
            @if ($ticket->quote_status === 'pending')
                <button type="button" wire:click="decide(true)" wire:confirm="Müşteri teklifi onayladı olarak işaretlensin mi?" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500">Telefonla onaylandı</button>
                <button type="button" wire:click="decide(false)" wire:confirm="Teklif reddedildi olarak işaretlensin mi?" class="rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50 dark:ring-rose-500/30 dark:hover:bg-rose-500/10">Reddetti</button>
            @endif
            @unless ($ticket->status->isClosed())
                <button type="button" wire:click="send" wire:loading.attr="disabled" @disabled($ticket->items->isEmpty())
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-40">
                    {{ $ticket->quote_status ? 'Teklifi yeniden gönder' : 'Teklifi müşteriye gönder' }}
                </button>
            @endunless
        </div>
    </div>
</section>
