<?php

namespace App\Support;

/**
 * Yanlış kodlamayla kaydedilmiş metinlerdeki "�" (U+FFFD) karakterlerini, servis notlarında sık geçen
 * Türkçe kelimelerin sözlüğüyle onarır. Sözlükte olmayan kelimeler olduğu gibi bırakılır.
 */
class TurkishTextRepair
{
    /** Bozuk yazım → doğru yazım (küçük harf karşılaştırma; büyük harfli kelimeler ayrıca eşleşir) */
    protected const WORDS = [
        'ç�kebilir' => 'çıkebilir', '��kebilir' => 'çıkebilir',
        'al�nabilir' => 'alınabilir', 'i�in' => 'için', 'tak�p' => 'takıp', 'tak�labilir' => 'takılabilir',
        'kapal�' => 'kapalı', 'ge�ici' => 'geçici', 'y�kle' => 'yükle', 'y�klenir' => 'yüklenir', 'y�kleyin' => 'yükleyin',
        '�in' => 'Çin', 'l�stede' => 'LİSTEDE', 'd�nerken' => 'dönerken', 'dosyalar�n�n' => 'dosyalarının',
        'kald�r�n�z' => 'kaldırınız', 'kald�r�ld�' => 'kaldırıldı', 'kullan�lmayan' => 'kullanılmayan',
        'olmad�' => 'olmadı', 'farkl�' => 'farklı', '�ebeke' => 'şebeke', 's�ler' => 'SİLER', 'd�kkkat' => 'DİKKKAT',
        'd�kkat' => 'DİKKAT', 'de�ilse' => 'değilse', '�art' => 'şart', 'se�iyor' => 'seçiyor', '�izgili' => 'çizgili',
        's�kmeden' => 'sökmeden', 's�k' => 'sök', 'yap�l�r' => 'yapılır', 'tak�l�' => 'takılı', 'okunan' => 'okunan',
        'parmakizi' => 'parmakizi', 'anakart�' => 'anakartı', 'direnc�' => 'direnci', 'g�ster' => 'göster',
        '��z�m' => 'çözüm', 'bo�' => 'boş', 'y�ksek' => 'yüksek', 'd���k' => 'düşük', 'a��k' => 'açık',
    ];

    public static function fix(string $text): string
    {
        if (! str_contains($text, "\u{FFFD}")) {
            return $text;
        }

        return preg_replace_callback('/[\p{L}\x{FFFD}_]+/u', function ($m) {
            $word = $m[0];
            if (! str_contains($word, "\u{FFFD}")) {
                return $word;
            }
            $lower = mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], $word));
            $fixed = self::WORDS[$lower] ?? null;
            if ($fixed === null) {
                return $word;
            }

            // Kaynak tamamen büyük harfse büyük harf koru
            $isUpper = $word === mb_strtoupper($word) && preg_match('/\p{Lu}/u', $word);

            return $isUpper ? \App\Models\Client::trUpper($fixed) : $fixed;
        }, $text);
    }

    public static function hasBroken(string $text): bool
    {
        return str_contains($text, "\u{FFFD}");
    }
}
