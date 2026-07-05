<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Technician\DiagnoseRequest;
use App\Http\Requests\Api\V1\Technician\UpdateStatusRequest;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\SparePart;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceRequestController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}

    public function index(Request $request): JsonResponse
    {
        $technician = $request->user();

        $requests = MaintenanceRequest::where('shop_id', $technician->shop_id)
            ->with(['customer'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'message' => 'Maintenance requests retrieved successfully',
            'data'    => $requests,
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $technician = $request->user();

        $maintenanceRequest = MaintenanceRequest::where('shop_id', $technician->shop_id)
            ->with(['customer', 'parts'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Maintenance request details retrieved successfully',
            'data'    => $maintenanceRequest,
        ], 200);
    }

    public function updateStatus(UpdateStatusRequest $request, int $id): JsonResponse
    {
        $technician = $request->user();

        $maintenanceRequest = MaintenanceRequest::where('shop_id', $technician->shop_id)
            ->with('customer')
            ->findOrFail($id);

        $newStatus = $request->validated('status');
        $maintenanceRequest->update(['status' => $newStatus]);

        [$title, $body] = match ($newStatus) {
            'under_inspection' => [
                'Request Under Inspection',
                'Your device is now being inspected by a technician.',
            ],
            'completed' => [
                'Repair Completed',
                'Your device repair has been completed!',
            ],
            default => [null, null],
        };

        if ($title && $maintenanceRequest->customer) {
            if ($maintenanceRequest->customer->fcm_token) {
                $this->fcm->send(
                    $maintenanceRequest->customer->fcm_token,
                    $title,
                    $body,
                    ['type' => 'maintenance_request', 'id' => (string) $maintenanceRequest->id],
                );
            }

            Notification::create([
                'customer_id' => $maintenanceRequest->customer_id,
                'type'        => 'maintenance_request',
                'title'       => $title,
                'body'        => $body,
                'data'        => ['request_id' => $maintenanceRequest->id],
            ]);
        }

        return response()->json([
            'message' => 'Maintenance request status updated successfully',
            'data'    => $maintenanceRequest,
        ], 200);
    }

    public function diagnose(DiagnoseRequest $request, int $id): JsonResponse
    {
        $technician = $request->user();

        $maintenanceRequest = MaintenanceRequest::where('shop_id', $technician->shop_id)
            ->with('customer')
            ->findOrFail($id);

        DB::transaction(function () use ($maintenanceRequest, $request) {
            $maintenanceRequest->parts()->delete();

            foreach ($request->validated('parts') as $part) {
                $sparePart = SparePart::find($part['spare_part_id']);

                $maintenanceRequest->parts()->create([
                    'spare_part_id' => $sparePart->id,
                    'name'          => $sparePart->name,
                    'price'         => $sparePart->price,
                    'quantity'      => $part['quantity'],
                    'is_required'   => $part['is_required'],
                    'is_selected'   => false,
                ]);
            }

            $maintenanceRequest->update([
                'estimated_days'  => $request->validated('estimated_days'),
                'customer_status' => 'pending_approval',
                'status'          => 'waiting_customer_approval',
            ]);
        });

        if ($maintenanceRequest->customer) {
            if ($maintenanceRequest->customer->fcm_token) {
                $this->fcm->send(
                    $maintenanceRequest->customer->fcm_token,
                    'Diagnosis Ready',
                    'Your device has been diagnosed. Please review the parts and approve.',
                    ['type' => 'maintenance_request', 'id' => (string) $maintenanceRequest->id],
                );
            }

            Notification::create([
                'customer_id' => $maintenanceRequest->customer_id,
                'type'        => 'maintenance_request',
                'title'       => 'Diagnosis Ready',
                'body'        => 'Your device has been diagnosed. Please review the parts and approve.',
                'data'        => ['request_id' => $maintenanceRequest->id],
            ]);
        }

        return response()->json([
            'message' => 'Diagnosis submitted successfully',
            'data'    => $maintenanceRequest->fresh()->load('parts'),
        ], 200);
    }
}
