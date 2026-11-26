<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Servis kaydı kalemi: parça (stoktan düşer) ya da işçilik. */
class TicketItem extends Model
{
    public const KIND_PART = 'part';

    public const KIND_LABOR = 'labor';

    protected $fillable = ['ticket_id', 'part_id', 'kind', 'name', 'qty', 'unit_cost', 'unit_price'];

    protected $casts = [
        'qty' => 'integer',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Stoktaki parça kayda eklenince stoktan düşer, kalem silinince geri eklenir
        static::created(function (TicketItem $item) {
            $item->part?->decrement('stock', $item->qty);
        });
        static::updated(function (TicketItem $item) {
            if ($item->wasChanged('qty') && $item->part) {
                $item->part->decrement('stock', $item->qty - (int) $item->getOriginal('qty'));
            }
        });
        static::deleted(function (TicketItem $item) {
            $item->part?->increment('stock', $item->qty);
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function total(): float
    {
        return $this->qty * (float) $this->unit_price;
    }

    public function costTotal(): float
    {
        return $this->qty * (float) $this->unit_cost;
    }
}
