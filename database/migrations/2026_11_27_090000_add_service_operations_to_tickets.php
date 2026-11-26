<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Servis operasyonları:
 *  - parts: yedek parça kataloğu ve stok
 *  - ticket_items: servis kaydının parça / işçilik kalemleri (teklif ve kâr hesabı)
 *  - ticket_photos: kabul fotoğrafları (hasar kaydı)
 *  - tickets: teknisyen, kabul formu, imza, ekran kilidi, teklif onayı, memnuniyet anketi
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();      // Ekran, Batarya, Şarj soketi …
            $table->string('compatible')->nullable();    // uyumlu modeller (serbest metin)
            $table->decimal('cost', 10, 2)->default(0);  // alış
            $table->decimal('price', 10, 2)->default(0); // satış
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->string('supplier')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });

        Schema::create('ticket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind')->default('part'); // part | labor
            $table->string('name');
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('ticket_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('technician')->nullable()->index();

            // Kabul formu: aksesuarlar, dış durum, not; kilit bilgisi şifreli saklanır
            $table->json('intake')->nullable();
            $table->string('lock_type')->nullable();   // none | pin | pattern | password
            $table->text('lock_secret')->nullable();   // encrypted
            $table->longText('signature')->nullable(); // PNG data URL
            $table->timestamp('signed_at')->nullable();

            // Teklif ve müşteri onayı
            $table->string('quote_status')->nullable(); // pending | approved | rejected
            $table->decimal('quote_total', 10, 2)->nullable();
            $table->timestamp('quote_sent_at')->nullable();
            $table->timestamp('quote_decided_at')->nullable();
            $table->string('quote_decided_by')->nullable(); // customer | staff
            $table->string('quote_decision_ip', 45)->nullable();
            $table->text('quote_customer_note')->nullable();

            // Memnuniyet anketi (NPS 0–10)
            $table->unsignedTinyInteger('survey_score')->nullable();
            $table->text('survey_comment')->nullable();
            $table->timestamp('survey_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['technician']);
            $table->dropColumn([
                'technician', 'intake', 'lock_type', 'lock_secret', 'signature', 'signed_at',
                'quote_status', 'quote_total', 'quote_sent_at', 'quote_decided_at', 'quote_decided_by', 'quote_decision_ip', 'quote_customer_note',
                'survey_score', 'survey_comment', 'survey_at',
            ]);
        });
        Schema::dropIfExists('ticket_photos');
        Schema::dropIfExists('ticket_items');
        Schema::dropIfExists('parts');
    }
};
