<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Customer\ApproveMaintenanceRequest;
use App\Http\Requests\Api\V1\Customer\RejectMaintenanceRequest;
use App\Http\Requests\Api\V1\Maintenance\StoreMaintenanceRequest;
use App\Models\Admin;
use App\Models\Delivery;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\SparePart;
use App\Models\Technician;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceRequestController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}

    public function store(StoreMaintenanceRequest $request): JsonResponse
    {
        $customer           = $request->user();
        $maintenanceRequest = $customer->maintenanceRequests()->create($request->validated());

        $this->notifyShopTechnicians(
            $maintenanceRequest->shop_id,
            'New Maintenance Request',
            'A new device repair request has been submitted.',
            ['type' => 'maintenance_request', 'id' => (string) $maintenanceRequest->id],
        );

        $customerName = trim($customer->first_name . ' ' . $customer->last_name);
        $deviceModel  = $request->validated('device_model');
        Admin::all()->each(function (Admin $admin) use ($maintenanceRequest, $customerName, $deviceModel) {
            Notification::create([
                'admin_id' => $admin->id,
                'type'     => 'new_maintenance_request',
                'title'    => 'طلب صيانة جديد',
                'body'     => "قدّم {$customerName} طلب صيانة لجهاز {$deviceModel}",
                'data'     => ['request_id' => $maintenanceRequest->id, 'shop_id' => $maintenanceRequest->shop_id],
            ]);
        });

        return response()->json([
            'message' => 'Maintenance request submitted successfully',
            'data'    => $maintenanceRequest,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        $requests = $customer->maintenanceRequests()
            ->with('shop:id,name,phone')
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'Maintenance requests retrieved successfully',
            'data'    => $requests,
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();

        $maintenanceRequest = $customer->maintenanceRequests()
            ->with(['shop:id,name,address,phone', 'parts'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Maintenance request details retrieved successfully',
            'data'    => $maintenanceRequest,
        ], 200);
    }

    public function parts(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();

        $maintenanceRequest = $customer->maintenanceRequests()
            ->with('parts')
            ->findOrFail($id);

        return response()->json([
            'message' => 'Parts retrieved successfully',
            'data'    => [
                'estimated_days' => $maintenanceRequest->estimated_days,
                'parts'          => $maintenanceRequest->parts,
            ],
        ], 200);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();

        $maintenanceRequest = $customer->maintenanceRequests()->findOrFail($id);

        if ($maintenanceRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot cancel request because it is no longer pending.',
            ], 400);
        }

        $maintenanceRequest->update(['status' => 'cancelled']);

        $this->notifyShopTechnicians(
            $maintenanceRequest->shop_id,
            'Request Cancelled',
            'The customer has cancelled their maintenance request.',
            ['type' => 'maintenance_request', 'id' => (string) $maintenanceRequest->id],
        );

        return response()->json([
            'message' => 'Maintenance request cancelled successfully',
            'data'    => $maintenanceRequest,
        ], 200);
    }

    public function approve(ApproveMaintenanceRequest $request, int $id): JsonResponse
    {
        $customer = $request->user();

        $maintenanceRequest = MaintenanceRequest::where('customer_id', $customer->id)
            ->where('status', 'waiting_customer_approval')
            ->findOrFail($id);

        $selectedPartIds = $request->validated('selected_parts');
        $parts           = $maintenanceRequest->parts()->get();

        $missingRequired = $parts->where('is_required', true)
            ->filter(fn ($p) => ! in_array($p->id, $selectedPartIds));

        if ($missingRequired->isNotEmpty()) {
            return response()->json([
                'message' => 'All required parts must be selected.',
            ], 422);
        }

        $validIds   = $parts->pluck('id')->toArray();
        $invalidIds = array_diff($selectedPartIds, $validIds);

        if (! empty($invalidIds)) {
            return response()->json([
                'message' => 'Invalid part IDs provided.',
            ], 422);
        }

        DB::transaction(function () use ($maintenanceRequest, $parts, $selectedPartIds, $request) {
            $selectedParts = $parts->filter(fn ($p) => in_array($p->id, $selectedPartIds));

            $parts->each(fn ($p) => $p->update(['is_selected' => in_array($p->id, $selectedPartIds)]));

            $selectedParts->each(function ($p) {
                if ($p->spare_part_id) {
                    SparePart::where('id', $p->spare_part_id)
                        ->decrement('stock_quantity', $p->quantity);
                }
            });

            $totalCost = $selectedParts->sum(fn ($p) => $p->price * $p->quantity);

            $maintenanceRequest->update([
                'estimated_cost'  => $totalCost,
                'payment_method'  => $request->validated('payment_method'),
                'customer_status' => 'approved',
                'status'          => 'approved',
            ]);

            Delivery::create([
                'type'                   => 'device_pickup',
                'status'                 => 'pending',
                'customer_id'            => $maintenanceRequest->customer_id,
                'shop_id'                => $maintenanceRequest->shop_id,
                'maintenance_request_id' => $maintenanceRequest->id,
                'payment_method'         => $request->validated('payment_method') === 'cash_on_delivery'
                                                ? 'cash_on_delivery'
                                                : 'prepaid',
            ]);
        });

        try {
            if ($customer->fcm_token) {
                $this->fcm->send(
                    $customer->fcm_token,
                    'تم إنشاء طلب التوصيل',
                    'سيتم توصيل طلبك قريباً.',
                    ['type' => 'delivery'],
                );
            }
        } catch (\Throwable) {}

        $this->notifyShopTechnicians(
            $maintenanceRequest->shop_id,
            'Diagnosis Approved',
            'The customer has approved the diagnosis. Repair can begin.',
            ['type' => 'maintenance_request', 'id' => (string) $maintenanceRequest->id],
        );

        return response()->json([
            'message' => 'Maintenance request approved',
            'data'    => $maintenanceRequest->fresh()->load('parts'),
        ], 200);
    }

    public function reject(RejectMaintenanceRequest $request, int $id): JsonResponse
    {
        $customer = $request->user();

        $maintenanceRequest = MaintenanceRequest::where('customer_id', $customer->id)
            ->where('status', 'waiting_customer_approval')
            ->findOrFail($id);

        $maintenanceRequest->update([
            'rejection_reason' => $request->validated('rejection_reason'),
            'customer_status'  => 'rejected',
            'status'           => 'cancelled',
        ]);

        $this->notifyShopTechnicians(
            $maintenanceRequest->shop_id,
            'Diagnosis Rejected',
            'The customer has rejected the diagnosis.',
            ['type' => 'maintenance_request', 'id' => (string) $maintenanceRequest->id],
        );

        return response()->json([
            'message' => 'Maintenance request has been rejected by the customer',
            'data'    => $maintenanceRequest,
        ], 200);
    }

    // -------------------------------------------------------------------------

    private function notifyShopTechnicians(int $shopId, string $title, string $body, array $data): void
    {
        $tokens = Technician::where('shop_id', $shopId)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        $this->fcm->sendMultiple($tokens, $title, $body, $data);
    }
}
