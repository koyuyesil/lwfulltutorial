<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entegre (PMIC / CPU-SoC) bölücü tipinden ayrılır: her üç tip de iki entegreden birine bağlanabilir.
     * pullup_pmic → pullup + pmic, pullup_cpu → pullup + soc, logic → soc, adc → pmic
     */
    public function up(): void
    {
        Schema::table('board_ids', function (Blueprint $table) {
            $table->string('chip')->default('pmic')->after('divider_type');
        });

        DB::table('board_ids')->where('divider_type', 'pullup_pmic')->update(['divider_type' => 'pullup', 'chip' => 'pmic']);
        DB::table('board_ids')->where('divider_type', 'pullup_cpu')->update(['divider_type' => 'pullup', 'chip' => 'soc']);
        DB::table('board_ids')->where('divider_type', 'logic')->update(['chip' => 'soc']);
    }

    public function down(): void
    {
        DB::table('board_ids')->where('divider_type', 'pullup')->where('chip', 'soc')->update(['divider_type' => 'pullup_cpu']);
        DB::table('board_ids')->where('divider_type', 'pullup')->update(['divider_type' => 'pullup_pmic']);

        Schema::table('board_ids', function (Blueprint $table) {
            $table->dropColumn('chip');
        });
    }
};
