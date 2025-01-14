<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardId extends Model
{
    /** @use HasFactory<\Database\Factories\BoardIdFactory> */
    use HasFactory;

    protected $fillable = ['build_name', 'user_id', 'product_id'];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
