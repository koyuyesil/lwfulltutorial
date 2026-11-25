<?php

use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('tracking_code', 16)->nullable()->unique()->after('id');
        });

        foreach (DB::table('tickets')->whereNull('tracking_code')->pluck('id') as $id) {
            DB::table('tickets')->where('id', $id)->update(['tracking_code' => Ticket::generateTrackingCode()]);
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['tracking_code']);
            $table->dropColumn('tracking_code');
        });
    }
};
