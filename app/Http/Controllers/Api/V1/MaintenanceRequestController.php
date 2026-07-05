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

        $customerName = trim($customer->first_name . ' ' . $customer->last_name);
        $deviceModel  = $request->validated('device_model');

        $this->notifyShopTechnicians(
            $maintenanceRequest->shop_id,
            'طلب صيانة جديد',
            "طلب صيانة جديد لجهاز {$deviceModel} من {$customerName} بانتظار الفحص",
            ['type' => 'maintenance_request', 'id' => (string) $maintenanceRequest->id],
        );

        $adminTitle = 'طلب صيانة جديد';
        $adminBody  = "قدّم {$customerName} طلب صيانة لجهاز {$deviceModel}";
        $adminData  = ['type' => 'new_maintenance_request', 'id' => (string) $maintenanceRequest->id];

        Admin::all()->each(function (Admin $admin) use ($maintenanceRequest, $adminTitle, $adminBody, $adminData) {
            Notification::create([
                'admin_id' => $admin->id,
                'type'     => 'new_maintenance_request',
                'title'    => $adminTitle,
                'body'     => $adminBody,
                'data'     => ['request_id' => $maintenanceRequest->id, 'shop_id' => $maintenanceRequest->shop_id],
            ]);

            if ($admin->fcm_token) {
                $this->fcm->send($admin->fcm_token, $adminTitle, $adminBody, $adminData);
            }
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
            'تم إلغاء طلب الصيانة',
            'قام العميل بإلغاء طلب الصيانة الخاص به.',
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
                'latitude'               => $request->validated('latitude'),
                'longitude'              => $request->validated('longitude'),
                'address'                => $request->validated('address'),
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

        Notification::create([
            'customer_id' => $customer->id,
            'type'        => 'delivery',
            'title'       => 'تم إنشاء طلب التوصيل',
            'body'        => 'سيتم توصيل طلبك قريباً.',
            'data'        => ['request_id' => $maintenanceRequest->id],
        ]);

        $this->notifyShopTechnicians(
            $maintenanceRequest->shop_id,
            'تمت الموافقة على التشخيص',
            'وافق العميل على التشخيص. يمكن البدء بالإصلاح.',
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
            'تم رفض التشخيص',
            'قام العميل برفض التشخيص المقترح.',
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
        $technicians = Technician::where('shop_id', $shopId)->get(['id', 'fcm_token']);

        // In-app (DB) notifications — power the dashboard bell regardless of FCM token.
        $technicians->each(function (Technician $tech) use ($title, $body, $data) {
            Notification::create([
                'technician_id' => $tech->id,
                'type'          => $data['type'] ?? 'maintenance_request',
                'title'         => $title,
                'body'          => $body,
                'data'          => $data,
            ]);
        });

        // FCM push — only reaches devices that registered a token.
        $tokens = $technicians->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        $this->fcm->sendMultiple($tokens, $title, $body, $data);
    }
}
