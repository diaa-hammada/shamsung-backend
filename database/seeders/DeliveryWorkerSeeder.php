<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeliveryWorker;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class DeliveryWorkerSeeder extends Seeder
{
    public function run(): void
    {
        $shop = Shop::first();

        DeliveryWorker::firstOrCreate(
            ['phone' => '+963988888888'],
            [
                'shop_id'    => $shop?->id,
                'first_name' => 'محمد',
                'last_name'  => 'الديلفري',
                'email'      => 'delivery.test@shamsung.com',
                'is_active'  => true,
            ]
        );
    }
}
