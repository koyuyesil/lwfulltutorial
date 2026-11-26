<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Geri alınabilir silme: çöp kutusu için deleted_at sütunları. */
return new class extends Migration
{
    private array $tables = ['tickets', 'clients', 'client_products', 'products', 'parts', 'components', 'tasks', 'board_ids'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->softDeletes();
                $table->index('deleted_at');
            });
        }

        // Çöpteki bileşen aynı kodun yeniden eklenmesini engellemesin: tekillik doğrulamada (deleted_at IS NULL) kontrol edilir
        Schema::table('components', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'type', 'code']);
            $table->index(['user_id', 'type', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type', 'code']);
            $table->unique(['user_id', 'type', 'code']);
        });

        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropIndex(['deleted_at']);
                $table->dropSoftDeletes();
            });
        }
    }
};
