{{--
    Servis formu gövdesi. $compact: yarım sayfa / A5 için sıkı düzen.
    $audience: customer (müşteri nüshası) | business (işletme nüshası: kilit türü, teknisyen, imza) | full
--}}
@php
    $client = $ticket->clientProduct->client;
    $device = $ticket->clientProduct;
    $product = $device->product;
    $intake = (array) $ticket->intake;
    $money = fn ($v) => \App\Models\Ticket::money((float) $v);
    $lockLabels = ['none' => 'Kilit yok', 'pin' => 'PIN', 'pattern' => 'Desen', 'password' => 'Şifre'];
    $business = $audience !== 'customer';
@endphp

<div class="grid2">
    <div>
        <h2>Müşteri</h2>
        <dl>
            <dt>Ad soyad</dt><dd>{{ $client->full_name }}{{ $client->company ? ' · '.$client->company : '' }}</dd>
            <dt>Telefon</dt><dd>{{ $client->phone }}</dd>
            @if ($client->email && ! $compact)<dt>E-posta</dt><dd>{{ $client->email }}</dd>@endif
        </dl>
    </div>
    <div>
        <h2>Cihaz</h2>
        <dl>
            <dt>Marka / model</dt><dd>{{ $product->brand }} {{ $product->model_name }}{{ $product->model_number ? ' ('.$product->model_number.')' : '' }}</dd>
            <dt>IMEI / seri</dt><dd class="mono">{{ $device->imei ?: '—' }} / {{ $device->serial ?: '—' }}</dd>
            @if ($business)
                <dt>Renk · ekran kilidi</dt><dd>{{ $device->color ?: '—' }} · {{ $lockLabels[$ticket->lock_type ?? 'none'] ?? '—' }}{{ $ticket->lock_type && $ticket->lock_type !== 'none' ? ' (serviste kayıtlı)' : '' }}</dd>
            @endif
        </dl>
    </div>
</div>

<h2>Arıza / müşteri şikâyeti</h2>
<p class="m0">{{ $ticket->problem }}</p>

<div class="grid2">
    <div>
        <h2>Aksesuarlar</h2>
        <div class="chips">@forelse ((array) ($intake['accessories'] ?? []) as $a)<span>{{ $a }}</span>@empty<span>Yok</span>@endforelse</div>
    </div>
    <div>
        <h2>Kabuldeki dış durum</h2>
        <div class="chips">@forelse ((array) ($intake['conditions'] ?? []) as $a)<span>{{ $a }}</span>@empty<span>Belirgin hasar yok</span>@endforelse</div>
    </div>
</div>
@if (! empty($intake['notes']) && $business)<p class="m0 small"><strong>Not:</strong> {{ $intake['notes'] }}</p>@endif

@if (! $compact && $ticket->photos->isNotEmpty())
    <h2>Kabul fotoğrafları</h2>
    <div class="photos">
        @foreach ($ticket->photos->take(8) as $photo)
            @if ($src = $photo->dataUri())<img src="{{ $src }}" alt="">@endif
        @endforeach
    </div>
@endif

@if ($ticket->items->isNotEmpty())
    <h2>Onarım teklifi {{ $ticket->quote_status === 'approved' ? '· müşteri onaylı' : '' }}</h2>
    <table>
        @unless ($compact)
            <thead><tr><th>Kalem</th><th style="width:40px">Adet</th><th style="width:80px; text-align:right">Birim</th><th style="width:80px; text-align:right">Tutar</th></tr></thead>
        @endunless
        <tbody>
            @foreach ($ticket->items as $item)
                <tr>
                    <td>{{ $item->name }}{{ $compact && $item->qty > 1 ? ' × '.$item->qty : '' }}</td>
                    @unless ($compact)<td>{{ $item->qty }}</td><td style="text-align:right">{{ $money($item->unit_price) }}</td>@endunless
                    <td style="text-align:right">{{ $money($item->total()) }}</td>
                </tr>
            @endforeach
            <tr><td colspan="{{ $compact ? 1 : 3 }}" style="text-align:right; font-weight:800">Toplam</td><td style="text-align:right; font-weight:900">{{ $money($ticket->itemsTotal()) }}</td></tr>
        </tbody>
    </table>
@endif

<div class="meta small">
    <span>Kabul: <strong>{{ $ticket->created_at->format('d.m.Y H:i') }}</strong></span>
    <span>Tahmini teslim: <strong>{{ $ticket->due_at?->format('d.m.Y') ?? '—' }}</strong></span>
    @if ($business && $ticket->technician)<span>Teknisyen: <strong>{{ $ticket->technician }}</strong></span>@endif
</div>

<h2>Koşullar</h2>
<p class="terms">{{ $company['terms'] }}</p>

<div class="sign">
    <div>
        @if ($ticket->signature && $audience !== 'customer')<img src="{{ $ticket->signature }}" alt="Müşteri imzası">@endif
        Müşteri imzası {{ $ticket->signed_at && $audience !== 'customer' ? '· '.$ticket->signed_at->format('d.m.Y H:i') : '' }}
    </div>
    <div>Teslim alan (servis){{ $ticket->technician ? ' · '.$ticket->technician : '' }}</div>
</div>
@if ($audience === 'customer')
    <p class="small center muted" style="margin-top:6px">Durumu takip etmek için QR'ı okutun ya da {{ str_replace(['http://', 'https://'], '', route('tracking')) }} adresine takip kodunuzu girin.</p>
@endif
@if ($company['footer'] && $audience !== 'business')<p class="footer">{{ $company['footer'] }}</p>@endif
