<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreShopRequest;
use App\Http\Requests\Api\V1\Admin\UpdateShopRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    public function index(): JsonResponse
    {
        $shops = Shop::withCount('maintenanceRequests')->latest()->paginate(15);
        $shops->getCollection()->transform(function ($shop) {
            $shop->image_url    = $shop->image_path ? url('storage/' . $shop->image_path) : null;
            $shop->orders_count = $shop->maintenance_requests_count;
            return $shop;
        });

        return response()->json([
            'message' => 'Shops retrieved successfully',
            'data'    => $shops,
        ], 200);
    }

    public function store(StoreShopRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('shops', 'public');
        }

        $shop            = Shop::create($validated);
        $shop->image_url = $shop->image_path ? url('storage/' . $shop->image_path) : null;

        return response()->json([
            'message' => 'Shop created successfully',
            'data'    => $shop,
        ], 201);
    }

    public function update(UpdateShopRequest $request, int $id): JsonResponse
    {
        $shop      = Shop::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($shop->image_path) {
                Storage::disk('public')->delete($shop->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('shops', 'public');
        }

        $shop->update($validated);
        $shop->image_url = $shop->image_path ? url('storage/' . $shop->image_path) : null;

        return response()->json([
            'message' => 'Shop updated successfully',
            'data'    => $shop,
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $shop = Shop::findOrFail($id);

        if ($shop->image_path) {
            Storage::disk('public')->delete($shop->image_path);
        }

        $shop->delete();

        return response()->json([
            'message' => 'Shop deleted successfully',
        ], 200);
    }
}
