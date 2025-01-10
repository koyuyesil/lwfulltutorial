<?php

namespace App\Models;

use App\Models\Device;
use App\Models\Ticket;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerDevice extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerDeviceFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'device_id', 'customer_id', 'serial', 'imei', 'color'];
    // user_id
    // customer_id
    // device_id

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
