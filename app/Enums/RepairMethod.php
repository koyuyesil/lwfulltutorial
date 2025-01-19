<?php

namespace App\Enums;

enum RepairMethod: string
{
    case META = 'meta';
    case FLASH = 'flash';
    case PATCH = 'patch';
    case UNLOCKED = 'unlocked';
    case LOCKED = 'locked';
    case RESISTOR = 'resistor';
    public function color(): string
    {
        return match ($this) {
            self::META => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::FLASH => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::PATCH => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::UNLOCKED => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            self::LOCKED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::RESISTOR => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }
}
