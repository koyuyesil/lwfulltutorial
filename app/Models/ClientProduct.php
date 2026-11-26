<?php

namespace App\Models;

use App\Models\User;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientProduct extends Model
{
    use \App\Models\Concerns\Trashable;

    /** Silinince birlikte çöp kutusuna taşınır, geri alınınca birlikte döner. */
    protected array $cascadeDeletes = ['tickets'];
    /** @use HasFactory<\Database\Factories\ClientProductFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'client_id', 'serial', 'imei', 'color'];
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }


}
