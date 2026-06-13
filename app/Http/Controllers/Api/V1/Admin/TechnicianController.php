<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreTechnicianRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTechnicianRequest;
use App\Models\Technician;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;

class TechnicianController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}

    public function index(): JsonResponse
    {
        $technicians = Technician::with('shop:id,name')->latest()->paginate(15);

        return response()->json([
            'message' => 'Technicians retrieved successfully',
            'data'    => $technicians,
        ], 200);
    }

    public function store(StoreTechnicianRequest $request): JsonResponse
    {
        $validated              = $request->validated();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $technician = Technician::create($validated);

        return response()->json([
            'message' => 'Technician created successfully',
            'data'    => $technician->load('shop:id,name'),
        ], 201);
    }

    public function update(UpdateTechnicianRequest $request, int $id): JsonResponse
    {
        $technician    = Technician::findOrFail($id);
        $wasActive     = $technician->is_active;
        $validated     = $request->validated();

        $technician->update($validated);

        // Notify technician if their activation status changed
        if (
            $technician->fcm_token
            && array_key_exists('is_active', $validated)
            && $wasActive !== $technician->is_active
        ) {
            [$title, $body] = $technician->is_active
                ? ['Account Activated', 'Your account has been activated by the admin.']
                : ['Account Deactivated', 'Your account has been deactivated by the admin.'];

            $this->fcm->send(
                $technician->fcm_token,
                $title,
                $body,
                ['type' => 'account_status', 'is_active' => $technician->is_active ? '1' : '0'],
            );
        }

        return response()->json([
            'message' => 'Technician updated successfully',
            'data'    => $technician->load('shop:id,name'),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $technician = Technician::findOrFail($id);
        $technician->delete();

        return response()->json([
            'message' => 'Technician deleted successfully',
        ], 200);
    }
}
