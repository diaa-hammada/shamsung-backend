<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('shops')->insert([
            [
                'name' => 'Al-Mazzah Main Center',
                'address' => 'Al-Mazzah Highway, Damascus',
                'latitude' => 33.507328,
                'longitude' => 36.273030,
                'phone' => '0112345678',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Abu Rummaneh Branch',
                'address' => 'Abu Rummaneh Square, Damascus',
                'latitude' => 33.520448,
                'longitude' => 36.289417,
                'phone' => '0112345679',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Al-Midan Fix Shop',
                'address' => 'Al-Midan, Damascus',
                'latitude' => 33.491234,
                'longitude' => 36.298765,
                'phone' => '0112345680',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }
}