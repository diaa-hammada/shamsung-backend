<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\RequestStockRequest;
use App\Http\Requests\Api\V1\Admin\StoreSparePartRequest;
use App\Http\Requests\Api\V1\Admin\UpdateSparePartRequest;
use App\Models\SparePart;
use App\Models\StockRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function index(): JsonResponse
    {
        $spareParts = SparePart::with('shop:id,name')->latest()->paginate(15);

        return response()->json([
            'message' => 'Spare parts inventory retrieved successfully',
            'data'    => $spareParts,
        ], 200);
    }

    public function store(StoreSparePartRequest $request): JsonResponse
    {
        $sparePart = SparePart::create($request->validated());

        return response()->json([
            'message' => 'Spare part created successfully',
            'data'    => $sparePart->load('shop:id,name'),
        ], 201);
    }

    public function update(UpdateSparePartRequest $request, int $id): JsonResponse
    {
        $sparePart = SparePart::findOrFail($id);
        $sparePart->update($request->validated());

        return response()->json([
            'message' => 'Spare part updated successfully',
            'data'    => $sparePart->load('shop:id,name'),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        SparePart::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Spare part deleted successfully',
        ], 200);
    }

    public function indexStockRequests(Request $request): JsonResponse
    {
        $query = StockRequest::with(['shop:id,name', 'sparePart:id,name'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'message' => 'Stock requests retrieved successfully',
            'data'    => $query->paginate(15),
        ], 200);
    }

    public function requestStock(RequestStockRequest $request): JsonResponse
    {
        $stockRequest = StockRequest::create($request->validated());

        return response()->json([
            'message' => 'Stock request submitted successfully',
            'data'    => $stockRequest->load(['shop:id,name', 'sparePart:id,name']),
        ], 201);
    }

    public function approveStockRequest(int $id): JsonResponse
    {
        $stockRequest = StockRequest::findOrFail($id);

        if ($stockRequest->status !== 'pending') {
            return response()->json(['message' => 'Request is already processed'], 400);
        }

        $stockRequest->update(['status' => 'approved']);

        SparePart::findOrFail($stockRequest->spare_part_id)
            ->increment('stock_quantity', $stockRequest->quantity);

        return response()->json([
            'message' => 'Stock request approved and inventory updated',
            'data'    => $stockRequest,
        ], 200);
    }
}
