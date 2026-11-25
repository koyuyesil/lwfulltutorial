<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('codename')->nullable()->index();
            $table->string('device_type')->default('phone');
            $table->unsignedSmallInteger('release_year')->nullable();
            $table->string('chipset')->nullable();
            $table->longText('image')->nullable(); // data:image/...;base64,...
        });

        // Kullanıcı (firma) bazlı ayarlar: anahtar → JSON değer
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['codename']);
            $table->dropColumn(['codename', 'device_type', 'release_year', 'chipset', 'image']);
        });
    }
};
