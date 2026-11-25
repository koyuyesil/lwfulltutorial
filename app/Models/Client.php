<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;
    protected $fillable = [
        'first_name',
        'last_name',
        'company',
        'phone',
        'email',
        'address',
    ];

    // Multi attiribute mutator
    public function setAttribute($key, $value)
    {
        if (! is_string($value)) {
            // null (ör. şirketsiz müşteri) mb_* fonksiyonlarına gönderilmez
        } elseif ($key === 'first_name' || $key === 'last_name') {
            $value = self::trTitle(trim($value)); // İlk harf büyük, geri kalanı küçük.
        } elseif ($key === 'address' || $key === 'company') {
            $value = trim($value) === '' ? null : self::trUpper(trim($value)); // Tamamını büyük harfe çevir.
        } elseif ($key === 'email') {
            $value = trim($value) === '' ? null : mb_strtolower(trim($value));
        } elseif ($key === 'phone') {
            $value = trim(preg_replace('/\s+/', ' ', $value));
        }

        parent::setAttribute($key, $value);
    }

    /** Türkçe büyük harf (i → İ, ı → I). */
    public static function trUpper(string $value): string
    {
        return mb_strtoupper(str_replace(['i', 'ı'], ['İ', 'I'], $value), 'UTF-8');
    }

    /** Türkçe baş harf büyütme: "ilker ışık" → "İlker Işık". */
    public static function trTitle(string $value): string
    {
        // Eski mb_convert_case çıktısındaki birleşik nokta (i̇ = i + U+0307) temizlenir
        $value = str_replace("\u{0307}", '', $value);

        return collect(preg_split('/(\s+)/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE))
            ->map(function ($word) {
                if (trim($word) === '') {
                    return $word;
                }
                $lower = mb_strtolower(str_replace(['I', 'İ'], ['ı', 'i'], $word), 'UTF-8');

                return self::trUpper(mb_substr($lower, 0, 1)).mb_substr($lower, 1);
            })->implode('');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getInitialsAttribute(): string
    {
        return mb_substr((string) $this->first_name, 0, 1).mb_substr((string) $this->last_name, 0, 1);
    }

    /** Uluslararası formatta (90…) WhatsApp numarası; geçersizse null. */
    public function whatsappNumber(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->phone);

        return match (true) {
            strlen($digits) === 10 && str_starts_with($digits, '5') => '90'.$digits,
            strlen($digits) === 11 && str_starts_with($digits, '0') => '9'.$digits,
            strlen($digits) >= 11 => $digits,
            default => null,
        };
    }

    /** Telefon araması için biçimlendirme karakterlerini atan SQL ifadesi. */
    public static function phoneDigitsSql(string $column = 'phone'): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($column, ' ', ''), '(', ''), ')', ''), '-', ''), '+', '')";
    }

    public function clientsProducts()
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function tickets()
    {
        return $this->hasManyThrough(Ticket::class, ClientProduct::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Tablo işlemi yapıldığında çağrılır
    protected static function booted()
    {
        //Silme işlemi yapıldığında çağrılır
        static::deleting(function ($client) {
            // Config dosyasından stratejiyi al, yoksa 'delete' varsayılanını kullan
            $strategy = config('client_product.deletion_strategy', 'delete');//TODO config

            switch ($strategy) {
                case 'delete':
                    // Cihazları tamamen sil
                    $client->clientsProducts()->delete();
                    break;

                case 'reassign':
                    // Cihazları başka bir kullanıcıya ata
                    $defaultUserId = config('client_product.default_user_id');
                    $client->clientsProducts()->update(['user_id' => $defaultUserId]);
                    break;

                case 'archive':
                    // Cihazları arşiv tablosuna taşı
                    foreach ($client->clientsProducts as $device) {
                        DB::table('archived_customer_devices')->insert($device->toArray());
                        $device->delete(); // Orijinal cihazı sil
                    }
                    break;

                default:
                    throw new \Exception("Unknown deletion strategy: $strategy");
            }
        });
    }

}
