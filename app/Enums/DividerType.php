<?php

namespace App\Enums;

/**
 * Board ID devresinin tipi. Okuyan entegre (PMIC / CPU-SoC) ayrı tutulur (ChipType) ve yalnızca görselde seçilir.
 *
 * - ADC: R1 (VREF'e) ve R2 (GND'ye) kartta; orta nokta bir ADC girişine gider, gerilim kart kimliğini belirler.
 * - LOGIC: tek direnç hattı 1 (pull-up) ya da 0 (pull-down) seviyesine bağlar; açık/kapalı bilgi biti.
 * - PULLUP: R1 entegrenin içindeki dahili pull-up'tır; kartta yalnızca R2 bulunur.
 */
enum DividerType: string
{
    case ADC = 'adc';
    case LOGIC = 'logic';
    case PULLUP = 'pullup';

    public function label(): string
    {
        return match ($this) {
            self::ADC => 'ADC gerilim bölücü',
            self::LOGIC => 'Lojik açık / kapalı',
            self::PULLUP => 'Dahili pull-up',
        };
    }

    public function short(): string
    {
        return match ($this) {
            self::ADC => 'ADC',
            self::LOGIC => 'Lojik',
            self::PULLUP => 'Pull-up',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADC => 'R1 ve R2 kartta. Orta noktadaki gerilim ADC ile okunur ve kart kimliğini belirler.',
            self::LOGIC => 'Tek direnç hattı 1 (R1 → VREF) ya da 0 (R2 → GND) yapar. Takılı olmayan direnç boş bırakılır.',
            self::PULLUP => 'R1 entegrenin içindeki dahili pull-up dirençtir. Kartta yalnızca R2 (GND\'ye) bulunur.',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ADC => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::LOGIC => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::PULLUP => 'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-300',
        };
    }

    /** Tip seçildiğinde formda hazır gelen değerler. */
    public function defaults(): array
    {
        return match ($this) {
            self::ADC, self::PULLUP => ['r1' => '100k', 'r2' => '10k'],
            self::LOGIC => ['r1' => '10k', 'r2' => ''],
        };
    }

    /** R1 entegrenin içinde mi? */
    public function internalR1(): bool
    {
        return $this === self::PULLUP;
    }

    /** Entegrenin okuyan pini. */
    public function pin(): string
    {
        return $this === self::LOGIC ? 'GPIO' : 'ADC_ID';
    }
}
