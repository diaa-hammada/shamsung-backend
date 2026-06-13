<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customer   = $request->user();
        $deliveries = Delivery::where('customer_id', $customer->id)
            ->with([
                'deliveryWorker:id,first_name,last_name',
                'shop:id,name',
            ])
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'Deliveries retrieved successfully',
            'data'    => $deliveries,
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();

        $delivery = Delivery::where('customer_id', $customer->id)
            ->with([
                'deliveryWorker:id,first_name,last_name',
                'shop:id,name',
            ])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Delivery retrieved successfully',
            'data'    => [
                'id'              => $delivery->id,
                'type'            => $delivery->type,
                'status'          => $delivery->status,
                'estimated_time'  => $delivery->estimated_time,
                'delivery_worker' => $delivery->deliveryWorker
                    ? ['name' => $delivery->deliveryWorker->first_name . ' ' . $delivery->deliveryWorker->last_name]
                    : null,
                'shop'            => $delivery->shop,
                'created_at'      => $delivery->created_at,
            ],
        ], 200);
    }
}
