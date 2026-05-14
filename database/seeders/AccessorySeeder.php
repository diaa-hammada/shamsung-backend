<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccessorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('accessories')->insert([
            [
                'shop_id' => 1,
                'name' => 'Samsung 45W Fast Charger',
                'description' => 'Original fast charger with Type-C cable included.',
                'price' => 35.50,
                'stock_quantity' => 20,
                'image_url' => 'https://example.com/images/charger.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'shop_id' => 1,
                'name' => 'iPhone 14 Pro Max Silicone Case',
                'description' => 'Premium silicone case with MagSafe support.',
                'price' => 15.00,
                'stock_quantity' => 50,
                'image_url' => 'https://example.com/images/case.jpg',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }
}