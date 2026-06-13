<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_request_parts', function (Blueprint $table) {
            $table->string('name')->nullable()->after('maintenance_request_id');
            $table->decimal('price', 10, 2)->nullable()->after('name');
            $table->boolean('is_required')->default(false)->after('price');
            $table->boolean('is_selected')->default(false)->after('is_required');
        });

        // Make spare_part_id nullable to allow free-text parts from diagnosis
        DB::statement('ALTER TABLE maintenance_request_parts MODIFY COLUMN spare_part_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('maintenance_request_parts', function (Blueprint $table) {
            $table->dropColumn(['name', 'price', 'is_required', 'is_selected']);
        });
    }
};
