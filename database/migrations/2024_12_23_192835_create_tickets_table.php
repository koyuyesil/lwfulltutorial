<?php
use App\Models\User;
use App\Models\ClientProduct;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(ClientProduct::class)->constrained()->onDelete('cascade');
            $table->string('problem');
            $table->string('priority');
            $table->string('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
