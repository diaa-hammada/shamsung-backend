<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Collection;

class ShopService
{
    public function findNearestShops(float $latitude, float $longitude, int $radiusInKm = 50): Collection
    {
        return Shop::select('shops.*')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->where('is_active', true)
            ->having('distance', '<=', $radiusInKm)
            ->orderBy('distance')
            ->get();
    }
}