<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->boolean('phone_verified')->default(false)->after('is_used');
        });
    }

    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->dropColumn('phone_verified');
        });
    }
};
