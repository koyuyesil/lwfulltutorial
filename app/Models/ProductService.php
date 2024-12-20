<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductService extends Model
{
    /** @use HasFactory<\Database\Factories\ProductServiceFactory> */
    use HasFactory;


    public function serviceDetail()
    {
        return $this->hasMany(ServiceDetail::class);
    }
}
