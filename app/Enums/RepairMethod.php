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
    case TMP_BL_UNLOCK = 'tmp_bl_unlock';
    case TMP_ROOT = 'tmp_root';
    case EDL_AUTH_BYPASS = 'edl_auth_bypass';
    case BROM_BYPASS = 'brom_bypass';
    case QC_DIAG = 'qc_diag';

    public function label(): string
    {
        return match ($this) {
            self::META => 'Meta',
            self::FLASH => 'Flash',
            self::PATCH => 'Patch',
            self::UNLOCKED => 'Kilidi açık',
            self::LOCKED => 'Kilitli',
            self::RESISTOR => 'Direnç değişimi',
            self::TMP_BL_UNLOCK => 'Geçici BL unlock',
            self::TMP_ROOT => 'Geçici root',
            self::EDL_AUTH_BYPASS => 'EDL auth bypass',
            self::BROM_BYPASS => 'BROM bypass',
            self::QC_DIAG => 'QC diag modu',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::META => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::FLASH => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::PATCH => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::UNLOCKED => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            self::LOCKED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::RESISTOR => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
            self::TMP_BL_UNLOCK => 'bg-fuchsia-100 text-fuchsia-800 dark:bg-fuchsia-900 dark:text-fuchsia-300',
            self::TMP_ROOT => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            self::EDL_AUTH_BYPASS => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300',
            self::BROM_BYPASS => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
            self::QC_DIAG => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
        };
    }
}
