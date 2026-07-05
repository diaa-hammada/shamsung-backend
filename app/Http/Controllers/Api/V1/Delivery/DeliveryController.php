<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Delivery\AcceptDeliveryRequest;
use App\Http\Requests\Api\V1\Delivery\CollectCashRequest;
use App\Http\Requests\Api\V1\Delivery\ConfirmDeliveryRequest;
use App\Http\Requests\Api\V1\Delivery\UpdateDeliveryStatusRequest;
use App\Models\Admin;
use App\Models\Delivery;
use App\Models\Notification;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    // Maps each status to the only valid next status via this endpoint.
    // 'delivered' is intentionally absent — it's set only via confirm().
    private const STATUS_TRANSITIONS = [
        'accepted'   => 'on_the_way',
        'on_the_way' => 'arrived',
    ];

    public function __construct(private readonly FcmService $fcm) {}

    // 1. GET /delivery/requests
    public function index(): JsonResponse
    {
        $deliveries = Delivery::where('status', 'pending')
            ->whereNull('delivery_worker_id')
            ->with([
                'customer:id,first_name,last_name,phone',
                'shop:id,name,address,latitude,longitude',
            ])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Pending delivery requests retrieved successfully',
            'data'    => $deliveries,
        ], 200);
    }

    // 2. POST /delivery/requests/{id}/accept
    public function accept(AcceptDeliveryRequest $request, int $id): JsonResponse
    {
        $worker = $request->user();

        $delivery = Delivery::where('status', 'pending')
            ->whereNull('delivery_worker_id')
            ->with('customer')
            ->findOrFail($id);

        $delivery->update([
            'delivery_worker_id' => $worker->id,
            'status'             => 'accepted',
            'estimated_time'     => $request->validated('estimated_time'),
        ]);

        try {
            if ($delivery->customer?->fcm_token) {
                $this->fcm->send(
                    $delivery->customer->fcm_token,
                    'عامل التوصيل في الطريق إليك',
                    'تم قبول طلب التوصيل الخاص بك.',
                    ['type' => 'delivery', 'id' => (string) $delivery->id],
                );
            }
        } catch (\Throwable) {}

        if ($delivery->customer_id) {
            Notification::create([
                'customer_id' => $delivery->customer_id,
                'type'        => 'delivery',
                'title'       => 'عامل التوصيل في الطريق إليك',
                'body'        => 'تم قبول طلب التوصيل الخاص بك.',
                'data'        => ['delivery_id' => $delivery->id],
            ]);
        }

        return response()->json([
            'message' => 'Delivery request accepted successfully',
            'data'    => $delivery,
        ], 200);
    }

    // 3. POST /delivery/requests/{id}/reject
    public function reject(Request $request, int $id): JsonResponse
    {
        $worker = $request->user();

        $delivery = Delivery::where('delivery_worker_id', $worker->id)
            ->findOrFail($id);

        $delivery->update([
            'delivery_worker_id' => null,
            'status'             => 'pending',
        ]);

        return response()->json([
            'message' => 'Delivery request rejected and returned to queue',
            'data'    => $delivery,
        ], 200);
    }

    // 4. GET /delivery/requests/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $worker = $request->user();

        $delivery = Delivery::where(function ($q) use ($worker) {
                $q->where('delivery_worker_id', $worker->id)
                  ->orWhere(fn ($q2) => $q2->where('status', 'pending')->whereNull('delivery_worker_id'));
            })
            ->with([
                'customer:id,first_name,last_name,phone',
                'shop:id,name,address,latitude,longitude',
                'maintenanceRequest',
                'order',
            ])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Delivery request retrieved successfully',
            'data'    => $delivery,
        ], 200);
    }

    // 5. POST /delivery/requests/{id}/status
    public function updateStatus(UpdateDeliveryStatusRequest $request, int $id): JsonResponse
    {
        $worker = $request->user();

        $delivery = Delivery::where('delivery_worker_id', $worker->id)
            ->with('customer')
            ->findOrFail($id);

        $newStatus = $request->validated('status');

        if ((self::STATUS_TRANSITIONS[$delivery->status] ?? null) !== $newStatus) {
            return response()->json([
                'message' => "Cannot transition from '{$delivery->status}' to '{$newStatus}'.",
            ], 422);
        }

        $delivery->update(['status' => $newStatus]);

        [$title, $body] = match ($newStatus) {
            'on_the_way' => ['عامل التوصيل في الطريق', 'عامل التوصيل في طريقه إليك الآن.'],
            'arrived'    => ['عامل التوصيل وصل', 'وصل عامل التوصيل إلى موقعك.'],
            default      => [null, null],
        };

        try {
            if ($title && $delivery->customer?->fcm_token) {
                $this->fcm->send(
                    $delivery->customer->fcm_token,
                    $title,
                    $body,
                    ['type' => 'delivery', 'id' => (string) $delivery->id],
                );
            }
        } catch (\Throwable) {}

        if ($title && $delivery->customer_id) {
            Notification::create([
                'customer_id' => $delivery->customer_id,
                'type'        => 'delivery',
                'title'       => $title,
                'body'        => $body,
                'data'        => ['delivery_id' => $delivery->id],
            ]);
        }

        return response()->json([
            'message' => 'Delivery status updated successfully',
            'data'    => $delivery,
        ], 200);
    }

    // 6. POST /delivery/requests/{id}/confirm
    public function confirm(ConfirmDeliveryRequest $request, int $id): JsonResponse
    {
        $worker = $request->user();

        $delivery = Delivery::where('delivery_worker_id', $worker->id)
            ->where('status', 'arrived')
            ->with('customer')
            ->findOrFail($id);

        $updateData = [
            'confirmed_at' => now(),
            'status'       => 'delivered',
        ];

        if ($request->filled('confirmation_code')) {
            $updateData['confirmation_code'] = $request->validated('confirmation_code');
        }

        if ($request->hasFile('image')) {
            $updateData['confirmation_image_path'] = $request->file('image')
                ->store('deliveries/confirmations', 'public');
        }

        $delivery->update($updateData);

        try {
            if ($delivery->customer?->fcm_token) {
                $this->fcm->send(
                    $delivery->customer->fcm_token,
                    'تم التوصيل بنجاح',
                    'تم توصيل طلبك بنجاح.',
                    ['type' => 'delivery', 'id' => (string) $delivery->id],
                );
            }
        } catch (\Throwable) {}

        if ($delivery->customer_id) {
            Notification::create([
                'customer_id' => $delivery->customer_id,
                'type'        => 'delivery',
                'title'       => 'تم التوصيل بنجاح',
                'body'        => 'تم توصيل طلبك بنجاح.',
                'data'        => ['delivery_id' => $delivery->id],
            ]);
        }

        try {
            $adminTokens = Admin::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            if (!empty($adminTokens)) {
                $this->fcm->sendMultiple(
                    $adminTokens,
                    'Delivery Confirmed',
                    "Delivery #{$delivery->id} has been marked as delivered.",
                    ['type' => 'delivery', 'id' => (string) $delivery->id],
                );
            }
        } catch (\Throwable) {}

        return response()->json([
            'message' => 'Delivery confirmed successfully',
            'data'    => $delivery,
        ], 200);
    }

    // 7. POST /delivery/requests/{id}/collect-cash
    public function collectCash(CollectCashRequest $request, int $id): JsonResponse
    {
        $worker = $request->user();

        $delivery = Delivery::where('delivery_worker_id', $worker->id)
            ->where('status', 'delivered')
            ->findOrFail($id);

        if ($delivery->payment_method !== 'cash_on_delivery') {
            return response()->json([
                'message' => 'This delivery does not require cash collection.',
            ], 422);
        }

        if ($delivery->cash_collected) {
            return response()->json([
                'message' => 'Cash has already been collected for this delivery.',
            ], 422);
        }

        $delivery->update([
            'cash_collected' => true,
            'cash_amount'    => $request->validated('cash_amount'),
        ]);

        try {
            $adminTokens = Admin::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            if (!empty($adminTokens)) {
                $this->fcm->sendMultiple(
                    $adminTokens,
                    'Cash Collected',
                    "Worker collected {$request->validated('cash_amount')} for delivery #{$delivery->id}.",
                    ['type' => 'delivery', 'id' => (string) $delivery->id],
                );
            }
        } catch (\Throwable) {}

        return response()->json([
            'message' => 'Cash collected successfully',
            'data'    => $delivery,
        ], 200);
    }

    // 8. GET /delivery/history
    public function history(Request $request): JsonResponse
    {
        $worker = $request->user();

        $deliveries = Delivery::where('delivery_worker_id', $worker->id)
            ->with([
                'customer:id,first_name,last_name,phone',
                'shop:id,name,address,latitude,longitude',
            ])
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'Delivery history retrieved successfully',
            'data'    => $deliveries,
        ], 200);
    }

    // 9. GET /delivery/earnings
    public function earnings(Request $request): JsonResponse
    {
        $worker     = $request->user();
        $deliveries = Delivery::where('delivery_worker_id', $worker->id)
            ->where('status', 'delivered')
            ->whereDate('confirmed_at', today())
            ->with([
                'customer:id,first_name,last_name',
                'shop:id,name',
            ])
            ->latest()
            ->get();

        return response()->json([
            'message' => "Today's earnings retrieved successfully",
            'data'    => [
                'total_deliveries' => $deliveries->count(),
                'total_cash'       => $deliveries->where('cash_collected', true)->sum('cash_amount'),
                'deliveries'       => $deliveries,
            ],
        ], 200);
    }
}
