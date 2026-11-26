<?php

namespace App\Enums;

/** Bileşen ↔ bileşen uyumluluk türü. */
enum CompatibilityKind: string
{
    case DIRECT = 'direct';
    case PIN = 'pin';
    case SOFTWARE = 'software';

    public function label(): string
    {
        return match ($this) {
            self::DIRECT => 'Birebir',
            self::PIN => 'Pin uyumlu',
            self::SOFTWARE => 'Yazılım / ayar gerekir',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DIRECT => 'Doğrudan yerine takılır, ek işlem gerekmez.',
            self::PIN => 'Aynı paket ve bacak yapısı; değer / frekans farkı olabilir.',
            self::SOFTWARE => 'Takıldıktan sonra yazılım, eşleştirme ya da kalibrasyon gerekir.',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::DIRECT => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::PIN => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::SOFTWARE => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
        };
    }
}
