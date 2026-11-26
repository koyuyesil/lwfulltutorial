<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Çapraz referans bileşenleri parça tablosuna taşınır; ayrı bileşen tablosu kalmaz.
 *  - parts: type (SoC, RAM, PMIC…), code (parça / model kodu), manufacturer, specs, notes
 *  - part_product: hangi cihazda hangi parça var (varyant notuyla) - pivot
 *  - part_compatibilities: parça ↔ muadil parça (çift tek satır, küçük id önce; iki yönlü) - pivot
 * Mevcut bileşenler: bağlı stok parçası varsa onunla birleşir, yoksa stoksuz parça olarak oluşturulur.
 */
return new class extends Migration
{
    /** Bileşen türü → parça kategorisi */
    private const CATEGORY = [
        'soc' => 'Anakart / entegre', 'ram' => 'Anakart / entegre', 'storage' => 'Anakart / entegre', 'mcp' => 'Anakart / entegre',
        'pmic' => 'Anakart / entegre', 'charging' => 'Anakart / entegre', 'rf' => 'Anakart / entegre',
        'display' => 'Ekran', 'battery' => 'Batarya', 'camera' => 'Kamera', 'other' => 'Diğer',
    ];

    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->string('type', 30)->nullable()->after('user_id');      // App\Enums\ComponentType
            $table->string('code', 120)->nullable()->after('type');        // SM8550, K3LK7K70BM-BGCP
            $table->string('manufacturer', 120)->nullable()->after('category');
            $table->json('specs')->nullable()->after('supplier');
            $table->text('notes')->nullable()->after('specs');
            $table->index(['user_id', 'type', 'code']);
        });

        Schema::create('part_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('variant', 60)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'part_id']);
        });

        Schema::create('part_compatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts')->cascadeOnDelete();
            $table->foreignId('compatible_id')->constrained('parts')->cascadeOnDelete();
            $table->string('kind', 20)->default('direct'); // direct | pin | software
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['part_id', 'compatible_id']);
            $table->index('compatible_id');
        });

        if (Schema::hasTable('components')) {
            $this->migrateComponents();

            Schema::table('parts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('component_id');
            });
            Schema::dropIfExists('component_compatibilities');
            Schema::dropIfExists('component_product');
            Schema::dropIfExists('components');
        }
    }

    private function migrateComponents(): void
    {
        $map = []; // component id → part id
        $now = now();

        foreach (DB::table('components')->orderBy('id')->get() as $c) {
            $linked = DB::table('parts')->where('component_id', $c->id)->orderByRaw('deleted_at IS NOT NULL')->orderBy('id')->get();

            if ($first = $linked->first()) {
                DB::table('parts')->where('id', $first->id)->update([
                    'type' => $c->type, 'code' => $c->code,
                    'manufacturer' => $c->manufacturer, 'specs' => $c->specs,
                    'notes' => $first->notes ?? $c->notes,
                ]);
                $map[$c->id] = $first->id;

                // Aynı bileşene bağlı diğer stok parçaları birebir muadil olur
                foreach ($linked->skip(1) as $other) {
                    [$a, $b] = $first->id < $other->id ? [$first->id, $other->id] : [$other->id, $first->id];
                    DB::table('part_compatibilities')->insertOrIgnore(['part_id' => $a, 'compatible_id' => $b, 'kind' => 'direct', 'created_at' => $now, 'updated_at' => $now]);
                }
            } else {
                $map[$c->id] = DB::table('parts')->insertGetId([
                    'user_id' => $c->user_id, 'type' => $c->type, 'code' => $c->code,
                    'name' => $c->name ?: $c->code, 'category' => self::CATEGORY[$c->type] ?? 'Diğer',
                    'manufacturer' => $c->manufacturer, 'specs' => $c->specs, 'notes' => $c->notes,
                    'cost' => 0, 'price' => 0, 'stock' => 0, 'min_stock' => 0,
                    'created_at' => $c->created_at, 'updated_at' => $c->updated_at, 'deleted_at' => $c->deleted_at,
                ]);
            }
        }

        foreach (DB::table('component_product')->orderBy('id')->get() as $row) {
            if (isset($map[$row->component_id])) {
                DB::table('part_product')->insert([
                    'part_id' => $map[$row->component_id], 'product_id' => $row->product_id,
                    'variant' => $row->variant, 'note' => $row->note,
                    'created_at' => $row->created_at, 'updated_at' => $row->updated_at,
                ]);
            }
        }

        foreach (DB::table('component_compatibilities')->orderBy('id')->get() as $row) {
            $a = $map[$row->component_id] ?? null;
            $b = $map[$row->compatible_id] ?? null;
            if ($a && $b && $a !== $b) {
                DB::table('part_compatibilities')->insertOrIgnore([
                    'part_id' => min($a, $b), 'compatible_id' => max($a, $b), 'kind' => $row->kind, 'note' => $row->note,
                    'created_at' => $row->created_at, 'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    /** Geri alma: yapı eski hâline döner (çapraz referans verisi taşınmaz). */
    public function down(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('code', 120);
            $table->string('name')->nullable();
            $table->string('manufacturer')->nullable();
            $table->json('specs')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'type', 'code']);
        });
        Schema::create('component_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('variant', 60)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
        Schema::create('component_compatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('components')->cascadeOnDelete();
            $table->foreignId('compatible_id')->constrained('components')->cascadeOnDelete();
            $table->string('kind', 20)->default('direct');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['component_id', 'compatible_id']);
        });
        Schema::table('parts', function (Blueprint $table) {
            $table->foreignId('component_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        Schema::dropIfExists('part_compatibilities');
        Schema::dropIfExists('part_product');
        Schema::table('parts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type', 'code']);
            $table->dropColumn(['type', 'code', 'manufacturer', 'specs', 'notes']);
        });
    }
};
