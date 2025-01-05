<?php

namespace App\Models;

use App\Models\CustomerDevice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Device extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceFactory> */
    use HasFactory;


    protected $fillable = ['manufacturer', 'brand', 'model_name', 'model_number', 'description'];

    //Slug'ın otomatik olarak oluşturulması için mutator kullanımı. not isim formatı zorunlu.
    public function setManufacturerAttribute($value)
    {
        $this->attributes['manufacturer'] = Str::title($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);  // User ile ilişki
    }

    public function customerDevices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class);
    }

    // TODO device'dan item silindiğinde customerDevices de başka bilinmeyen aygıt olarak aktarılsın
    protected static function booted()
    {
        static::deleting(function ($device) {
            $device->customerDevices()->delete();
        });
    }
}
