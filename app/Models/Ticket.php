<?php

namespace App\Models;

use App\Enums\StatusType;
use App\Enums\PriorityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;
    protected $fillable = ['user_id','client_product_id', 'problem', 'priority', 'status'];

      // database den gelen verileri cast etme yani tip dönüşümü
      protected $casts = [
        'status' => StatusType::class,
        'priority' => PriorityType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientProduct()
    {
        return $this->belongsTo(ClientProduct::class);
    }
}
