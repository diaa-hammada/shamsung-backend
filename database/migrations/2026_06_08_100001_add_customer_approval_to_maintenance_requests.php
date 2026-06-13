<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('customer_status')->default('pending_approval')->after('status');
            $table->string('rejection_reason')->nullable()->after('customer_status');
            $table->string('payment_method')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['customer_status', 'rejection_reason', 'payment_method']);
        });
    }
};
