<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('phone');
        });

        Schema::table('technicians', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });

        Schema::table('technicians', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
};
