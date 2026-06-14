<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Technician;
use Illuminate\Database\Seeder;
class TechnicianSeeder extends Seeder
{
    public function run(): void
    {
        $shop = Shop::first();

        // hashed cast on Technician model auto-hashes the password — pass plain text
        Technician::firstOrCreate(
            ['email' => 'tech@shamsung.com'],
            [
                'shop_id'        => $shop?->id,
                'first_name'     => 'أحمد',
                'last_name'      => 'التقني',
                'phone'          => '+963977777777',
                'password'       => 'password123',
                'specialization' => 'iPhone Repair',
                'experience'     => '3 years',
                'is_active'      => true,
            ]
        );
    }
}
