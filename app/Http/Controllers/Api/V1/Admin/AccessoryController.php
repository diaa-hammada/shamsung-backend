<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreAccessoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateAccessoryRequest;
use App\Models\Accessory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AccessoryController extends Controller
{
    public function index(): JsonResponse
    {
        $accessories = Accessory::with('shop:id,name')->latest()->paginate(15);

        return response()->json([
            'message' => 'Accessories inventory retrieved successfully',
            'data'    => $accessories,
        ], 200);
    }

    public function store(StoreAccessoryRequest $request): JsonResponse
    {
        $validated              = $request->validated();
        $validated['is_active'] = $validated['is_active'] ?? true;

        if ($request->hasFile('image')) {
            $path                   = $request->file('image')->store('accessories', 'public');
            $validated['image_url'] = url('storage/' . $path);
        }

        unset($validated['image']);

        $accessory = Accessory::create($validated);

        return response()->json([
            'message' => 'Accessory created successfully',
            'data'    => $accessory->load('shop:id,name'),
        ], 201);
    }

    public function update(UpdateAccessoryRequest $request, int $id): JsonResponse
    {
        $accessory = Accessory::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($accessory->image_url && str_contains($accessory->image_url, 'storage/')) {
                $oldPath = explode('storage/', $accessory->image_url)[1];
                Storage::disk('public')->delete($oldPath);
            }

            $path                   = $request->file('image')->store('accessories', 'public');
            $validated['image_url'] = url('storage/' . $path);
        }

        unset($validated['image']);

        $accessory->update($validated);

        return response()->json([
            'message' => 'Accessory updated successfully',
            'data'    => $accessory->load('shop:id,name'),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $accessory = Accessory::findOrFail($id);

        if ($accessory->image_url && str_contains($accessory->image_url, 'storage/')) {
            $oldPath = explode('storage/', $accessory->image_url)[1];
            Storage::disk('public')->delete($oldPath);
        }

        $accessory->delete();

        return response()->json([
            'message' => 'Accessory deleted successfully',
        ], 200);
    }
}
