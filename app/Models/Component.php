<?php

namespace App\Models;

use App\Enums\CompatibilityKind;
use App\Enums\ComponentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Çapraz referans bileşeni (SoC, RAM, depolama, PMIC…).
 * Bir bileşen birden çok cihazda bulunabilir ve başka bileşenlerle uyumlu olabilir (iki yönlü).
 */
class Component extends Model
{
    protected $fillable = ['user_id', 'type', 'code', 'name', 'manufacturer', 'specs', 'notes'];

    protected $casts = [
        'type' => ComponentType::class,
        'specs' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot(['id', 'variant', 'note'])->withTimestamps();
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    /** Bu bileşenin "küçük kimlikli" taraf olduğu uyumluluklar */
    public function compatibleTo(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'component_compatibilities', 'component_id', 'compatible_id')->withPivot(['id', 'kind', 'note'])->withTimestamps();
    }

    /** Bu bileşenin "büyük kimlikli" taraf olduğu uyumluluklar */
    public function compatibleFrom(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'component_compatibilities', 'compatible_id', 'component_id')->withPivot(['id', 'kind', 'note'])->withTimestamps();
    }

    /** İki yöndeki tüm uyumlu bileşenler (pivot: kind, note) */
    public function compatibles(): Collection
    {
        $this->loadMissing('compatibleTo', 'compatibleFrom');

        return $this->compatibleTo->concat($this->compatibleFrom)->sortBy('code')->values();
    }

    /** Uyumluluk ekle / güncelle. Çift tek satır olarak (küçük id, büyük id) saklanır. */
    public function linkCompatible(self $other, CompatibilityKind $kind = CompatibilityKind::DIRECT, ?string $note = null): void
    {
        abort_if($other->is($this) || $other->user_id !== $this->user_id, 422);
        [$a, $b] = $this->id < $other->id ? [$this->id, $other->id] : [$other->id, $this->id];

        DB::table('component_compatibilities')->updateOrInsert(
            ['component_id' => $a, 'compatible_id' => $b],
            ['kind' => $kind->value, 'note' => $note ?: null, 'updated_at' => now(), 'created_at' => now()],
        );
        $this->unsetRelation('compatibleTo')->unsetRelation('compatibleFrom');
    }

    public function unlinkCompatible(self $other): void
    {
        [$a, $b] = $this->id < $other->id ? [$this->id, $other->id] : [$other->id, $this->id];
        DB::table('component_compatibilities')->where(['component_id' => $a, 'compatible_id' => $b])->delete();
        $this->unsetRelation('compatibleTo')->unsetRelation('compatibleFrom');
    }

    /** Bu bileşenle ya da uyumlu bileşenlerinden biriyle çalışan cihazlar */
    public function reachableProductIds(): Collection
    {
        $ids = $this->compatibles()->pluck('id')->push($this->id);

        return DB::table('component_product')->whereIn('component_id', $ids)->distinct()->pluck('product_id');
    }

    public function label(): string
    {
        return $this->name ? "{$this->code} · {$this->name}" : $this->code;
    }
}
