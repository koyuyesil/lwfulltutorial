<?php

namespace App\Enums;

/** Çapraz referans bileşen türleri. */
enum ComponentType: string
{
    case SOC = 'soc';
    case RAM = 'ram';
    case STORAGE = 'storage';
    case MCP = 'mcp';
    case PMIC = 'pmic';
    case CHARGING = 'charging';
    case RF = 'rf';
    case DISPLAY = 'display';
    case BATTERY = 'battery';
    case CAMERA = 'camera';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SOC => 'CPU / SoC',
            self::RAM => 'RAM',
            self::STORAGE => 'Depolama (eMMC / UFS)',
            self::MCP => 'eMCP / uMCP',
            self::PMIC => 'PMIC',
            self::CHARGING => 'Şarj entegresi',
            self::RF => 'RF / modem',
            self::DISPLAY => 'Ekran',
            self::BATTERY => 'Batarya',
            self::CAMERA => 'Kamera',
            self::OTHER => 'Diğer',
        };
    }

    public function short(): string
    {
        return match ($this) {
            self::SOC => 'SoC', self::RAM => 'RAM', self::STORAGE => 'UFS', self::MCP => 'MCP', self::PMIC => 'PMIC',
            self::CHARGING => 'ŞARJ', self::RF => 'RF', self::DISPLAY => 'EKRAN', self::BATTERY => 'BAT', self::CAMERA => 'KAM', self::OTHER => 'DİĞER',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::SOC => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
            self::RAM => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
            self::STORAGE => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300',
            self::MCP => 'bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300',
            self::PMIC => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::CHARGING => 'bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
            self::RF => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
            self::DISPLAY => 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/15 dark:text-fuchsia-300',
            self::BATTERY => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::CAMERA => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
            self::OTHER => 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-300',
        };
    }

    /** Türe göre önerilen teknik özellik alanları (formda hazır gelir) */
    public function specKeys(): array
    {
        return match ($this) {
            self::SOC => ['Üretim süreci', 'Çekirdek', 'GPU'],
            self::RAM => ['Bellek tipi', 'Kapasite', 'Paket'],
            self::STORAGE => ['Arayüz', 'Kapasite', 'Paket'],
            self::MCP => ['RAM', 'Depolama', 'Paket'],
            self::PMIC => ['Görev'],
            self::DISPLAY => ['Panel', 'Boyut', 'Konnektör'],
            self::BATTERY => ['Kapasite', 'Model'],
            default => [],
        };
    }
}
