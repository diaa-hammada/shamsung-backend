<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['device_pickup', 'device_dropoff', 'accessory_delivery']);
            $table->foreignId('delivery_worker_id')->nullable()->constrained('delivery_workers')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('maintenance_request_id')->nullable()->constrained('maintenance_requests')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->enum('status', ['pending', 'accepted', 'on_the_way', 'arrived', 'delivered'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('confirmation_code')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('cash_collected')->default(false);
            $table->decimal('cash_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
