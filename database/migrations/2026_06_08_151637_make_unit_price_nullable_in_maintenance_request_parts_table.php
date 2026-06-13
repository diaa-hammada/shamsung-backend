<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE maintenance_request_parts MODIFY COLUMN unit_price DECIMAL(10,2) NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE maintenance_request_parts SET unit_price = 0 WHERE unit_price IS NULL');
        DB::statement('ALTER TABLE maintenance_request_parts MODIFY COLUMN unit_price DECIMAL(10,2) NOT NULL');
    }
};
