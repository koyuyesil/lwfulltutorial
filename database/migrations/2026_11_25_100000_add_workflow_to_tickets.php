<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUS_MAP = [
        'open' => 'received',
        'in_progress' => 'in_repair',
        'done' => 'delivered',
    ];

    private const SLA_HOURS = ['high' => 24, 'normal' => 72, 'low' => 120];

    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
        });

        Schema::create('ticket_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // created | status_changed | note
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_public')->default(false); // müşteri takip sayfasında görünsün mü
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        // Eski 3 durumlu kayıtları yeni iş akışına taşı ve SLA / geçmiş üret
        foreach (DB::table('tickets')->get() as $ticket) {
            $status = self::STATUS_MAP[$ticket->status] ?? $ticket->status;
            $closed = in_array($status, ['delivered', 'cancelled'], true);

            DB::table('tickets')->where('id', $ticket->id)->update([
                'status' => $status,
                'due_at' => \Illuminate\Support\Carbon::parse($ticket->created_at)
                    ->addHours(self::SLA_HOURS[$ticket->priority] ?? 72),
                'status_changed_at' => $ticket->updated_at,
                'closed_at' => $closed ? $ticket->updated_at : null,
            ]);

            DB::table('ticket_events')->insert([
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'type' => 'created',
                'to_status' => 'received',
                'is_public' => true,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->created_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_events');

        foreach (array_flip(self::STATUS_MAP) as $new => $old) {
            DB::table('tickets')->where('status', $new)->update(['status' => $old]);
        }
        DB::table('tickets')->whereNotIn('status', array_values(array_flip(self::STATUS_MAP)))->update(['status' => 'in_progress']);

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['due_at']);
            $table->dropColumn(['due_at', 'status_changed_at', 'closed_at']);
        });
    }
};
