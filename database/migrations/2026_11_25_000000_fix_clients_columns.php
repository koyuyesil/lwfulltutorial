<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * address yanlışlıkla `date` tipinde açılmıştı; company/email ise formlarda opsiyonel.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
            $table->string('company')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->date('address')->change();
            $table->string('company')->change();
            $table->string('email')->change();
        });
    }
};
