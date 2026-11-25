<?php

namespace App\Enums;

/** Board ID hattını okuyan entegre. Yalnızca PCB görselinde gösterilir ve seçilir. */
enum ChipType: string
{
    case PMIC = 'pmic';
    case SOC = 'soc';

    /** Görseldeki entegrenin üzerinde yazan ad. */
    public function label(): string
    {
        return match ($this) {
            self::PMIC => 'PMIC',
            self::SOC => 'CPU/SoC',
        };
    }
}
