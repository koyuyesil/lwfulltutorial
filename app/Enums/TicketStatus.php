<?php

namespace App\Enums;

/**
 * Satış sonrası servis iş akışı. Her durum yalnızca izin verilen
 * sonraki durumlara geçebilir; böylece süreç atlanamaz ve denetlenebilir.
 */
enum TicketStatus: string
{
    case RECEIVED = 'received';
    case DIAGNOSING = 'diagnosing';
    case AWAITING_APPROVAL = 'awaiting_approval';
    case AWAITING_PARTS = 'awaiting_parts';
    case IN_REPAIR = 'in_repair';
    case TESTING = 'testing';
    case READY = 'ready';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Kabul edildi',
            self::DIAGNOSING => 'Teşhis',
            self::AWAITING_APPROVAL => 'Onay bekliyor',
            self::AWAITING_PARTS => 'Parça bekliyor',
            self::IN_REPAIR => 'Onarımda',
            self::TESTING => 'Test',
            self::READY => 'Teslime hazır',
            self::DELIVERED => 'Teslim edildi',
            self::CANCELLED => 'İptal / İade',
        };
    }

    /** Rozet renkleri (açık + koyu tema). */
    public function badge(): string
    {
        return match ($this) {
            self::RECEIVED => 'bg-slate-100 text-slate-700 ring-slate-300 dark:bg-slate-700/50 dark:text-slate-200 dark:ring-slate-500',
            self::DIAGNOSING => 'bg-sky-100 text-sky-700 ring-sky-300 dark:bg-sky-900/40 dark:text-sky-200 dark:ring-sky-700',
            self::AWAITING_APPROVAL => 'bg-amber-100 text-amber-800 ring-amber-300 dark:bg-amber-900/40 dark:text-amber-200 dark:ring-amber-700',
            self::AWAITING_PARTS => 'bg-orange-100 text-orange-800 ring-orange-300 dark:bg-orange-900/40 dark:text-orange-200 dark:ring-orange-700',
            self::IN_REPAIR => 'bg-indigo-100 text-indigo-700 ring-indigo-300 dark:bg-indigo-900/40 dark:text-indigo-200 dark:ring-indigo-700',
            self::TESTING => 'bg-violet-100 text-violet-700 ring-violet-300 dark:bg-violet-900/40 dark:text-violet-200 dark:ring-violet-700',
            self::READY => 'bg-emerald-100 text-emerald-700 ring-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-200 dark:ring-emerald-700',
            self::DELIVERED => 'bg-green-600 text-white ring-green-700',
            self::CANCELLED => 'bg-rose-100 text-rose-700 ring-rose-300 dark:bg-rose-900/40 dark:text-rose-200 dark:ring-rose-700',
        };
    }

    /** Özet sayaçlarında kullanılan kenarlık rengi. */
    public function color(): string
    {
        return match ($this) {
            self::RECEIVED => 'border-slate-400',
            self::DIAGNOSING => 'border-sky-500',
            self::AWAITING_APPROVAL => 'border-amber-500',
            self::AWAITING_PARTS => 'border-orange-500',
            self::IN_REPAIR => 'border-indigo-500',
            self::TESTING => 'border-violet-500',
            self::READY => 'border-emerald-500',
            self::DELIVERED => 'border-green-600',
            self::CANCELLED => 'border-rose-500',
        };
    }

    /** Grafik çubuğu dolgu rengi. */
    public function bar(): string
    {
        return match ($this) {
            self::RECEIVED => 'bg-slate-400',
            self::DIAGNOSING => 'bg-sky-500',
            self::AWAITING_APPROVAL => 'bg-amber-500',
            self::AWAITING_PARTS => 'bg-orange-500',
            self::IN_REPAIR => 'bg-indigo-500',
            self::TESTING => 'bg-violet-500',
            self::READY => 'bg-emerald-500',
            self::DELIVERED => 'bg-green-600',
            self::CANCELLED => 'bg-rose-500',
        };
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::RECEIVED => [self::DIAGNOSING, self::CANCELLED],
            self::DIAGNOSING => [self::AWAITING_APPROVAL, self::AWAITING_PARTS, self::IN_REPAIR, self::CANCELLED],
            self::AWAITING_APPROVAL => [self::AWAITING_PARTS, self::IN_REPAIR, self::CANCELLED],
            self::AWAITING_PARTS => [self::IN_REPAIR, self::CANCELLED],
            self::IN_REPAIR => [self::AWAITING_PARTS, self::TESTING],
            self::TESTING => [self::IN_REPAIR, self::READY],
            self::READY => [self::DELIVERED, self::IN_REPAIR],
            self::DELIVERED => [],
            self::CANCELLED => [self::RECEIVED],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    /** Teslim edilen ya da iptal edilen işler kapanmış sayılır. */
    public function isClosed(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED], true);
    }

    /** Müşteri tarafında beklenen işler: SLA sayacı bu durumlarda durmaz ama raporda ayrıştırılır. */
    public function isWaitingOnCustomer(): bool
    {
        return $this === self::AWAITING_APPROVAL;
    }

    /** @return list<string> */
    public static function openValues(): array
    {
        return array_values(array_map(
            fn (self $s) => $s->value,
            array_filter(self::cases(), fn (self $s) => ! $s->isClosed()),
        ));
    }
}
