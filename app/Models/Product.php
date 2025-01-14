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

    // TODO multiple yap key value olarak. Başlık formatı mutator kullanımı. (Methot ismi rastgele değildir.)
    public function setManufacturerAttribute($value)
    {
        $this->attributes['manufacturer'] = Str::title($value);
    }

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

    // TODO device'dan item silindiğinde customerDevices de başka bilinmeyen aygıt olarak aktarılsın
    protected static function booted()
    {
        //TODO Clients modelden aynısını yap.
        static::deleting(function ($device) {
            $device->clientProducts()->delete();
        });
    }
}
