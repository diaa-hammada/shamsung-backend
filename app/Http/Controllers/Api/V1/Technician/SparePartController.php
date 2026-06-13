<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Technician;

use App\Http\Controllers\Controller;
use App\Models\SparePart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $technician = $request->user();

        $spareParts = SparePart::where('shop_id', $technician->shop_id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Spare parts retrieved successfully',
            'data' => $spareParts
        ], 200);
    }
}