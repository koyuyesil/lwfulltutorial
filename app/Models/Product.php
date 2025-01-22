<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    protected $fillable = ['manufacturer', 'brand', 'model_name', 'model_number', 'description'];

    // Single mutator. (Methot ismi rastgele değildir.)

    public function setManufacturerAttribute($value)
    {
        $this->attributes['manufacturer'] = Str::title($value);
    }

    // TODO Multi attiribute mutator
    // public function setAttribute($key, $value)
    // {
    //     if ($key === 'manufacturer' || $key === 'brand') {
    //         $value = mb_convert_case($value, MB_CASE_TITLE, "UTF-8"); // İlk harf büyük, geri kalanı küçük.
    //     } elseif ($key === 'model_number' || $key === 'model_name') {
    //         $value = mb_strtoupper($value, "UTF-8"); // Tamamını büyük harfe çevir.
    //     }

    //     parent::setAttribute($key, $value);
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);  // User ile ilişki
    }

    public function clientProducts(): HasMany
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function boardIds(): HasMany
    {
        return $this->hasMany(BoardId::class);
    }

    //TODO Clients modelden aynısını yap.
    protected static function booted()
    {
        static::deleting(function ($device) {
            $device->clientProducts()->delete();
        });
    }
}
