<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreDeliveryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateDeliveryRequest;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryWorker;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;

class AdminDeliveryController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}

    public function index(): JsonResponse
    {
        $deliveries = Delivery::with([
            'customer:id,first_name,last_name,phone',
            'deliveryWorker:id,first_name,last_name',
            'shop:id,name',
            'maintenanceRequest:id,tracking_number,status',
            'order:id,order_number,status',
        ])->latest()->paginate(15);

        return response()->json([
            'message' => 'Deliveries retrieved successfully',
            'data'    => $deliveries,
        ], 200);
    }

    public function store(StoreDeliveryRequest $request): JsonResponse
    {
        $validated           = $request->validated();
        $validated['status'] = 'pending';

        $delivery = Delivery::create($validated);

        try {
            $customer = Customer::find($validated['customer_id']);
            if ($customer?->fcm_token) {
                $this->fcm->send(
                    $customer->fcm_token,
                    'تم إنشاء طلب التوصيل',
                    'سيتم توصيل طلبك قريباً.',
                    ['type' => 'delivery', 'id' => (string) $delivery->id],
                );
            }
        } catch (\Throwable) {}

        return response()->json([
            'message' => 'Delivery created successfully',
            'data'    => $delivery->load([
                'customer:id,first_name,last_name,phone',
                'shop:id,name',
            ]),
        ], 201);
    }

    public function update(UpdateDeliveryRequest $request, int $id): JsonResponse
    {
        $delivery  = Delivery::findOrFail($id);
        $validated = $request->validated();

        if (array_key_exists('delivery_worker_id', $validated) && $validated['delivery_worker_id'] !== null) {
            $worker = DeliveryWorker::find($validated['delivery_worker_id']);
            if ($worker && $worker->shop_id !== $delivery->shop_id) {
                return response()->json([
                    'message' => 'Selected delivery worker does not belong to this shop',
                ], 422);
            }
        }

        $delivery->update($validated);

        return response()->json([
            'message' => 'Delivery updated successfully',
            'data'    => $delivery->load([
                'customer:id,first_name,last_name,phone',
                'deliveryWorker:id,first_name,last_name',
                'shop:id,name',
                'maintenanceRequest:id,tracking_number,status',
                'order:id,order_number,status',
            ]),
        ], 200);
    }
}
