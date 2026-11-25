<?php

namespace App\Enums;

/**
 * Teknik servis işletmesinde karşılaşılan görev türleri.
 */
enum TaskCategory: string
{
    case INVOICE = 'invoice';
    case COLLECTION = 'collection';
    case PAYMENT = 'payment';
    case REMINDER = 'reminder';
    case MAINTENANCE = 'maintenance';
    case FOLLOW_UP = 'follow_up';
    case WARRANTY = 'warranty';
    case PURCHASE = 'purchase';
    case TAX = 'tax';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::INVOICE => 'Fatura kesimi',
            self::COLLECTION => 'Tahsilat',
            self::PAYMENT => 'Ödeme',
            self::REMINDER => 'Hatırlatma',
            self::MAINTENANCE => 'Periyodik bakım',
            self::FOLLOW_UP => 'Müşteri takibi',
            self::WARRANTY => 'Garanti',
            self::PURCHASE => 'Parça siparişi',
            self::TAX => 'Vergi / beyanname',
            self::GENERAL => 'Genel',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::INVOICE => '🧾',
            self::COLLECTION => '💰',
            self::PAYMENT => '💳',
            self::REMINDER => '⏰',
            self::MAINTENANCE => '🛠️',
            self::FOLLOW_UP => '📞',
            self::WARRANTY => '🛡️',
            self::PURCHASE => '📦',
            self::TAX => '🏛️',
            self::GENERAL => '📌',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::INVOICE => 'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
            self::COLLECTION => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
            self::PAYMENT => 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
            self::REMINDER => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
            self::MAINTENANCE => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300',
            self::FOLLOW_UP => 'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-300',
            self::WARRANTY => 'bg-teal-100 text-teal-800 dark:bg-teal-500/15 dark:text-teal-300',
            self::PURCHASE => 'bg-orange-100 text-orange-800 dark:bg-orange-500/15 dark:text-orange-300',
            self::TAX => 'bg-slate-200 text-slate-800 dark:bg-slate-500/20 dark:text-slate-200',
            self::GENERAL => 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300',
        };
    }

    public function dot(): string
    {
        return match ($this) {
            self::INVOICE => 'bg-sky-500',
            self::COLLECTION => 'bg-emerald-500',
            self::PAYMENT => 'bg-rose-500',
            self::REMINDER => 'bg-amber-500',
            self::MAINTENANCE => 'bg-indigo-500',
            self::FOLLOW_UP => 'bg-violet-500',
            self::WARRANTY => 'bg-teal-500',
            self::PURCHASE => 'bg-orange-500',
            self::TAX => 'bg-slate-500',
            self::GENERAL => 'bg-gray-400',
        };
    }

    /** Tutar alanı anlamlı olan türler. */
    public function hasAmount(): bool
    {
        return in_array($this, [self::INVOICE, self::COLLECTION, self::PAYMENT, self::TAX, self::PURCHASE], true);
    }

    /** Para girişi (+) mi çıkışı (−) mı? */
    public function isIncome(): bool
    {
        return in_array($this, [self::INVOICE, self::COLLECTION], true);
    }
}
