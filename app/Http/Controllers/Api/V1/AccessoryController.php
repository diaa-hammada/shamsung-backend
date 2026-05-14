<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Senior Trick: فلترة ذكية حسب الصالة إذا أرسل تطبيق الموبايل رقم الصالة في الرابط
        $query = Accessory::with('shop:id,name,address')->where('is_active', true);

        if ($request->has('shop_id')) {
            $query->where('shop_id', (int) $request->input('shop_id'));
        }

        $accessories = $query->latest()->get();

        return response()->json([
            'message' => 'Accessories retrieved successfully',
            'data' => $accessories
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $accessory = Accessory::with('shop:id,name,address,phone')
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'message' => 'Accessory details retrieved successfully',
            'data' => $accessory
        ], 200);
    }
}