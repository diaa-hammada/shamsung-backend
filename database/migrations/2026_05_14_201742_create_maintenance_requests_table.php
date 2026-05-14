<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('device_model');
            $table->text('problem_description');
            $table->enum('status', [
                'pending',      // Request sent by customer
                'received',     // Device received at shop
                'in_repair',    // Technician started working
                'completed',    // Fix finished
                'delivered',    // Customer took the device
                'cancelled'     // Request cancelled
            ])->default('pending');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};