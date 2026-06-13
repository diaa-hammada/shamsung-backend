<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('confirmation_image_path')->nullable()->after('confirmation_code');
            $table->enum('payment_method', ['cash_on_delivery', 'prepaid'])->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['confirmation_image_path', 'payment_method']);
        });
    }
};
