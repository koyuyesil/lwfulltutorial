<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDetail extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceDetailFactory> */
    use HasFactory;

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function productService()
    {
        return $this->belongsTo(ProductService::class);
    }

}
