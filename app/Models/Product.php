<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use \App\Models\Concerns\Trashable;

    /** Silinince birlikte çöp kutusuna taşınır, geri alınınca birlikte döner. */
    protected array $cascadeDeletes = ['clientProducts', 'boardIds'];
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    protected $fillable = [
        'manufacturer', 'brand', 'model_name', 'model_number', 'description',
        'codename', 'device_type', 'release_year', 'chipset', 'image',
    ];

    /** Base64 görseller listelerde gereksiz yere taşınmasın. */
    protected $hidden = ['image'];

    protected $casts = ['release_year' => 'integer'];

    // Single mutator. (Methot ismi rastgele değildir.)

    public function setManufacturerAttribute($value)
    {
        $this->attributes['manufacturer'] = Str::title($value);
    }

    // TODO Multi attiribute mutator
    // public function setAttribute($key, $value)
    // {
    //     if ($key === 'manufacturer' || $key === 'brand') {
    //         $value = mb_convert_case($value, MB_CASE_TITLE, "UTF-8"); // İlk harf büyük, geri kalanı küçük.
    //     } elseif ($key === 'model_number' || $key === 'model_name') {
    //         $value = mb_strtoupper($value, "UTF-8"); // Tamamını büyük harfe çevir.
    //     }

    //     parent::setAttribute($key, $value);
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);  // User ile ilişki
    }

    public function clientProducts(): HasMany
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Ticket::class, ClientProduct::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->brand.' '.$this->model_name);
    }

    /** Çapraz referans: cihazdaki parçalar (SoC, RAM, PMIC, ekran…) */
    public function parts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Part::class, 'part_product')->withPivot(['id', 'variant', 'note'])->withTimestamps();
    }

    public function boardIds(): HasMany
    {
        return $this->hasMany(BoardId::class);
    }

}
