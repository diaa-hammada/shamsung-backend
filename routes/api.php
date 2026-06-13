<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CustomerAuthController;
use App\Http\Controllers\Api\V1\ShopController;
use App\Http\Controllers\Api\V1\MaintenanceRequestController;
use App\Http\Controllers\Api\V1\AccessoryController;

Route::prefix('v1/customer')->group(function () {
    Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp']);       // STEP 1
    Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp']);   // STEP 2
    Route::post('/register', [CustomerAuthController::class, 'register']);      // STEP 3

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::get('/profile', [CustomerAuthController::class, 'profile']);
        Route::post('/delete-account', [CustomerAuthController::class, 'deleteAccount']);
        Route::post('/fcm-token', [CustomerAuthController::class, 'updateFcmToken']);

        Route::get('/deliveries', [\App\Http\Controllers\Api\V1\Customer\DeliveryController::class, 'index']);
        Route::get('/deliveries/{id}', [\App\Http\Controllers\Api\V1\Customer\DeliveryController::class, 'show']);
    });
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/shops/nearest', [ShopController::class, 'nearest']);
    
    Route::post('/maintenance-requests', [MaintenanceRequestController::class, 'store']);
    Route::get('/maintenance-requests', [MaintenanceRequestController::class, 'index']);
    Route::get('/maintenance-requests/{id}', [MaintenanceRequestController::class, 'show']);
    Route::get('/maintenance-requests/{id}/parts', [MaintenanceRequestController::class, 'parts']);
    Route::post('/maintenance-requests/{id}/cancel', [MaintenanceRequestController::class, 'cancel']);
    Route::post('/maintenance-requests/{id}/approve', [MaintenanceRequestController::class, 'approve']);
    Route::post('/maintenance-requests/{id}/reject', [MaintenanceRequestController::class, 'reject']);

    Route::get('/accessories', [AccessoryController::class, 'index']);
    Route::get('/accessories/{id}', [AccessoryController::class, 'show']);
    
    Route::get('/cart', [\App\Http\Controllers\Api\V1\CartController::class, 'index']);
    Route::post('/cart', [\App\Http\Controllers\Api\V1\CartController::class, 'store']);
    Route::delete('/cart/{id}', [\App\Http\Controllers\Api\V1\CartController::class, 'destroy']);

    Route::post('/checkout', [\App\Http\Controllers\Api\V1\OrderController::class, 'checkout']);
    Route::get('/orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'index']);

    Route::post('/consultations', [\App\Http\Controllers\Api\V1\Customer\ConsultationController::class, 'store']);
    Route::get('/consultations', [\App\Http\Controllers\Api\V1\Customer\ConsultationController::class, 'index']);
});

Route::prefix('v1/technician')->group(function () {
    Route::post('/send-otp', [\App\Http\Controllers\Api\V1\TechnicianAuthController::class, 'sendOtp']);    // STEP 1
    Route::post('/verify-otp', [\App\Http\Controllers\Api\V1\TechnicianAuthController::class, 'verifyOtp']); // STEP 2

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\V1\TechnicianAuthController::class, 'logout']);
        Route::get('/profile', [\App\Http\Controllers\Api\V1\TechnicianAuthController::class, 'profile']);
        Route::post('/fcm-token', [\App\Http\Controllers\Api\V1\TechnicianAuthController::class, 'updateFcmToken']);

        Route::get('/maintenance-requests', [\App\Http\Controllers\Api\V1\Technician\MaintenanceRequestController::class, 'index']);
        Route::get('/maintenance-requests/{id}', [\App\Http\Controllers\Api\V1\Technician\MaintenanceRequestController::class, 'show']);
        Route::post('/maintenance-requests/{id}/status', [\App\Http\Controllers\Api\V1\Technician\MaintenanceRequestController::class, 'updateStatus']);
        Route::post('/maintenance-requests/{id}/diagnose', [\App\Http\Controllers\Api\V1\Technician\MaintenanceRequestController::class, 'diagnose']);
        Route::get('/spare-parts', [\App\Http\Controllers\Api\V1\Technician\SparePartController::class, 'index']);

        Route::get('/consultations', [\App\Http\Controllers\Api\V1\Technician\ConsultationController::class, 'index']);
        Route::post('/consultations/{id}/reply', [\App\Http\Controllers\Api\V1\Technician\ConsultationController::class, 'reply']);
    });
});

Route::prefix('v1/delivery')->group(function () {
    Route::post('/send-otp', [\App\Http\Controllers\Api\V1\DeliveryAuthController::class, 'sendOtp']);    // STEP 1
    Route::post('/verify-otp', [\App\Http\Controllers\Api\V1\DeliveryAuthController::class, 'verifyOtp']); // STEP 2

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\V1\DeliveryAuthController::class, 'logout']);
        Route::get('/profile', [\App\Http\Controllers\Api\V1\DeliveryAuthController::class, 'profile']);
        Route::post('/fcm-token', [\App\Http\Controllers\Api\V1\DeliveryAuthController::class, 'updateFcmToken']);

        Route::get('/requests', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'index']);
        Route::get('/requests/{id}', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'show']);
        Route::post('/requests/{id}/accept', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'accept']);
        Route::post('/requests/{id}/reject', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'reject']);
        Route::post('/requests/{id}/status', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'updateStatus']);
        Route::post('/requests/{id}/confirm', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'confirm']);
        Route::post('/requests/{id}/collect-cash', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'collectCash']);
        Route::get('/history', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'history']);
        Route::get('/earnings', [\App\Http\Controllers\Api\V1\Delivery\DeliveryController::class, 'earnings']);
    });
});

