<?php

use App\Models\Device;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_devices', function (Blueprint $table) {
            $table->id();
            // $table->foreignIdFor(Device::class);
            // $table->foreignIdFor(Customer::class);
            $table->foreignIdFor(Device::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Customer::class)->constrained()->onDelete('cascade');
            $table->string('serial');
            $table->string('imei');
            $table->string('color');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_devices');
    }
};
