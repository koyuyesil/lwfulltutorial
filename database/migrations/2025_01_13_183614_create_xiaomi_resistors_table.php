<?php

use App\Models\File;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('xiaomi_resistors', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Product::class)->constrained()->onDelete('cascade');

            $table->string('device'); // Dosya yolu
            $table->json('resistances')->nullable(); // R1 ve R2 değerlerini JSON olarak saklar
            $table->string('hwid_for_orginal'); // Dosya yolu
            $table->string('hwid_for_rsa'); // Dosya yolu
            $table->string('pcb_image_path'); // Dosya yolu
            $table->string('sch_image_path'); // Dosya yolu
            $table->string('schematics'); // Dosya yolu
            $table->string('solutions'); // Dosya yolu
            $table->string('description'); // Dosya yolu


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xiaomi_resistors');
    }
};
