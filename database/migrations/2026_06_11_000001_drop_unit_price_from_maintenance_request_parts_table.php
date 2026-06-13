<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE maintenance_request_parts DROP COLUMN unit_price');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE maintenance_request_parts ADD COLUMN unit_price DECIMAL(10,2) NULL AFTER quantity');
    }
};
