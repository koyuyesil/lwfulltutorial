<?php

namespace App\Models;

use App\Enums\RepairMethod;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardId extends Model
{
    /** @use HasFactory<\Database\Factories\BoardIdFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'build_name',
        'mass_production_hwid',
        'pre_production_hwid',
        'repair_methods',
        'resistances',
        'description',

    ];
    protected $casts = [
        'repair_methods' => RepairMethod::class,
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
