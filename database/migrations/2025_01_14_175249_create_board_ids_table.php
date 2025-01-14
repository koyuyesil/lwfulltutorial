<?php

use App\Models\Product;
use App\Models\User;
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
        Schema::create('board_ids', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Product::class)->constrained()->onDelete('cascade');

            $table->string('build_name');
            $table->json('resistances')->nullable(); // R1 ve R2 değerlerini JSON olarak saklar
            // $table->string('hwid_for_orginal');
            // $table->string('hwid_for_rsa');
            // $table->string('pcb_view'); // Dosya yolu
            // $table->string('schematics'); // Dosya yolu
            // $table->string('solutions'); // Dosya yolu
            // $table->string('description'); // Açıklama

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
