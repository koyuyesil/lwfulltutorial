<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('board_ids', function (Blueprint $table) {
            // Board ID devresinin tipi: adc | logic | pullup_pmic | pullup_cpu
            $table->string('divider_type')->default('adc')->after('build_name');
            // Bölücünün beslendiği referans gerilim (çoğu kartta 1,8 V)
            $table->decimal('vref', 5, 3)->default(1.8)->after('divider_type');

            $table->string('mass_production_hwid')->nullable()->change();
            $table->string('pre_production_hwid')->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('board_ids', function (Blueprint $table) {
            $table->dropColumn(['divider_type', 'vref']);
        });
    }
};