Route::prefix('v1/admin')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\V1\Admin\AdminAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Api\V1\Admin\AdminAuthController::class, 'profile']);
        Route::post('/profile', [\App\Http\Controllers\Api\V1\Admin\AdminAuthController::class, 'updateProfile']);
        Route::post('/change-password', [\App\Http\Controllers\Api\V1\Admin\AdminAuthController::class, 'changePassword']);
        Route::post('/logout', [\App\Http\Controllers\Api\V1\Admin\AdminAuthController::class, 'logout']);

        // Shops
        Route::get('/shops', [\App\Http\Controllers\Api\V1\Admin\ShopController::class, 'index']);
        Route::post('/shops', [\App\Http\Controllers\Api\V1\Admin\ShopController::class, 'store']);
        Route::put('/shops/{id}', [\App\Http\Controllers\Api\V1\Admin\ShopController::class, 'update']);
        Route::delete('/shops/{id}', [\App\Http\Controllers\Api\V1\Admin\ShopController::class, 'destroy']);

        // Technicians
        Route::get('/technicians', [\App\Http\Controllers\Api\V1\Admin\TechnicianController::class, 'index']);
        Route::post('/technicians', [\App\Http\Controllers\Api\V1\Admin\TechnicianController::class, 'store']);
        Route::post('/technicians/{id}', [\App\Http\Controllers\Api\V1\Admin\TechnicianController::class, 'update']);
        Route::delete('/technicians/{id}', [\App\Http\Controllers\Api\V1\Admin\TechnicianController::class, 'destroy']);

        // Spare Parts & Stock Requests (FR-29, FR-30)
        Route::get('/spare-parts', [\App\Http\Controllers\Api\V1\Admin\SparePartController::class, 'index']);
        Route::post('/spare-parts', [\App\Http\Controllers\Api\V1\Admin\SparePartController::class, 'store']);
        Route::put('/spare-parts/{id}', [\App\Http\Controllers\Api\V1\Admin\SparePartController::class, 'update']);
        Route::delete('/spare-parts/{id}', [\App\Http\Controllers\Api\V1\Admin\SparePartController::class, 'destroy']);
        Route::get('/stock-requests', [\App\Http\Controllers\Api\V1\Admin\SparePartController::class, 'indexStockRequests']);
        Route::post('/stock-requests', [\App\Http\Controllers\Api\V1\Admin\SparePartController::class, 'requestStock']);
        Route::post('/stock-requests/{id}/approve', [\App\Http\Controllers\Api\V1\Admin\SparePartController::class, 'approveStockRequest']);

        // Accessories Inventory (FR-31)
        Route::get('/accessories', [\App\Http\Controllers\Api\V1\Admin\AccessoryController::class, 'index']);
        Route::post('/accessories', [\App\Http\Controllers\Api\V1\Admin\AccessoryController::class, 'store']);
        Route::put('/accessories/{id}', [\App\Http\Controllers\Api\V1\Admin\AccessoryController::class, 'update']);
        Route::delete('/accessories/{id}', [\App\Http\Controllers\Api\V1\Admin\AccessoryController::class, 'destroy']);

        // Delivery Workers
        Route::get('/delivery-workers', [\App\Http\Controllers\Api\V1\Admin\DeliveryWorkerController::class, 'index']);
        Route::post('/delivery-workers', [\App\Http\Controllers\Api\V1\Admin\DeliveryWorkerController::class, 'store']);
        Route::put('/delivery-workers/{id}', [\App\Http\Controllers\Api\V1\Admin\DeliveryWorkerController::class, 'update']);
        Route::delete('/delivery-workers/{id}', [\App\Http\Controllers\Api\V1\Admin\DeliveryWorkerController::class, 'destroy']);

        // Dashboard
        Route::get('/dashboard/stats', [\App\Http\Controllers\Api\V1\Admin\AdminDashboardController::class, 'stats']);

        // Maintenance Requests (admin view — all shops)
        Route::get('/maintenance-requests', [\App\Http\Controllers\Api\V1\Admin\AdminMaintenanceRequestController::class, 'index']);

        // Orders (accessory purchases)
        Route::get('/orders', [\App\Http\Controllers\Api\V1\Admin\AdminOrderController::class, 'index']);

        // Deliveries
        Route::get('/deliveries', [\App\Http\Controllers\Api\V1\Admin\AdminDeliveryController::class, 'index']);
        Route::post('/deliveries', [\App\Http\Controllers\Api\V1\Admin\AdminDeliveryController::class, 'store']);
        Route::patch('/deliveries/{id}', [\App\Http\Controllers\Api\V1\Admin\AdminDeliveryController::class, 'update']);
    });
});