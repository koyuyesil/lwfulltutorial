<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Enums\PriorityType;
use App\Enums\StatusType;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceFactory> */
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'status', 'priority', 'deadline'];

    // formdan gelen verileri cast etme yani tip dönüşümü
    protected $casts = [
        'deadline' => 'date',
        'status' => StatusType::class,
        'priority' => PriorityType::class,
    ];

    /*setSlugAttribute Mutator: Laravel'de model üzerinde bir mutator tanımlayarak,
    bir özelliğin değeri ayarlandığında otomatik olarak işlem yapabilirsiniz.
    setSlugAttribute metodu, slug değeri atandığında devreye girer ve değeri otomatik olarak
    Str::slug ile dönüştürür. Bu sayede slug'ın her zaman doğru formatta
    (örneğin, title-of-task) olmasını sağlar. */

    //Slug'ın otomatik olarak oluşturulması için mutator kullanımı
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
