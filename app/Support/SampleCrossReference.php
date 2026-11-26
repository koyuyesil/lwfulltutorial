<?php

namespace App\Support;

/**
 * Çapraz referans örnek verisi: örnek cihaz kataloğundaki (SampleDeviceCatalog) modellerin yonga setleri.
 * Parça kodu kesin bilinenler kod olarak yazılır; doğrulanamayanlarda ticari ad kullanılır ve not düşülür.
 * RAM / depolama parça numaraları cihaz partisine göre değiştiği için örnekte yoktur.
 */
class SampleCrossReference
{
    /** chipset (katalogdaki ad) => [kod, üretici, not] */
    public const SOCS = [
        'Snapdragon 8 Gen 3' => ['SM8650', 'Qualcomm', null],
        'Snapdragon 8s Gen 3' => ['SM8635', 'Qualcomm', null],
        'Snapdragon 8 Gen 2' => ['SM8550', 'Qualcomm', null],
        'Snapdragon 8+ Gen 1' => ['SM8475', 'Qualcomm', null],
        'Snapdragon 8 Gen 1' => ['SM8450', 'Qualcomm', null],
        'Snapdragon 888' => ['SM8350', 'Qualcomm', null],
        'Snapdragon 865' => ['SM8250', 'Qualcomm', null],
        'Snapdragon 7+ Gen 2' => ['SM7475', 'Qualcomm', null],
        'Snapdragon 7s Gen 2' => ['SM7435', 'Qualcomm', null],
        'Snapdragon 778G' => ['SM7325', 'Qualcomm', null],
        'Snapdragon 778G+' => ['SM7325-AE', 'Qualcomm', null],
        'Snapdragon 732G' => ['SM7150-AC', 'Qualcomm', null],
        'Snapdragon 680' => ['SM6225', 'Qualcomm', null],
        'Dimensity 9200' => ['MT6985', 'MediaTek', null],
        'Dimensity 9200+' => ['Dimensity 9200+', 'MediaTek', 'Parça kodu doğrulanmalı.'],
        'Dimensity 8300-Ultra' => ['MT6897', 'MediaTek', null],
        'Dimensity 8200-Ultra' => ['MT6896', 'MediaTek', null],
        'Dimensity 7200-Ultra' => ['MT6886', 'MediaTek', null],
        'Dimensity 1080' => ['MT6877V', 'MediaTek', null],
        'Helio G90T' => ['MT6785', 'MediaTek', null],
        'Helio G80' => ['MT6769T', 'MediaTek', null],
        'Exynos 2400' => ['S5E9945', 'Samsung', null],
        'Exynos 2200' => ['S5E9925', 'Samsung', null],
        'Exynos 2100' => ['S5E9840', 'Samsung', null],
        'Exynos 1380' => ['S5E8835', 'Samsung', null],
        'Exynos 1280' => ['S5E8825', 'Samsung', null],
        'Exynos 990' => ['S5E9830', 'Samsung', null],
        'Exynos 9825' => ['S5E9825', 'Samsung', null],
        'Google Tensor' => ['GS101', 'Google', null],
        'Google Tensor G2' => ['GS201', 'Google', null],
        'Google Tensor G3' => ['Tensor G3', 'Google', 'Parça kodu doğrulanmalı.'],
        'Kirin 990 5G' => ['Hi3690', 'HiSilicon', null],
        'Kirin 980' => ['Hi3680', 'HiSilicon', null],
        'A17 Pro' => ['A17 Pro', 'Apple', 'Apple parça kodu doğrulanmalı.'],
        'A16 Bionic' => ['A16 Bionic', 'Apple', 'Apple parça kodu doğrulanmalı.'],
        'A15 Bionic' => ['A15 Bionic', 'Apple', 'Apple parça kodu doğrulanmalı.'],
        'A14 Bionic' => ['A14 Bionic', 'Apple', 'Apple parça kodu doğrulanmalı.'],
        'A13 Bionic' => ['A13 Bionic', 'Apple', 'Apple parça kodu doğrulanmalı.'],
    ];

    /** Aynı paket / bacak yapısındaki varyantlar (pin uyumlu, frekans farkı) */
    public const COMPATIBLE = [
        ['SM7325', 'SM7325-AE', 'Aynı paket; 778G+ daha yüksek frekanslı. Yazılım uyumu doğrulanmalı.'],
        ['MT6985', 'Dimensity 9200+', 'Aynı aile; 9200+ daha yüksek frekanslı. Yazılım uyumu doğrulanmalı.'],
    ];

    /** CSV satırları (başlık dahil) */
    public static function csvRows(): \Generator
    {
        yield \App\Services\CrossReferenceCatalog::COLUMNS;

        $devices = collect(SampleDeviceCatalog::rows())->groupBy('chipset');
        $compat = collect(self::COMPATIBLE);

        foreach (self::SOCS as $chipset => [$code, $maker, $note]) {
            $links = $compat->filter(fn ($c) => $c[0] === $code)->map(fn ($c) => 'soc:'.$c[1].':pin')->implode('; ');

            yield [
                'soc', $code, $chipset, $maker, '',
                ($devices[$chipset] ?? collect())->map(fn ($d) => $d['brand'].' > '.$d['model_name'])->implode('; '),
                $links,
                trim(($note ?? '').' '.($compat->firstWhere(0, $code)[2] ?? '')),
            ];
        }
    }
}
