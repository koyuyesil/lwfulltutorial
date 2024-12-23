<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDevice extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerDeviceFactory> */
    use HasFactory;


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }
}
