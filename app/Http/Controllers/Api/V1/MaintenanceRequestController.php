<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Maintenance\StoreMaintenanceRequest;
use App\Models\MaintenanceRequest;
use Illuminate\Http\JsonResponse;

class MaintenanceRequestController extends Controller
{
    public function store(StoreMaintenanceRequest $request): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();

        $maintenanceRequest = $customer->maintenanceRequests()->create($request->validated());

        return response()->json([
            'message' => 'Maintenance request submitted successfully',
            'data' => $maintenanceRequest
        ], 201);
    }

    public function index(): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();
        
        $requests = $customer->maintenanceRequests()
            ->with('shop:id,name,phone')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Maintenance requests retrieved successfully',
            'data' => $requests
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();
        
        $maintenanceRequest = $customer->maintenanceRequests()
            ->with('shop:id,name,address,phone')
            ->findOrFail($id);

        return response()->json([
            'message' => 'Maintenance request details retrieved successfully',
            'data' => $maintenanceRequest
        ], 200);
    }

    public function cancel(int $id): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();
        
        $maintenanceRequest = $customer->maintenanceRequests()->findOrFail($id);

        if ($maintenanceRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot cancel request because it is no longer pending.'
            ], 400);
        }

        $maintenanceRequest->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Maintenance request cancelled successfully',
            'data' => $maintenanceRequest
        ], 200);
    }
}