<?php

namespace App\Support;

/**
 * Örnek CSV şablonu: popüler markaların gerçek modelleri.
 * Görseller, telifli ürün fotoğrafı yerine marka renginde üretilen küçük SVG çizimlerdir (base64).
 *
 * Sütunlar: brand, model_name, model_number, codename, manufacturer, device_type, release_year, chipset, description
 */
class SampleDeviceCatalog
{
    /** Marka → [arka plan rengi, vurgu rengi] */
    protected const BRAND_COLORS = [
        'Samsung' => ['#1428A0', '#5B8DEF'],
        'Apple' => ['#1D1D1F', '#A1A1A6'],
        'Xiaomi' => ['#FF6900', '#FFB27A'],
        'Redmi' => ['#E2231A', '#FF8A80'],
        'POCO' => ['#FFC400', '#1A1A1A'],
        'Google' => ['#4285F4', '#34A853'],
        'OnePlus' => ['#EB0029', '#FF8A8A'],
        'Huawei' => ['#C7000B', '#FF6B6B'],
        'Nothing' => ['#111111', '#D71921'],
        'Motorola' => ['#001428', '#5C92FA'],
        'OPPO' => ['#1A7F5A', '#6FD3A8'],
        'realme' => ['#F5C400', '#1A1A1A'],
        'vivo' => ['#415FFF', '#9DB0FF'],
        'Honor' => ['#0A0A0A', '#00B4FF'],
    ];

