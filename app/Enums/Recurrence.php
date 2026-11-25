<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

enum Recurrence: string
{
    case NONE = 'none';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'Tekrarlanmaz',
            self::DAILY => 'Günlük',
            self::WEEKLY => 'Haftalık',
            self::MONTHLY => 'Aylık',
            self::YEARLY => 'Yıllık',
        };
    }

    /** "Her gün", "Her 2 haftada bir", "Her ay" … */
    public function describe(int $interval = 1): string
    {
        $unit = match ($this) {
            self::NONE => null,
            self::DAILY => ['gün', 'günde'],
            self::WEEKLY => ['hafta', 'haftada'],
            self::MONTHLY => ['ay', 'ayda'],
            self::YEARLY => ['yıl', 'yılda'],
        };

        if (! $unit) {
            return 'Tekrarlanmaz';
        }

        return $interval <= 1 ? 'Her '.$unit[0] : "Her {$interval} {$unit[1]} bir";
    }

    /**
     * Sonraki tarih. Ay/yıl sonu taşmaz: 31 Ocak + 1 ay = 28/29 Şubat.
     */
    public function next(Carbon $from, int $interval = 1, ?int $anchorDay = null): ?Carbon
    {
        $interval = max(1, $interval);
        $date = $from->copy();

        $next = match ($this) {
            self::NONE => null,
            self::DAILY => $date->addDays($interval),
            self::WEEKLY => $date->addWeeks($interval),
            self::MONTHLY => $date->addMonthsNoOverflow($interval),
            self::YEARLY => $date->addYearsNoOverflow($interval),
        };

        // Ayın 31'i gibi günler kısa aylardan sonra kaymasın: 31 Oca → 28 Şub → 31 Mar
        if ($next && $anchorDay && in_array($this, [self::MONTHLY, self::YEARLY], true)) {
            $next->day(min($anchorDay, $next->daysInMonth));
        }

        return $next;
    }
}
