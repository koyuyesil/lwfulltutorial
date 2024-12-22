<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'company',
        'phone',
        'email',
        'address',
    ];

    /**
     * Set the attribute values with custom formatting.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if ($key === 'fname' || $key === 'lname') {
            $value = ucfirst(strtolower($value));
        } elseif ($key === 'address' || $key === 'company') {
            $value = strtoupper($value);
        }

        parent::setAttribute($key, $value);
    }

    public function customerDevices()
    {
        return $this->hasMany(CustomerDevice::class);
    }

    // TODO MÜŞTERİ SİLİNDİĞİNDE customerDevices SİLİNMESİN BAŞKA USER YADA TABLOYA TAŞINSIN
    protected static function booted()
    {
        static::deleting(function ($customer) {
            $customer->customerDevices()->delete();
        });
    }

}
