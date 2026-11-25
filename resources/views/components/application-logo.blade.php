{{--
    KoyuMavi simgesi: koyu maviden gök mavisine geçen gökyüzü, hilal ve beş köşeli yıldız.
    Yıldızın bir köşesi hilalin açıklığına bakar. Kendi arka planı vardır; sarmalayıcıya gerek yoktur.
--}}
@php($uid = 'km'.\Illuminate\Support\Str::random(6))
<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ config('app.name', 'KoyuMavi') }}" {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $uid }}-sky" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#0B1E4D" />
            <stop offset="0.55" stop-color="#123E8C" />
            <stop offset="1" stop-color="#2A7DE1" />
        </linearGradient>
        <radialGradient id="{{ $uid }}-glow" cx="0.3" cy="0.25" r="0.75">
            <stop offset="0" stop-color="#FFFFFF" stop-opacity="0.18" />
            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0" />
        </radialGradient>
        <mask id="{{ $uid }}-crescent">
            <rect width="64" height="64" fill="#000" />
            <circle cx="27" cy="32" r="15" fill="#fff" />
            <circle cx="31" cy="32" r="12" fill="#000" />
        </mask>
    </defs>
    <rect width="64" height="64" rx="14" fill="url(#{{ $uid }}-sky)" />
    <rect width="64" height="64" rx="14" fill="url(#{{ $uid }}-glow)" />
    <rect width="64" height="64" fill="#fff" mask="url(#{{ $uid }}-crescent)" />
    <polygon fill="#fff" points="37.5,32 41.99,30.54 41.99,25.82 44.77,29.64 49.26,28.18 46.48,32 49.26,35.82 44.77,34.36 41.99,38.18 41.99,33.46" />
</svg>
