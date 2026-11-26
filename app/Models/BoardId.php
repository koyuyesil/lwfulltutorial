<?php

namespace App\Models;

use App\Enums\ChipType;
use App\Enums\DividerType;
use App\Enums\RepairMethod;
use App\Support\Si;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardId extends Model
{
    use \App\Models\Concerns\Trashable;
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'build_name', 'divider_type', 'chip', 'vref',
        'mass_production_hwid', 'pre_production_hwid', 'repair_methods', 'resistances', 'description',
    ];

    protected $casts = [
        'resistances' => 'array',
        'repair_methods' => 'array',
        'divider_type' => DividerType::class,
        'chip' => ChipType::class,
        'vref' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Ohm cinsinden R1 / R2 (takılı değilse null). */
    public function resistor(string $key): ?float
    {
        $value = $this->resistances[$key] ?? null;

        return $value === null || $value === '' ? null : Si::parse($value);
    }

    public function r1(): ?float
    {
        return $this->resistor('R1');
    }

    public function r2(): ?float
    {
        return $this->resistor('R2');
    }

    /** Onarım öncesi orijinal değerler (içe aktarmadan gelir; formda düzenlenmez). */
    public function r1Original(): ?float
    {
        return $this->resistor('R1_ORG');
    }

    public function r2Original(): ?float
    {
        return $this->resistor('R2_ORG');
    }

    /**
     * Board ID hattındaki gerilim.
     * Lojik tipte yalnız R1 → VREF (1), yalnız R2 → 0 V (0).
     */
    public function vout(): ?float
    {
        $vref = (float) ($this->vref ?: 1.8);
        [$r1, $r2] = [$this->r1(), $this->r2()];

        // Dahili pull-up değeri bilinmiyorsa gerilim hesaplanamaz
        if ($this->divider_type?->internalR1() && $r1 === null) {
            return null;
        }

        return match (true) {
            $r1 !== null && $r2 !== null && $r1 + $r2 > 0 => $vref * $r2 / ($r1 + $r2),
            $r1 !== null && $r2 === null => $vref,
            $r1 === null && $r2 !== null => 0.0,
            default => null,
        };
    }

    /** Lojik tipte okunan bit: 1 | 0 | null (belirsiz) */
    public function logicLevel(): ?int
    {
        $v = $this->vout();
        if ($v === null) {
            return null;
        }
        $vref = (float) ($this->vref ?: 1.8);

        return match (true) {
            $v >= $vref * 0.7 => 1,
            $v <= $vref * 0.3 => 0,
            default => null,
        };
    }

    /** @return list<RepairMethod> */
    public function methods(): array
    {
        return array_values(array_filter(array_map(
            fn ($m) => $m instanceof RepairMethod ? $m : RepairMethod::tryFrom((string) $m),
            (array) $this->repair_methods,
        )));
    }
}
