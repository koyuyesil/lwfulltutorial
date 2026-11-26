{{-- Baskı başlığı: işletme (logo, unvan, iletişim, vergi) + belge adı + takip QR. $title, $compact, $copy --}}
@php
    $address = \App\Support\Company::addressLine($company);
    $contact = implode(' · ', array_filter([$company['phone'], $company['email'], $company['website']]));
    $tax = \App\Support\Company::taxLine($company);
@endphp
<div class="head {{ $compact ? 'compact' : '' }}">
    <div class="brand">
        @if ($company['logo'])
            <img src="{{ $company['logo'] }}" alt="" class="logo">
        @endif
        <div style="min-width:0">
            <h1>{{ $company['name'] }}</h1>
            @if ($company['legal_name'])<p class="muted small">{{ $company['legal_name'] }}</p>@endif
            @if ($address)<p class="muted small">{{ $address }}</p>@endif
            @if ($contact)<p class="muted small">{{ $contact }}</p>@endif
            @if ($tax)<p class="muted small">{{ $tax }}</p>@endif
        </div>
    </div>
    <div class="doc">
        @if (! empty($copy))<p class="copy">{{ $copy }}</p>@endif
        <p class="doc-title">{{ $title }}</p>
        <div class="qr" data-qr="{{ $ticket->trackingUrl() }}"></div>
        <p class="mono code">{{ $ticket->tracking_code }}</p>
    </div>
</div>
