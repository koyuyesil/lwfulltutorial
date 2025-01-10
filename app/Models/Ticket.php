<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;
    protected $fillable = ['client_product_id', 'problem', 'priority', 'status'];
    public function clientProduct()
    {
        return $this->belongsTo(ClientProduct::class);
    }
}
