<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Device;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerDevice extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerDeviceFactory> */
    use HasFactory;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function ticket(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
