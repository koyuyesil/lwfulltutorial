<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Çapraz referans (ilişkisel bileşen kataloğu):
 *  - components: CPU/SoC, RAM, depolama, PMIC, ekran, batarya… (parça / model kodu ile)
 *  - component_product: hangi cihazda hangi bileşen var (varyant notuyla, örn. 8/256)
 *  - component_compatibilities: bileşen ↔ bileşen uyumluluğu (aynı tablodan başka satır; iki yönlü)
 *  - parts.component_id: stoktaki parçanın hangi bileşen olduğu (uyumlu cihazlar buradan bulunur)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);           // App\Enums\ComponentType
            $table->string('code', 120);          // parça / model kodu: SM8550, K3LK7K70BM-BGCP …
            $table->string('name')->nullable();   // ticari ad: Snapdragon 8 Gen 2, 12 GB LPDDR5X …
            $table->string('manufacturer')->nullable();
            $table->json('specs')->nullable();    // {"Bellek tipi": "LPDDR5X", "Kapasite": "12 GB"}
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'code']);
            $table->index(['user_id', 'code']);
        });

        Schema::create('component_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('variant', 60)->nullable(); // 8/256, Global, CN …
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'component_id']);
        });

        Schema::create('component_compatibilities', function (Blueprint $table) {
            $table->id();
            // Çift tek satırla saklanır: component_id < compatible_id (iki yönde de sorgulanır)
            $table->foreignId('component_id')->constrained('components')->cascadeOnDelete();
            $table->foreignId('compatible_id')->constrained('components')->cascadeOnDelete();
            $table->string('kind', 20)->default('direct'); // direct | pin | software
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['component_id', 'compatible_id']);
            $table->index('compatible_id');
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->foreignId('component_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('component_id');
        });
        Schema::dropIfExists('component_compatibilities');
        Schema::dropIfExists('component_product');
        Schema::dropIfExists('components');
    }
};
