<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Yedek parça ve stok. */
class Part extends Model
{
    use HasFactory;

    public const CATEGORIES = ['Ekran', 'Batarya', 'Şarj soketi', 'Kamera', 'Arka kapak', 'Hoparlör / mikrofon', 'Anakart / entegre', 'Kablo / flex', 'Sarf malzeme', 'Diğer'];

    protected $fillable = ['user_id', 'component_id', 'name', 'sku', 'category', 'compatible', 'cost', 'price', 'stock', 'min_stock', 'supplier'];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TicketItem::class);
    }

    public function isLow(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function margin(): ?float
    {
        return (float) $this->price > 0 ? ((float) $this->price - (float) $this->cost) / (float) $this->price * 100 : null;
    }

    public function scopeLow(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }
}
