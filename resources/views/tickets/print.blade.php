@php
    $client = $ticket->clientProduct->client;
    $device = $ticket->clientProduct;
    $product = $device->product;
    $intake = (array) $ticket->intake;
    $money = fn ($v) => \App\Models\Ticket::money((float) $v);
    $thermal = in_array($format, ['80', '58'], true);
    $sheet = ['a4' => '210mm', 'a4-2' => '210mm', 'a5' => '148mm', '80' => '80mm', '58' => '58mm', 'label' => '50mm'][$format];
    $page = ['a4' => 'A4', 'a4-2' => 'A4', 'a5' => 'A5 portrait', 'label' => '50mm 30mm'][$format] ?? $sheet.' auto';
    $titles = ['a4' => 'Servis kabul formu', 'a4-2' => 'Servis kabul formu', 'a5' => 'Servis kabul formu', '80' => 'Servis fişi', '58' => 'Servis fişi', 'label' => 'Cihaz etiketi'];
    $formats = ['a4' => 'A4 form', 'a4-2' => 'A4 · 2 nüsha', 'a5' => 'A5 dikey', '80' => 'Termal 80 mm', '58' => 'Termal 58 mm', 'label' => 'QR etiket'];
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $ticket->tracking_code }} · {{ $titles[$format] }}</title>
    @vite(['resources/js/print.js'])
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: ui-sans-serif, system-ui, "Segoe UI", Arial, sans-serif; color: #000; background: #e5e7eb; }
        .sheet { width: {{ $sheet }}; margin: 16px auto; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,.15); }
        .mono { font-family: ui-monospace, Consolas, monospace; }
        .muted { color: #555; }
        .small { font-size: .88em; }
        .center { text-align: center; }
        .m0 { margin: 0; }
        .qr svg { display: block; width: 100%; height: 100%; }
        table { width: 100%; border-collapse: collapse; }
        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; padding: 10px; background: #0f172a; }
        .toolbar a, .toolbar button { font: 600 13px/1 ui-sans-serif, system-ui; color: #fff; background: #334155; border: 0; border-radius: 8px; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .toolbar .on { background: #4f46e5; }

        /* Form (A4 / A4 yarım / A5) */
        .form { padding: 12mm; font-size: 11.5px; line-height: 1.42; }
        .form h1 { font-size: 18px; margin: 0; line-height: 1.15; }
        .form h2 { font-size: 10px; letter-spacing: .12em; text-transform: uppercase; color: #444; margin: 10px 0 5px; border-bottom: 1px solid #ddd; padding-bottom: 2px; }
        .form p { margin: 0; }
        .head { display: flex; justify-content: space-between; gap: 14px; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .brand { display: flex; gap: 10px; min-width: 0; }
        .logo { width: 16mm; height: 16mm; object-fit: contain; flex-shrink: 0; }
        .doc { text-align: center; flex-shrink: 0; }
        .doc .qr { width: 26mm; height: 26mm; margin: 3px auto 0; }
        .doc-title { font-size: 12px; font-weight: 800; }
        .copy { display: inline-block; font-size: 9px; font-weight: 900; letter-spacing: .15em; border: 1.5px solid #000; border-radius: 3px; padding: 1px 6px; margin-bottom: 3px; }
        .code { font-size: 13px; font-weight: 900; margin-top: 2px !important; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; }
        dl { margin: 0; }
        dt { color: #555; font-size: 9px; text-transform: uppercase; letter-spacing: .06em; }
        dd { margin: 0 0 3px; font-weight: 600; }
        td, th { border-bottom: 1px solid #e5e5e5; padding: 3px 2px; text-align: left; }
        th { font-size: 9px; text-transform: uppercase; color: #555; }
        .chips span { display: inline-block; border: 1px solid #999; border-radius: 999px; padding: 0 7px; margin: 0 3px 3px 0; font-size: 10px; }
        .photos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; }
        .photos img { width: 100%; aspect-ratio: 4/3; object-fit: cover; border: 1px solid #ccc; }
        .meta { display: flex; flex-wrap: wrap; gap: 4px 16px; margin-top: 8px; }
        .terms { color: #444; font-size: 9.5px; line-height: 1.35; }
        .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 12px; }
        .sign div { border-top: 1px solid #000; padding-top: 3px; font-size: 9.5px; color: #333; min-height: 46px; }
        .sign img { height: 44px; display: block; }
        .footer { margin-top: 8px !important; border-top: 1px dashed #999; padding-top: 4px; text-align: center; font-size: 10px; color: #333; }

        /* Sıkı düzen: A4'ün yarısı ve A5 */
        .compact { font-size: 9.6px; line-height: 1.32; }
        .compact h1 { font-size: 14px; }
        .compact h2 { margin: 6px 0 3px; font-size: 8.5px; }
        .compact .head { padding-bottom: 5px; }
        .compact .logo { width: 12mm; height: 12mm; }
        .compact .doc .qr { width: 20mm; height: 20mm; }
        .compact .sign { margin-top: 8px; }
        .compact .sign div { min-height: 34px; }
        .compact .sign img { height: 32px; }
        .compact .terms { font-size: 8.3px; }

        /* A4 · 2 nüsha: her yarı 148.5 mm */
        .half { height: 148.5mm; padding: 8mm 11mm; overflow: hidden; position: relative; }
        .cut { position: relative; height: 0; border-top: 1.5px dashed #555; }
        .cut span { position: absolute; left: 50%; top: -9px; transform: translateX(-50%); background: #fff; padding: 0 8px; font-size: 11px; color: #555; letter-spacing: .1em; }
        .a5 { min-height: 210mm; padding: 9mm 9mm; }

        /* Termal */
        .thermal { padding: 4mm 3mm; font-size: {{ $format === '58' ? '10px' : '11.5px' }}; line-height: 1.35; }
        .thermal .code { font-size: {{ $format === '58' ? '17px' : '21px' }}; font-weight: 900; letter-spacing: .06em; }
        .thermal hr { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
        .thermal .qr { width: {{ $format === '58' ? '32mm' : '40mm' }}; height: {{ $format === '58' ? '32mm' : '40mm' }}; margin: 6px auto; }
        .thermal .row { display: flex; justify-content: space-between; gap: 6px; }
        .thermal .tlogo { max-height: 14mm; max-width: 40mm; display: block; margin: 0 auto 3px; filter: grayscale(1) contrast(1.4); }

        /* Etiket 50×30 mm */
        .label { height: 30mm; padding: 2mm; display: grid; grid-template-columns: 24mm 1fr; gap: 2mm; align-items: center; overflow: hidden; }
        .label .qr { width: 24mm; height: 24mm; }
        .label .code { font-size: 10.5px; font-weight: 900; letter-spacing: .03em; margin: 0; }
        .label p { margin: 0; font-size: 8.5px; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; }
            @page { size: {{ $page }}; margin: 0; }
        }
    </style>
</head>
<body data-autoprint="{{ $autoprint ? '1' : '0' }}">
    <nav class="toolbar">
        @foreach ($formats as $f => $l)
            <a href="{{ route('tickets.print', [$ticket, $f]) }}?onizleme=1" class="{{ $f === $format ? 'on' : '' }}">{{ $l }}</a>
        @endforeach
        <button type="button" onclick="window.print()">🖨 Yazdır</button>
    </nav>

    @if ($format === 'a4')
        <main class="sheet form">
            @include('tickets.print.header', ['title' => 'Servis kabul formu', 'compact' => false, 'copy' => null])
            @include('tickets.print.body', ['compact' => false, 'audience' => 'full'])
        </main>

    @elseif ($format === 'a4-2')
        {{-- Üst yarı müşteriye verilir, alt yarı işletmede kalır --}}
        <main class="sheet compact">
            <section class="half form compact">
                @include('tickets.print.header', ['title' => 'Servis kabul formu', 'compact' => true, 'copy' => 'MÜŞTERİ NÜSHASI'])
                @include('tickets.print.body', ['compact' => true, 'audience' => 'customer'])
            </section>
            <div class="cut"><span>✂ buradan kesin</span></div>
            <section class="half form compact">
                @include('tickets.print.header', ['title' => 'Servis kabul formu', 'compact' => true, 'copy' => 'İŞLETME NÜSHASI'])
                @include('tickets.print.body', ['compact' => true, 'audience' => 'business'])
            </section>
        </main>

    @elseif ($format === 'a5')
        <main class="sheet form compact a5">
            @include('tickets.print.header', ['title' => 'Servis kabul formu', 'compact' => true, 'copy' => null])
            @include('tickets.print.body', ['compact' => true, 'audience' => 'full'])
        </main>

    @elseif ($thermal)
        <main class="sheet thermal">
            <div class="center">
                @if ($company['logo'])<img src="{{ $company['logo'] }}" alt="" class="tlogo">@endif
                <strong style="font-size:1.15em">{{ $company['name'] }}</strong>
                @if ($company['phone'])<div>{{ $company['phone'] }}</div>@endif
                @if ($a = \App\Support\Company::addressLine($company))<div class="small">{{ $a }}</div>@endif
            </div>
            <hr>
            <div class="center">
                <div class="muted">TAKİP KODU</div>
                <div class="code mono">{{ $ticket->tracking_code }}</div>
                <div class="qr" data-qr="{{ $ticket->trackingUrl() }}"></div>
                <div class="muted" style="font-size:.85em">QR'ı okutup durumu takip edin</div>
            </div>
            <hr>
            <div class="row"><span>Tarih</span><span>{{ $ticket->created_at->format('d.m.Y H:i') }}</span></div>
            <div class="row"><span>Tahmini</span><span>{{ $ticket->due_at?->format('d.m.Y') ?? '—' }}</span></div>
            <div class="row"><span>Müşteri</span><span>{{ $client->full_name }}</span></div>
            <div class="row"><span>Cihaz</span><span>{{ $product->brand }} {{ $product->model_name }}</span></div>
            @if ($device->imei)<div class="row"><span>IMEI</span><span class="mono">{{ $device->imei }}</span></div>@endif
            <hr>
            <div><strong>Arıza:</strong> {{ $ticket->problem }}</div>
            @if (! empty($intake['accessories']))<div><strong>Aksesuar:</strong> {{ implode(', ', (array) $intake['accessories']) }}</div>@endif
            @if ($ticket->items->isNotEmpty())
                <hr>
                @foreach ($ticket->items as $item)
                    <div class="row"><span>{{ $item->qty }}× {{ $item->name }}</span><span>{{ $money($item->total()) }}</span></div>
                @endforeach
                <div class="row" style="font-weight:900; margin-top:3px"><span>TOPLAM</span><span>{{ $money($ticket->itemsTotal()) }}</span></div>
            @endif
            <hr>
            <div class="center muted" style="font-size:.85em">{{ $company['footer'] ?: 'Cihazınızı teslim alırken bu fişi getiriniz.' }}</div>
        </main>

    @else
        <main class="sheet label">
            <div class="qr" data-qr="{{ route('tickets.show', $ticket) }}"></div>
            <div style="min-width:0">
                <p class="code mono">{{ $ticket->tracking_code }}</p>
                <p><strong>{{ $client->full_name }}</strong></p>
                <p>{{ $product->brand }} {{ $product->model_name }}</p>
                <p>{{ \Illuminate\Support\Str::limit($ticket->problem, 40) }}</p>
                <p>{{ $ticket->created_at->format('d.m.Y') }} → {{ $ticket->due_at?->format('d.m') ?? '—' }}{{ $ticket->technician ? ' · '.$ticket->technician : '' }}</p>
            </div>
        </main>
    @endif
</body>
</html>
