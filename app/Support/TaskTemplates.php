<?php

namespace App\Support;

use App\Enums\PriorityType;
use App\Enums\Recurrence;
use App\Enums\TaskCategory;
use Illuminate\Support\Carbon;

/**
 * Teknik servis işletmesi için tek tıkla eklenebilen hazır görevler.
 */
class TaskTemplates
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            'rent' => [
                'title' => 'Dükkân kira ödemesi', 'category' => TaskCategory::PAYMENT, 'recurrence' => Recurrence::MONTHLY,
                'day' => 1, 'time' => '10:00', 'remind_before' => 1440, 'priority' => PriorityType::HIGH,
                'description' => 'Kira ödemesini yapın ve dekontu muhasebeye iletin.',
            ],
            'utilities' => [
                'title' => 'Elektrik / internet faturası', 'category' => TaskCategory::PAYMENT, 'recurrence' => Recurrence::MONTHLY,
                'day' => 15, 'time' => '10:00', 'remind_before' => 4320, 'priority' => PriorityType::NORMAL,
                'description' => 'Son ödeme tarihini kaçırmadan fatura ödemelerini yapın.',
            ],
            'vat' => [
                'title' => 'KDV beyannamesi', 'category' => TaskCategory::TAX, 'recurrence' => Recurrence::MONTHLY,
                'day' => 28, 'time' => '12:00', 'remind_before' => 4320, 'priority' => PriorityType::HIGH,
                'description' => 'Aylık KDV beyannamesi için fatura ve fişleri muhasebeciye gönderin.',
            ],
            'payroll' => [
                'title' => 'Muhtasar ve SGK bildirgesi', 'category' => TaskCategory::TAX, 'recurrence' => Recurrence::MONTHLY,
                'day' => 26, 'time' => '12:00', 'remind_before' => 4320, 'priority' => PriorityType::HIGH,
                'description' => 'Personel bordrosu, muhtasar ve SGK prim bildirgesi.',
            ],
            'invoice' => [
                'title' => 'Fatura kes', 'category' => TaskCategory::INVOICE, 'recurrence' => Recurrence::NONE,
                'offset_days' => 0, 'time' => '17:00', 'remind_before' => 60, 'priority' => PriorityType::NORMAL,
                'description' => 'Teslim edilen servis işlemleri için e-fatura / e-arşiv faturası düzenleyin.',
            ],
            'collection' => [
                'title' => 'Müşteri tahsilatı', 'category' => TaskCategory::COLLECTION, 'recurrence' => Recurrence::NONE,
                'offset_days' => 7, 'time' => '11:00', 'remind_before' => 1440, 'priority' => PriorityType::NORMAL,
                'description' => 'Vadesi gelen ödemeyi müşteriden tahsil edin.',
            ],
            'ready_calls' => [
                'title' => 'Teslime hazır cihazlar için müşterileri ara', 'category' => TaskCategory::FOLLOW_UP, 'recurrence' => Recurrence::DAILY,
                'offset_days' => 0, 'time' => '11:00', 'remind_before' => 0, 'priority' => PriorityType::NORMAL,
                'description' => '"Teslime hazır" durumundaki ve alınmamış cihazların sahiplerine ulaşın.',
            ],
            'parts' => [
                'title' => 'Yedek parça stok kontrolü ve sipariş', 'category' => TaskCategory::PURCHASE, 'recurrence' => Recurrence::WEEKLY,
                'weekday' => Carbon::MONDAY, 'time' => '10:00', 'remind_before' => 60, 'priority' => PriorityType::NORMAL,
                'description' => 'Azalan ekran, batarya ve şarj soketi stoklarını kontrol edip sipariş verin.',
            ],
            'cash' => [
                'title' => 'Haftalık kasa sayımı', 'category' => TaskCategory::GENERAL, 'recurrence' => Recurrence::WEEKLY,
                'weekday' => Carbon::FRIDAY, 'time' => '18:00', 'remind_before' => 15, 'priority' => PriorityType::NORMAL,
                'description' => 'Kasa ve POS tahsilatlarını servis kayıtlarıyla karşılaştırın.',
            ],
            'maintenance' => [
                'title' => 'Periyodik bakım hatırlatması', 'category' => TaskCategory::MAINTENANCE, 'recurrence' => Recurrence::YEARLY,
                'offset_days' => 365, 'time' => '10:00', 'remind_before' => 10080, 'priority' => PriorityType::LOW,
                'description' => 'Müşteriyi yıllık bakım için arayın ve randevu oluşturun.',
            ],
            'warranty' => [
                'title' => 'Garanti bitiş kontrolü', 'category' => TaskCategory::WARRANTY, 'recurrence' => Recurrence::NONE,
                'offset_days' => 180, 'time' => '10:00', 'remind_before' => 10080, 'priority' => PriorityType::LOW,
                'description' => 'Onarım garantisi bitmeden müşteriye memnuniyet araması yapın.',
            ],
            'insurance' => [
                'title' => 'İşyeri sigortası yenileme', 'category' => TaskCategory::PAYMENT, 'recurrence' => Recurrence::YEARLY,
                'offset_days' => 30, 'time' => '10:00', 'remind_before' => 10080, 'priority' => PriorityType::HIGH,
                'description' => 'Poliçe yenileme tekliflerini karşılaştırın.',
            ],
        ];
    }

    /** Şablonun ilk tarihi: ayın belirli günü, haftanın günü ya da bugünden itibaren gün sayısı. */
    public static function firstDueAt(array $template): Carbon
    {
        [$hour, $minute] = array_map('intval', explode(':', $template['time'] ?? '09:00'));
        $now = now();

        if (isset($template['day'])) {
            $date = $now->copy()->day(min($template['day'], $now->daysInMonth))->setTime($hour, $minute);
            if ($date->isPast()) {
                $date = $now->copy()->startOfMonth()->addMonthNoOverflow();
                $date->day(min($template['day'], $date->daysInMonth))->setTime($hour, $minute);
            }

            return $date;
        }

        if (isset($template['weekday'])) {
            $date = $now->copy()->setTime($hour, $minute);

            return $date->dayOfWeek === $template['weekday'] && $date->isFuture()
                ? $date
                : $date->next($template['weekday'])->setTime($hour, $minute);
        }

        $date = $now->copy()->addDays($template['offset_days'] ?? 0)->setTime($hour, $minute);

        return $date->isPast() ? $date->addDay() : $date;
    }
}
