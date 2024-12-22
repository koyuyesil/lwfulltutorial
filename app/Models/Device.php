<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceFactory> */
    use HasFactory;



    public function customerDevices()
    {
        return $this->hasMany(CustomerDevice::class);
    }

    // TODO AYGIT SİLİNDİĞİNDE customerDevices de başka bilinmeyen aygıt olarak aktarılsın
    protected static function booted()
    {
        static::deleting(function ($device) {
            $device->customerDevices()->delete();
        });
    }
}
