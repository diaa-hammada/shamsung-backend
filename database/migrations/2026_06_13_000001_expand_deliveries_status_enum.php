<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE deliveries MODIFY COLUMN status ENUM('pending','accepted','on_the_way','arrived','picked_up','in_transit','delivered','failed','rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE deliveries MODIFY COLUMN status ENUM('pending','accepted','on_the_way','arrived','delivered') NOT NULL DEFAULT 'pending'");
    }
};
