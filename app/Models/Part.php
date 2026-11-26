<?php

namespace App\Models;

use App\Enums\CompatibilityKind;
use App\Enums\ComponentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Parça: stok kalemi ve çapraz referans kaydı tek tabloda.
 *  - type / code doluysa çapraz referans parçasıdır (SoC, RAM, PMIC, ekran…); stoksuz da olabilir.
 *  - products(): bulunduğu cihazlar (pivot part_product, varyant notuyla)
 *  - compatibles(): muadil parçalar (pivot part_compatibilities, iki yönlü)
 */
class Part extends Model
{
    use \App\Models\Concerns\Trashable;
    use HasFactory;

    public const CATEGORIES = ['Ekran', 'Batarya', 'Şarj soketi', 'Kamera', 'Arka kapak', 'Hoparlör / mikrofon', 'Anakart / entegre', 'Kablo / flex', 'Sarf malzeme', 'Diğer'];

    protected $fillable = [
        'user_id', 'type', 'code', 'name', 'sku', 'category', 'manufacturer', 'compatible',
        'cost', 'price', 'stock', 'min_stock', 'supplier', 'specs', 'notes',
    ];

    protected $casts = [
        'type' => ComponentType::class,
        'specs' => 'array',
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TicketItem::class);
    }

    /** Bulunduğu cihazlar */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'part_product')->withPivot(['id', 'variant', 'note'])->withTimestamps();
    }

    /** Bu parçanın "küçük kimlikli" taraf olduğu muadillikler */
    public function compatibleTo(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'part_compatibilities', 'part_id', 'compatible_id')->withPivot(['id', 'kind', 'note'])->withTimestamps();
    }

    /** Bu parçanın "büyük kimlikli" taraf olduğu muadillikler */
    public function compatibleFrom(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'part_compatibilities', 'compatible_id', 'part_id')->withPivot(['id', 'kind', 'note'])->withTimestamps();
    }

    /** İki yöndeki tüm muadiller (pivot: kind, note) */
    public function compatibles(): Collection
    {
        $this->loadMissing('compatibleTo', 'compatibleFrom');

        return $this->compatibleTo->concat($this->compatibleFrom)->sortBy(fn ($p) => $p->code ?? $p->name)->values();
    }

    /** Muadillik ekle / güncelle. Çift tek satır olarak (küçük id, büyük id) saklanır. */
    public function linkCompatible(self $other, CompatibilityKind $kind = CompatibilityKind::DIRECT, ?string $note = null): void
    {
        abort_if($other->is($this) || $other->user_id !== $this->user_id, 422);
        [$a, $b] = $this->id < $other->id ? [$this->id, $other->id] : [$other->id, $this->id];

        DB::table('part_compatibilities')->updateOrInsert(
            ['part_id' => $a, 'compatible_id' => $b],
            ['kind' => $kind->value, 'note' => $note ?: null, 'updated_at' => now(), 'created_at' => now()],
        );
        $this->unsetRelation('compatibleTo')->unsetRelation('compatibleFrom');
    }

    public function unlinkCompatible(self $other): void
    {
        [$a, $b] = $this->id < $other->id ? [$this->id, $other->id] : [$other->id, $this->id];
        DB::table('part_compatibilities')->where(['part_id' => $a, 'compatible_id' => $b])->delete();
        $this->unsetRelation('compatibleTo')->unsetRelation('compatibleFrom');
    }

    /** Bu parçayla ya da muadillerinden biriyle çalışan cihazlar */
    public function reachableProductIds(): Collection
    {
        $ids = $this->compatibles()->pluck('id')->push($this->id);

        return DB::table('part_product')->whereIn('part_id', $ids)->distinct()->pluck('product_id');
    }

    /** Listede gösterilecek kısa kimlik: kod varsa kod, yoksa ad */
    public function ref(): string
    {
        return $this->code ?: $this->name;
    }

    public function label(): string
    {
        return $this->code && $this->name && $this->name !== $this->code ? "{$this->code} · {$this->name}" : $this->ref();
    }

    /** Minimum stok 0 ise stok takibi yapılmaz (ör. yalnızca çapraz referans kaydı) */
    public function isLow(): bool
    {
        return $this->min_stock > 0 && $this->stock <= $this->min_stock;
    }

    public function margin(): ?float
    {
        return (float) $this->price > 0 ? ((float) $this->price - (float) $this->cost) / (float) $this->price * 100 : null;
    }

    public function scopeLow(Builder $query): Builder
    {
        return $query->where('min_stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
    }

    /** Çapraz referans parçaları: türü belirli ya da bir cihaza bağlı */
    public function scopeReference(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->whereNotNull('type')->orWhereHas('products'));
    }
}
