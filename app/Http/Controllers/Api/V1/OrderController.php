<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\CheckoutRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderController extends Controller
{
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();
        $cartItems = $customer->cartItems()->with('accessory')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        try {
            $orders = DB::transaction(function () use ($customer, $cartItems, $request) {
                
                $groupedCartItems = $cartItems->groupBy(function ($item) {
                    return $item->accessory->shop_id;
                });

                $createdOrders = [];

                foreach ($groupedCartItems as $shopId => $items) {
                    $totalAmount = $items->sum(function ($item) {
                        return $item->quantity * $item->accessory->price;
                    });

                    $order = $customer->orders()->create([
                        'shop_id' => $shopId,
                        'total_amount' => $totalAmount,
                        'payment_method' => $request->validated('payment_method'),
                        'status' => 'pending'
                    ]);

                    foreach ($items as $item) {
                        if ($item->accessory->stock_quantity < $item->quantity) {
                            throw new Exception("Insufficient stock for item: " . $item->accessory->name);
                        }

                        $order->items()->create([
                            'accessory_id' => $item->accessory_id,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->accessory->price,
                        ]);

                        $item->accessory->decrement('stock_quantity', $item->quantity);
                    }

                    $createdOrders[] = $order->load('items.accessory:id,name', 'shop:id,name');
                }

                $customer->cartItems()->delete();

                return $createdOrders;
            });

            return response()->json([
                'message' => 'Checkout completed successfully',
                'data' => $orders
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 400);
        }
    }

    public function index(): JsonResponse
    {
        /** @var \App\Models\Customer $customer */
        $customer = auth()->user();

        $orders = $customer->orders()
            ->with(['shop:id,name', 'items.accessory:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data' => $orders
        ], 200);
    }
}