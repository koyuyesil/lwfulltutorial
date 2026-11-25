<?php

namespace App\Enums;

enum PriorityType: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Düşük',
            self::NORMAL => 'Normal',
            self::HIGH => 'Acil',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::LOW => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::NORMAL => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200',
            self::HIGH => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200',
        };
    }
}
