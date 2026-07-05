<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\RequestStockRequest;
use App\Http\Requests\Api\V1\Admin\StoreSparePartRequest;
use App\Http\Requests\Api\V1\Admin\UpdateSparePartRequest;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\SparePart;
use App\Models\StockRequest;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}

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
        $stockRequest->load(['shop:id,name', 'sparePart:id,name']);

        $sparePartName = $stockRequest->sparePart->name;
        $shopName      = $stockRequest->shop->name;
        $quantity      = $stockRequest->quantity;

        $adminTitle = 'طلب توريد جديد';
        $adminBody  = "تم طلب {$quantity} وحدة من {$sparePartName} لصالة {$shopName}";
        $adminData  = ['type' => 'stock_request', 'id' => (string) $stockRequest->id];

        Admin::all()->each(function (Admin $admin) use ($stockRequest, $adminTitle, $adminBody, $adminData) {
            Notification::create([
                'admin_id' => $admin->id,
                'type'     => 'stock_request',
                'title'    => $adminTitle,
                'body'     => $adminBody,
                'data'     => [
                    'stock_request_id' => $stockRequest->id,
                    'shop_id'          => $stockRequest->shop_id,
                    'spare_part_id'    => $stockRequest->spare_part_id,
                ],
            ]);

            if ($admin->fcm_token) {
                $this->fcm->send($admin->fcm_token, $adminTitle, $adminBody, $adminData);
            }
        });

        return response()->json([
            'message' => 'Stock request submitted successfully',
            'data'    => $stockRequest,
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
