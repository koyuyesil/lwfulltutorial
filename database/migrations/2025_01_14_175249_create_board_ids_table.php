<?php

use App\Models\User;
use App\Models\Product;
use App\Enums\RepairMethod;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('board_ids', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Product::class)->constrained()->onDelete('cascade');

            $table->string('build_name');
            $table->json('resistances'); // R1 ve R2 değerlerini JSON olarak saklar
            //$table->string('repair_methods'); //meta,flash,patch,unlocked,locked,resistor
            $table->json('repair_methods'); //meta,flash,patch,unlocked,locked,resistor
            $table->string('mass_production_hwid');
            $table->string('pre_production_hwid');
            // $table->string('pcb_view'); // Dosya yolu
            // $table->string('schematics'); // Dosya yolu
            // $table->string('solutions'); // Dosya yolu
            $table->string('description'); // Açıklama

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_ids');
    }
};