    /** @return list<array<string, string|int|null>> */
    public static function rows(): array
    {
        $r = fn ($brand, $model, $number, $codename, $year, $chipset, $type = 'phone', $manufacturer = null, $description = null) => [
            'brand' => $brand, 'model_name' => $model, 'model_number' => $number, 'codename' => $codename,
            'manufacturer' => $manufacturer ?? $brand, 'device_type' => $type, 'release_year' => $year,
            'chipset' => $chipset, 'description' => $description,
        ];

        return [
            // Samsung
            $r('Samsung', 'Galaxy S24 Ultra', 'SM-S928B', 'e3q', 2024, 'Snapdragon 8 Gen 3', description: 'S Pen dahili; titanyum kasa.'),
            $r('Samsung', 'Galaxy S24', 'SM-S921B', 'e1s', 2024, 'Exynos 2400'),
            $r('Samsung', 'Galaxy S23 Ultra', 'SM-S918B', 'dm3q', 2023, 'Snapdragon 8 Gen 2'),
            $r('Samsung', 'Galaxy S23', 'SM-S911B', 'dm1q', 2023, 'Snapdragon 8 Gen 2'),
            $r('Samsung', 'Galaxy S22 Ultra', 'SM-S908B', 'b0s', 2022, 'Exynos 2200'),
            $r('Samsung', 'Galaxy S21', 'SM-G991B', 'o1s', 2021, 'Exynos 2100'),
            $r('Samsung', 'Galaxy S20 FE', 'SM-G780F', 'r8s', 2020, 'Exynos 990'),
            $r('Samsung', 'Galaxy Note10+', 'SM-N975F', 'd2s', 2019, 'Exynos 9825'),
            $r('Samsung', 'Galaxy Z Fold5', 'SM-F946B', 'q5q', 2023, 'Snapdragon 8 Gen 2', description: 'Katlanabilir; iç ekran değişimi uzman işçilik ister.'),
            $r('Samsung', 'Galaxy Z Flip5', 'SM-F731B', 'b5q', 2023, 'Snapdragon 8 Gen 2'),
            $r('Samsung', 'Galaxy A54 5G', 'SM-A546B', 'a54x', 2023, 'Exynos 1380'),
            $r('Samsung', 'Galaxy A53 5G', 'SM-A536B', 'a53x', 2022, 'Exynos 1280'),
            $r('Samsung', 'Galaxy A34 5G', 'SM-A346B', 'a34x', 2023, 'Dimensity 1080'),
            $r('Samsung', 'Galaxy A14', 'SM-A145F', 'a14', 2023, 'Helio G80'),
            $r('Samsung', 'Galaxy Tab S9', 'SM-X710', 'gts9wifi', 2023, 'Snapdragon 8 Gen 2', 'tablet'),

            // Apple (codename: dahili kod adı)
            $r('Apple', 'iPhone 15 Pro Max', 'A3106', 'D84', 2023, 'A17 Pro'),
            $r('Apple', 'iPhone 15 Pro', 'A3102', 'D83', 2023, 'A17 Pro'),
            $r('Apple', 'iPhone 15', 'A3090', 'D37', 2023, 'A16 Bionic'),
            $r('Apple', 'iPhone 14 Pro Max', 'A2894', 'D74', 2022, 'A16 Bionic'),
            $r('Apple', 'iPhone 14 Pro', 'A2890', 'D73', 2022, 'A16 Bionic'),
            $r('Apple', 'iPhone 14', 'A2882', 'D27', 2022, 'A15 Bionic'),
            $r('Apple', 'iPhone 13', 'A2633', 'D17', 2021, 'A15 Bionic'),
            $r('Apple', 'iPhone 12', 'A2403', 'D53g', 2020, 'A14 Bionic'),
            $r('Apple', 'iPhone 11', 'A2221', 'N104', 2019, 'A13 Bionic'),
            $r('Apple', 'iPhone SE (3. nesil)', 'A2595', 'D49', 2022, 'A15 Bionic'),

            // Xiaomi / Redmi / POCO
            $r('Xiaomi', 'Xiaomi 14', '23127PN0CG', 'houji', 2024, 'Snapdragon 8 Gen 3'),
            $r('Xiaomi', 'Xiaomi 13T Pro', '23078PND5G', 'corot', 2023, 'Dimensity 9200+'),
            $r('Xiaomi', 'Xiaomi 13T', '2306EPN60G', 'aristotle', 2023, 'Dimensity 8200-Ultra'),
            $r('Xiaomi', 'Xiaomi 13', '2211133G', 'fuxi', 2023, 'Snapdragon 8 Gen 2'),
            $r('Xiaomi', 'Xiaomi 12', '2201123G', 'cupid', 2022, 'Snapdragon 8 Gen 1'),
            $r('Xiaomi', 'Xiaomi 11T Pro', '2107113SG', 'vili', 2021, 'Snapdragon 888'),
            $r('Redmi', 'Redmi Note 13 Pro+ 5G', '23090RA98G', 'zircon', 2024, 'Dimensity 7200-Ultra', manufacturer: 'Xiaomi'),
            $r('Redmi', 'Redmi Note 13 Pro 5G', '23117RA68G', 'garnet', 2024, 'Snapdragon 7s Gen 2', manufacturer: 'Xiaomi'),
            $r('Redmi', 'Redmi Note 12 Pro 5G', '22101316G', 'ruby', 2023, 'Dimensity 1080', manufacturer: 'Xiaomi'),
            $r('Redmi', 'Redmi Note 11', '2201117TG', 'spes', 2022, 'Snapdragon 680', manufacturer: 'Xiaomi'),
            $r('Redmi', 'Redmi Note 10 Pro', 'M2101K6G', 'sweet', 2021, 'Snapdragon 732G', manufacturer: 'Xiaomi'),
            $r('Redmi', 'Redmi Note 8 Pro', 'M1906G7G', 'begonia', 2019, 'Helio G90T', manufacturer: 'Xiaomi'),
            $r('POCO', 'POCO F6', '24069PC21G', 'peridot', 2024, 'Snapdragon 8s Gen 3', manufacturer: 'Xiaomi'),
            $r('POCO', 'POCO X6 Pro', '2311DRK48G', 'duchamp', 2024, 'Dimensity 8300-Ultra', manufacturer: 'Xiaomi'),
            $r('POCO', 'POCO F5 Pro', '23013PC75G', 'mondrian', 2023, 'Snapdragon 8+ Gen 1', manufacturer: 'Xiaomi'),
            $r('POCO', 'POCO F5', '23049PCD8G', 'marble', 2023, 'Snapdragon 7+ Gen 2', manufacturer: 'Xiaomi'),
            $r('POCO', 'POCO X5 Pro 5G', '22101320G', 'redwood', 2023, 'Snapdragon 778G', manufacturer: 'Xiaomi'),

            // Google
            $r('Google', 'Pixel 8 Pro', 'GC3VE', 'husky', 2023, 'Google Tensor G3'),
            $r('Google', 'Pixel 8', 'GKWS6', 'shiba', 2023, 'Google Tensor G3'),
            $r('Google', 'Pixel 8a', 'GKV4X', 'akita', 2024, 'Google Tensor G3'),
            $r('Google', 'Pixel 7 Pro', 'GE2AE', 'cheetah', 2022, 'Google Tensor G2'),
            $r('Google', 'Pixel 7', 'GVU6C', 'panther', 2022, 'Google Tensor G2'),
            $r('Google', 'Pixel 7a', 'GWKK3', 'lynx', 2023, 'Google Tensor G2'),
            $r('Google', 'Pixel 6 Pro', 'GLU0G', 'raven', 2021, 'Google Tensor'),
            $r('Google', 'Pixel 6', 'GB7N6', 'oriole', 2021, 'Google Tensor'),
            $r('Google', 'Pixel 6a', 'GX7AS', 'bluejay', 2022, 'Google Tensor'),

            // OnePlus
            $r('OnePlus', 'OnePlus 12', 'CPH2581', 'waffle', 2024, 'Snapdragon 8 Gen 3'),
            $r('OnePlus', 'OnePlus 11', 'CPH2449', 'salami', 2023, 'Snapdragon 8 Gen 2'),
            $r('OnePlus', 'OnePlus 9 Pro', 'LE2123', 'lemonadep', 2021, 'Snapdragon 888'),
            $r('OnePlus', 'OnePlus 8T', 'KB2003', 'kebab', 2020, 'Snapdragon 865'),

            // Huawei
            $r('Huawei', 'P40 Pro', 'ELS-NX9', null, 2020, 'Kirin 990 5G'),
            $r('Huawei', 'P30 Pro', 'VOG-L29', null, 2019, 'Kirin 980'),
            $r('Huawei', 'Mate 20 Pro', 'LYA-L29', null, 2018, 'Kirin 980'),

            // Diğer
            $r('Nothing', 'Phone (2)', 'A065', 'Pong', 2023, 'Snapdragon 8+ Gen 1'),
            $r('Nothing', 'Phone (1)', 'A063', 'Spacewar', 2022, 'Snapdragon 778G+'),
            $r('Motorola', 'Edge 30 Pro', 'XT2201-1', 'hiphi', 2022, 'Snapdragon 8 Gen 1'),
            $r('OPPO', 'Find X5 Pro', 'CPH2305', null, 2022, 'Snapdragon 8 Gen 1'),
            $r('realme', 'GT 2 Pro', 'RMX3301', null, 2022, 'Snapdragon 8 Gen 1'),
            $r('vivo', 'X90 Pro', 'V2219', null, 2023, 'Dimensity 9200'),
            $r('Honor', 'Magic5 Pro', 'PGT-N19', null, 2023, 'Snapdragon 8 Gen 2'),
        ];
    }

