<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddToCartRequest;
use App\Models\Accessory;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();

        $cartItems = $customer->cartItems()->with('accessory.shop:id,name')->latest()->get();

        return response()->json([
            'message' => 'Cart retrieved successfully',
            'data' => $cartItems
        ], 200);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();
        $accessory = Accessory::findOrFail($request->validated('accessory_id'));
        $requestedQuantity = (int) $request->validated('quantity');

        if (!$accessory->is_active || $accessory->stock_quantity < $requestedQuantity) {
            return response()->json([
                'message' => 'Product is unavailable or insufficient stock.'
            ], 400);
        }

        $cartItem = $customer->cartItems()->where('accessory_id', $accessory->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $requestedQuantity;
            if ($accessory->stock_quantity < $newQuantity) {
                return response()->json(['message' => 'Insufficient stock for the requested total quantity.'], 400);
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            $cartItem = $customer->cartItems()->create($request->validated());
        }

        return response()->json([
            'message' => 'Item added to cart successfully',
            'data' => $cartItem->load('accessory:id,name,price')
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();
        
        $cartItem = $customer->cartItems()->findOrFail($id);
        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart successfully'
        ], 200);
    }
}