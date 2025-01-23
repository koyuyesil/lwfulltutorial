<?php

if (!function_exists('convertToSMDCode')) {
    /**
     * Direnci 3 basamaklı SMD koduna çevirir.
     *
     * @param float $resistance Direnç değeri
     * @return string SMD kodu
     */
    function convertToSMDCode($resistance)
    {
        // Sayısal bir değer elde et
        $resistance = floatval($resistance);

        // 0 ve 0'dan küçük değerler için 0 döndür
        if ($resistance <= 0) {
            return '0';
        }

        // 10'dan küçük değerler için 'R' formatı
        if ($resistance < 10 && $resistance > 0) {
            $resistanceString = str_replace('.', 'R', number_format($resistance, 1));
            return $resistanceString;
        }

        // 10 ve üzeri değerler için SMD formatı
        $exp = floor(log10($resistance)); // Kaç basamak olduğunu bul
        $multiplier = $exp - 1;           // Çarpanı belirle
        $base = intval($resistance / pow(10, $multiplier)); // İlk iki rakamı al

        return $base . $multiplier; // SMD kodunu döndür
    }
}