    /** Marka renginde, model adının baş harfleriyle telefon / tablet çizimi (SVG, base64 data URI). */
    public static function image(string $brand, string $model, string $type = 'phone'): string
    {
        [$bg, $accent] = self::BRAND_COLORS[$brand] ?? ['#334155', '#94A3B8'];
        $label = htmlspecialchars(mb_strtoupper(mb_substr($brand, 0, 1)), ENT_XML1);
        $text = htmlspecialchars(mb_strimwidth($model, 0, 18, '…'), ENT_XML1);

        [$w, $h, $rx] = $type === 'tablet' ? [150, 110, 10] : [70, 140, 12];
        $x = (200 - $w) / 2;
        $y = (180 - $h) / 2 - 10;
        $cy = $y + $h / 2 + 12;

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{$bg}"/><stop offset="1" stop-color="{$accent}"/></linearGradient></defs><rect width="200" height="200" fill="#F1F5F9"/><rect x="{$x}" y="{$y}" width="{$w}" height="{$h}" rx="{$rx}" fill="url(#g)"/><rect x="{$x}" y="{$y}" width="{$w}" height="{$h}" rx="{$rx}" fill="none" stroke="#0F172A" stroke-opacity=".25" stroke-width="3"/><text x="100" y="{$cy}" font-family="Arial,sans-serif" font-size="34" font-weight="700" fill="#fff" text-anchor="middle">{$label}</text><text x="100" y="190" font-family="Arial,sans-serif" font-size="14" font-weight="700" fill="#0F172A" text-anchor="middle">{$text}</text></svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /** Görselleriyle birlikte CSV satırları (ilk satır başlık). */
    public static function csvRows(): \Generator
    {
        yield \App\Services\DeviceCatalog::COLUMNS;

        foreach (self::rows() as $row) {
            yield [
                $row['brand'], $row['model_name'], $row['model_number'], $row['codename'], $row['manufacturer'],
                $row['device_type'], $row['release_year'], $row['chipset'], $row['description'],
                self::image($row['brand'], $row['model_name'], $row['device_type']),
            ];
        }
    }
}
