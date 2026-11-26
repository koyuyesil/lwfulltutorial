<?php

namespace App\Support;

use App\Models\User;

/**
 * İşletme (firma) bilgileri: profil sayfasından düzenlenir; servis fişleri, baskılar,
 * müşteri takip sayfası ve bildirim şablonlarında kullanılır.
 */
class Company
{
    /** Ayar anahtarı => etiket */
    public const FIELDS = [
        'name' => 'İşletme adı',
        'legal_name' => 'Ticari unvan',
        'phone' => 'Telefon',
        'email' => 'E-posta',
        'website' => 'Web sitesi',
        'address' => 'Adres',
        'city' => 'İl / ilçe',
        'tax_office' => 'Vergi dairesi',
        'tax_number' => 'Vergi / TC kimlik no',
        'terms' => 'Servis koşulları (fişlerde)',
        'footer' => 'Fiş alt notu',
        'logo' => 'Logo',
    ];

    public const DEFAULT_TERMS = 'Cihaz yukarıda belirtilen dış durum ve aksesuarlarla teslim alınmıştır. Teşhis sonrası onarım bedeli müşteriye bildirilir; onay alınmadan onarıma başlanmaz. Veri kaybına karşı yedek alınması müşterinin sorumluluğundadır. Teslime hazır bildirimi sonrası 90 gün içinde alınmayan cihazlardan servis sorumlu değildir.';

    /** @return array<string, string|null> */
    public static function for(?User $user): array
    {
        $data = [];
        foreach (array_keys(self::FIELDS) as $key) {
            $value = $user?->setting('company.'.$key);
            $data[$key] = is_string($value) && trim($value) !== '' ? $value : null;
        }
        $data['name'] ??= $user?->name ?? config('app.name');
        $data['terms'] ??= self::DEFAULT_TERMS;

        return $data;
    }

    /** Tek satır adres: "Adres, İl / ilçe" */
    public static function addressLine(array $company): ?string
    {
        return implode(', ', array_filter([$company['address'] ?? null, $company['city'] ?? null])) ?: null;
    }

    /** Vergi satırı: "Kadıköy VD · 1234567890" */
    public static function taxLine(array $company): ?string
    {
        return implode(' · ', array_filter([
            ($company['tax_office'] ?? null) ? $company['tax_office'].' VD' : null,
            $company['tax_number'] ?? null,
        ])) ?: null;
    }

    /** Profilin tamamlanma oranı (%) — logo, vergi gibi alanlar baskıların profesyonel görünmesi için. */
    public static function completeness(array $company): int
    {
        $keys = ['name', 'phone', 'email', 'address', 'city', 'tax_office', 'tax_number', 'logo'];
        $filled = count(array_filter($keys, fn ($k) => ! empty($company[$k])));

        return (int) round($filled / count($keys) * 100);
    }
}
