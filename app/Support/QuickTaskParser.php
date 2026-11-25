<?php

namespace App\Support;

use App\Enums\Recurrence;
use App\Enums\TaskCategory;
use Illuminate\Support\Carbon;

/**
 * Hızlı ekleme satırını görev alanlarına çevirir.
 * Örn: "Kira öde her ay ayın 1'i 10:00 15000 tl" →
 *      başlık "Kira öde", aylık, ayın 1'i 10:00, ₺15.000, tür: ödeme
 */
class QuickTaskParser
{
    private const WEEKDAYS = [
        'pazartesi' => Carbon::MONDAY, 'salı' => Carbon::TUESDAY, 'çarşamba' => Carbon::WEDNESDAY,
        'perşembe' => Carbon::THURSDAY, 'cuma' => Carbon::FRIDAY, 'cumartesi' => Carbon::SATURDAY, 'pazar' => Carbon::SUNDAY,
    ];

    private const CATEGORY_WORDS = [
        'fatura kes' => TaskCategory::INVOICE, 'fatura' => TaskCategory::INVOICE,
        'tahsil' => TaskCategory::COLLECTION, 'alacak' => TaskCategory::COLLECTION,
        'kira' => TaskCategory::PAYMENT, 'öde' => TaskCategory::PAYMENT, 'ödeme' => TaskCategory::PAYMENT,
        'kdv' => TaskCategory::TAX, 'vergi' => TaskCategory::TAX, 'beyanname' => TaskCategory::TAX, 'sgk' => TaskCategory::TAX,
        'sipariş' => TaskCategory::PURCHASE, 'parça' => TaskCategory::PURCHASE,
        'bakım' => TaskCategory::MAINTENANCE, 'garanti' => TaskCategory::WARRANTY,
        'ara' => TaskCategory::FOLLOW_UP, 'arama' => TaskCategory::FOLLOW_UP,
        'hatırlat' => TaskCategory::REMINDER,
    ];

    /** @return array{title: string, due_at: ?Carbon, recurrence: Recurrence, amount: ?float, category: TaskCategory, has_time: bool} */
    public static function parse(string $input): array
    {
        $text = ' '.trim(preg_replace('/\s+/u', ' ', $input)).' ';
        $lower = mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], $text));
        $remove = [];
        $date = null;
        $time = null;
        $recurrence = Recurrence::NONE;
        $amount = null;

        $take = function (string $pattern, callable $handler) use (&$lower, &$remove, $text) {
            if (preg_match($pattern.'u', $lower, $m, PREG_OFFSET_CAPTURE)) {
                $handler($m);
                $remove[] = trim($m[0][0]);
            }
        };

        // Tekrar
        $take('/\s(her\s(gün|hafta|ay|yıl))(?=\s)/', function ($m) use (&$recurrence) {
            $recurrence = match ($m[2][0]) {
                'gün' => Recurrence::DAILY, 'hafta' => Recurrence::WEEKLY, 'ay' => Recurrence::MONTHLY, default => Recurrence::YEARLY,
            };
        });

        // Tutar: 1500 tl, 1.500,50 ₺, ₺750
        $take('/\s(?:₺\s?([\d.]+(?:,\d{1,2})?)|([\d.]+(?:,\d{1,2})?)\s?(?:tl|₺|lira))(?=\s)/', function ($m) use (&$amount) {
            $raw = $m[1][0] !== '' ? $m[1][0] : $m[2][0];
            $amount = (float) str_replace(['.', ','], ['', '.'], $raw);
        });

        // Saat: 14:30 / saat 14
        $take('/\s(?:saat\s)?(\d{1,2})[:.](\d{2})(?=\s)/', function ($m) use (&$time) {
            $time = [(int) $m[1][0], (int) $m[2][0]];
        });
        if (! $time) {
            $take('/\ssaat\s(\d{1,2})(?=\s)/', function ($m) use (&$time) {
                $time = [(int) $m[1][0], 0];
            });
        }

        // Tarih
        $take('/\s(bugün|yarın|haftaya)(?=\s)/', function ($m) use (&$date) {
            $date = match ($m[1][0]) {
                'bugün' => now(), 'yarın' => now()->addDay(), default => now()->addWeek(),
            };
        });
        if (! $date) {
            $take('/\sayın\s(\d{1,2})(?:\'?(?:i|ı|u|ü|si|sı|su|sü|nci|ncı|ncu|ncü))?(?=\s)/', function ($m) use (&$date) {
                $day = max(1, min(31, (int) $m[1][0]));
                $candidate = now()->day(min($day, now()->daysInMonth));
                if ($candidate->lt(now()->startOfDay())) {
                    $candidate = now()->startOfMonth()->addMonthNoOverflow();
                    $candidate->day(min($day, $candidate->daysInMonth));
                }
                $date = $candidate;
            });
        }
        if (! $date) {
            $take('/\s('.implode('|', array_keys(self::WEEKDAYS)).')(?:\sgünü)?(?=\s)/', function ($m) use (&$date) {
                $date = now()->next(self::WEEKDAYS[$m[1][0]]);
            });
        }

        // Başlıktan tanınan ifadeleri çıkar
        $title = $text;
        foreach ($remove as $phrase) {
            $title = preg_replace('/'.preg_quote($phrase, '/').'/iu', ' ', $title, 1);
        }
        $title = trim(preg_replace('/\s+/u', ' ', $title));

        $category = TaskCategory::GENERAL;
        foreach (self::CATEGORY_WORDS as $word => $cat) {
            if (preg_match('/(^|\s)'.preg_quote($word, '/').'/u', $lower)) {
                $category = $cat;
                break;
            }
        }

        if ($recurrence !== Recurrence::NONE && ! $date) {
            $date = now();
        }
        if ($time && ! $date) {
            $date = now();
        }

        $dueAt = $date?->copy()->setTime($time[0] ?? 9, $time[1] ?? 0);
        // Bugün için saat geçmişse ve saat belirtilmediyse gün sonuna al
        if ($dueAt && ! $time && $dueAt->isToday() && $dueAt->isPast()) {
            $dueAt->setTime(18, 0);
        }

        return [
            'title' => $title !== '' ? mb_strtoupper(mb_substr($title, 0, 1)).mb_substr($title, 1) : trim($input),
            'due_at' => $dueAt,
            'recurrence' => $recurrence,
            'amount' => $amount,
            'category' => $category,
            'has_time' => $time !== null,
        ];
    }
}
