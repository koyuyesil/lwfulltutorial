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
        if ($key === 'first_name' || $key === 'last_name') {
            $value = mb_convert_case($value, MB_CASE_TITLE, "UTF-8"); // İlk harf büyük, geri kalanı küçük.
        } elseif ($key === 'address' || $key === 'company') {
            $value = mb_strtoupper($value, "UTF-8"); // Tamamını büyük harfe çevir.
        }

        parent::setAttribute($key, $value);
    }

    public function clientProducts()
    {
        return $this->hasMany(ClientProduct::class);
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
            $strategy = config('client_product.deletion_strategy', 'delete');//todo config

            switch ($strategy) {
                case 'delete':
                    // Cihazları tamamen sil
                    $client->clientProducts()->delete();
                    break;

                case 'reassign':
                    // Cihazları başka bir kullanıcıya ata
                    $defaultUserId = config('client_product.default_user_id');
                    $client->clientProducts()->update(['user_id' => $defaultUserId]);
                    break;

                case 'archive':
                    // Cihazları arşiv tablosuna taşı
                    foreach ($client->clientProducts as $device) {
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
