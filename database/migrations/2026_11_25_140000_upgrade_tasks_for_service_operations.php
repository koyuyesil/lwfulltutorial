<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('slug')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->date('deadline')->nullable()->change();

            $table->string('category')->default('general')->index();
            $table->dateTime('due_at')->nullable()->index();
            $table->unsignedInteger('remind_before')->nullable(); // dakika
            $table->dateTime('remind_at')->nullable()->index();   // erteleme bunu günceller
            $table->string('recurrence')->default('none');        // none|daily|weekly|monthly|yearly
            $table->unsignedSmallInteger('recurrence_interval')->default(1);
            $table->date('recurrence_until')->nullable();
            $table->string('series_id', 36)->nullable()->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('completed_at')->nullable();
        });

        // Eski "deadline" (yalnızca tarih) → due_at (saat 09:00)
        foreach (DB::table('tasks')->get() as $task) {
            DB::table('tasks')->where('id', $task->id)->update([
                'due_at' => $task->deadline ? Carbon::parse($task->deadline)->setTime(9, 0) : null,
                'completed_at' => $task->status === 'done' ? $task->updated_at : null,
                'series_id' => (string) Str::uuid(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropConstrainedForeignId('ticket_id');
            $table->dropIndex(['category']);
            $table->dropIndex(['due_at']);
            $table->dropIndex(['remind_at']);
            $table->dropIndex(['series_id']);
            $table->dropColumn([
                'category', 'due_at', 'remind_before', 'remind_at', 'recurrence', 'recurrence_interval',
                'recurrence_until', 'series_id', 'amount', 'completed_at',
            ]);
        });
    }
};
