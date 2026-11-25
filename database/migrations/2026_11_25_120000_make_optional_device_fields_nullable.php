<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Model numarası, açıklama, IMEI, seri no ve renk her cihazda bilinmeyebilir.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('model_number')->nullable()->change();
            $table->text('description')->nullable()->change();
        });

        Schema::table('client_products', function (Blueprint $table) {
            $table->string('serial')->nullable()->change();
            $table->string('imei')->nullable()->change();
            $table->string('color')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('model_number')->change();
            $table->string('description')->change();
        });

        Schema::table('client_products', function (Blueprint $table) {
            $table->string('serial')->change();
            $table->string('imei')->change();
            $table->string('color')->change();
        });
    }
};
