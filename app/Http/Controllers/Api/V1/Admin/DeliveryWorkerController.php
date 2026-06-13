<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreDeliveryWorkerRequest;
use App\Http\Requests\Api\V1\Admin\UpdateDeliveryWorkerRequest;
use App\Models\DeliveryWorker;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;

class DeliveryWorkerController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}

    public function index(): JsonResponse
    {
        $workers = DeliveryWorker::with('shop:id,name')->latest()->paginate(15);

        return response()->json([
            'message' => 'Delivery workers retrieved successfully',
            'data'    => $workers,
        ], 200);
    }

    public function store(StoreDeliveryWorkerRequest $request): JsonResponse
    {
        $validated              = $request->validated();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $worker = DeliveryWorker::create($validated);

        return response()->json([
            'message' => 'Delivery worker created successfully',
            'data'    => $worker->load('shop:id,name'),
        ], 201);
    }

    public function update(UpdateDeliveryWorkerRequest $request, int $id): JsonResponse
    {
        $worker    = DeliveryWorker::findOrFail($id);
        $wasActive = $worker->is_active;
        $validated = $request->validated();

        $worker->update($validated);

        if (
            $worker->fcm_token
            && array_key_exists('is_active', $validated)
            && $wasActive !== $worker->is_active
        ) {
            [$title, $body] = $worker->is_active
                ? ['Account Activated', 'Your account has been activated by the admin.']
                : ['Account Deactivated', 'Your account has been deactivated by the admin.'];

            $this->fcm->send(
                $worker->fcm_token,
                $title,
                $body,
                ['type' => 'account_status', 'is_active' => $worker->is_active ? '1' : '0'],
            );
        }

        return response()->json([
            'message' => 'Delivery worker updated successfully',
            'data'    => $worker->load('shop:id,name'),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $worker = DeliveryWorker::findOrFail($id);
        $worker->delete();

        return response()->json([
            'message' => 'Delivery worker deleted successfully',
        ], 200);
    }
}
