<?php

namespace App\Enums;

enum DeviceType: string
{
    case PHONE = 'phone';
    case TABLET = 'tablet';
    case LAPTOP = 'laptop';
    case WATCH = 'watch';
    case CONSOLE = 'console';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PHONE => 'Telefon',
            self::TABLET => 'Tablet',
            self::LAPTOP => 'Dizüstü',
            self::WATCH => 'Akıllı saat',
            self::CONSOLE => 'Oyun konsolu',
            self::OTHER => 'Diğer',
        };
    }

    /** CSV'de Türkçe ya da İngilizce yazılmış türü tanır. */
    public static function fromLoose(?string $value): self
    {
        $v = mb_strtolower(trim((string) $value));

        return self::tryFrom($v) ?? match ($v) {
            'telefon', 'akıllı telefon', 'smartphone', 'mobile' => self::PHONE,
            'tablet', 'pad' => self::TABLET,
            'dizüstü', 'notebook', 'laptop', 'bilgisayar' => self::LAPTOP,
            'saat', 'akıllı saat', 'smartwatch' => self::WATCH,
            'konsol', 'oyun konsolu' => self::CONSOLE,
            default => $v === '' ? self::PHONE : self::OTHER,
        };
    }
}
