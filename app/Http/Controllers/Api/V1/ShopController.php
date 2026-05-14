<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shop\FindNearestShopRequest;
use App\Services\ShopService;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    public function __construct(private readonly ShopService $shopService)
    {
    }

    public function nearest(FindNearestShopRequest $request): JsonResponse
    {
        $shops = $this->shopService->findNearestShops(
            (float) $request->validated('latitude'),
            (float) $request->validated('longitude')
        );

        return response()->json([
            'message' => 'Nearest shops retrieved successfully',
            'data' => $shops
        ], 200);
    }
}