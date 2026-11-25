<?php

namespace App\Support;

/**
 * SI önekli değerler: "4k7" → 4700, 4700 → "4,7 kΩ", SMD kodu.
 * resources/js/calculators.js içindeki parseSI / si / smdCode ile aynı kuralları uygular.
 */
class Si
{
    protected const PREFIX = ['p' => 1e-12, 'n' => 1e-9, 'u' => 1e-6, 'µ' => 1e-6, 'μ' => 1e-6, 'm' => 1e-3, 'R' => 1, 'r' => 1, 'k' => 1e3, 'K' => 1e3, 'M' => 1e6, 'G' => 1e9];

    public static function parse(mixed $input): ?float
    {
        if ($input === null) {
            return null;
        }
        if (is_int($input) || is_float($input)) {
            return (float) $input;
        }

        $s = str_replace(',', '.', preg_replace('/\s+/u', '', (string) $input));
        if ($s === '') {
            return null;
        }

        if (preg_match('/^(\d+)([RrpnuµμmkKMG])(\d+)[a-zA-ZΩ]*$/u', $s, $m)) {
            return (float) ($m[1].'.'.$m[3]) * self::PREFIX[$m[2]];
        }
        if (preg_match('/^([+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?)([pnuµμmkKMG]?)(?:Ω|ohm|[a-zA-Z]{0,3})?$/u', $s, $m)) {
            return (float) $m[1] * ($m[2] !== '' ? self::PREFIX[$m[2]] : 1);
        }

        return null;
    }

    public static function format(?float $value, string $unit = 'Ω', int $digits = 3): string
    {
        if ($value === null) {
            return '—';
        }
        if ($value == 0) {
            return '0 '.$unit;
        }

        foreach ([[1e9, 'G'], [1e6, 'M'], [1e3, 'k'], [1, ''], [1e-3, 'm'], [1e-6, 'µ'], [1e-9, 'n']] as [$factor, $prefix]) {
            if (abs($value) >= $factor * 0.9995) {
                $n = rtrim(rtrim(number_format($value / $factor, $digits, ',', ''), '0'), ',');

                return $n.' '.$prefix.$unit;
            }
        }

        return $value.' '.$unit;
    }

    /** SMD direnç kodu: 4,7 → 4R7, 10 k → 103, 10,2 k → 1022 */
    public static function smdCode(?float $ohms): ?string
    {
        if ($ohms === null || $ohms < 0) {
            return null;
        }
        if ($ohms == 0) {
            return '000';
        }
        if ($ohms < 10) {
            $s = rtrim(rtrim(number_format($ohms, 2, '.', ''), '0'), '.');
            $s = str_contains($s, '.') ? $s : $s.'.0';

            return str_replace('.', 'R', str_starts_with($s, '0.') ? substr($s, 1) : $s);
        }

        $exp3 = (int) floor(log10($ohms)) - 1;
        $sig3 = (int) round($ohms / 10 ** $exp3);
        if ($sig3 < 100 && abs($sig3 * 10 ** $exp3 - $ohms) < $ohms * 1e-6) {
            return sprintf('%02d%d', $sig3, $exp3);
        }

        $exp4 = (int) floor(log10($ohms)) - 2;
        $sig4 = (int) round($ohms / 10 ** $exp4);

        return $exp4 >= 0 ? sprintf('%03d%d', $sig4, $exp4) : number_format($ohms, 1, 'R', '');
    }
}
