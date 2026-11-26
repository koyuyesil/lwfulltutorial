<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserSetting;

/**
 * Firma (kullanıcı) bazlı ayarlar. Tanımlı olmayan anahtar varsayılan değerini döndürür.
 */
class Settings
{
    public const DEFAULTS = [
        // Genel
        'company.name' => null,
        'company.phone' => null,
        'company.address' => null,

        // Servis kayıtları: öncelik → hedef teslim süresi (saat)
        'tickets.sla_hours' => ['high' => 24, 'normal' => 72, 'low' => 120],
        'tickets.technicians' => [],

        // Müşteri bildirim şablonları (durum → metin); boşsa NotificationTemplates varsayılanı
        'notify.templates' => [],

        // Müşteriler
        'clients.vcard_version' => '3.0',
        'clients.import_strategy' => 'skip',

        // Görevler
        'tasks.default_time' => '09:00',
        'tasks.default_remind' => '0',

        // Cihaz modelleri
        'products.default_view' => 'cards',
        'products.import_strategy' => 'skip', // skip | update
    ];

    /** İstek kapsamlı önbellek (her istek / test için yeni; statik durum sızmaz). */
    protected static function cache(): \ArrayObject
    {
        if (! app()->bound('settings.cache')) {
            app()->scoped('settings.cache', fn () => new \ArrayObject);
        }

        return app('settings.cache');
    }

    public static function get(?User $user, string $key, mixed $default = null): mixed
    {
        $fallback = $default ?? (self::DEFAULTS[$key] ?? null);

        if (! $user) {
            return $fallback;
        }

        $cache = self::cache();
        if (! isset($cache[$user->id])) {
            $cache[$user->id] = UserSetting::where('user_id', $user->id)->pluck('value', 'key')->all();
        }
        $all = $cache[$user->id];
        $value = $all[$key] ?? null;

        // Dizi ayarlarda eksik alt anahtarlar varsayılanla tamamlanır
        if (is_array($fallback) && is_array($value)) {
            return array_replace($fallback, $value);
        }

        return $value ?? $fallback;
    }

    public static function set(User $user, string $key, mixed $value): void
    {
        UserSetting::updateOrCreate(['user_id' => $user->id, 'key' => $key], ['value' => $value]);
        $cache = self::cache();
        unset($cache[$user->id]);
    }

    public static function flush(): void
    {
        self::cache()->exchangeArray([]);
    }
}
