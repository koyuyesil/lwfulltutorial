<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PriotyType;
use App\Enums\StatusType;

class Task extends Model
{
    Use HasFactory;

    protected $fillable=['title','slug','description','status','priority','deadline'];

    //burası model koruması son kontrol
    protected $casts=[
        'deadline'=>'date',
        'status'=>StatusType::class,
        'prioty'=>PriotyType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
