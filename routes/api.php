<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CustomerAuthController;
use App\Http\Controllers\Api\V1\ShopController;
use App\Http\Controllers\Api\V1\MaintenanceRequestController;
use App\Http\Controllers\Api\V1\AccessoryController;

Route::prefix('v1/customer')->group(function () {
    
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [CustomerAuthController::class, 'profile']);
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::post('/delete-account', [CustomerAuthController::class, 'deleteAccount']);
    });
    
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Shops
    Route::post('/shops/nearest', [ShopController::class, 'nearest']);
    
    // Maintenance Requests
    Route::post('/maintenance-requests', [MaintenanceRequestController::class, 'store']);
    Route::get('/maintenance-requests', [MaintenanceRequestController::class, 'index']);
    Route::get('/maintenance-requests/{id}', [MaintenanceRequestController::class, 'show']);
    Route::post('/maintenance-requests/{id}/cancel', [MaintenanceRequestController::class, 'cancel']);

    // Accessories
    Route::get('/accessories', [AccessoryController::class, 'index']);
    Route::get('/accessories/{id}', [AccessoryController::class, 'show']);
    
    // Cart
    Route::get('/cart', [\App\Http\Controllers\Api\V1\CartController::class, 'index']);
    Route::post('/cart', [\App\Http\Controllers\Api\V1\CartController::class, 'store']);
    Route::delete('/cart/{id}', [\App\Http\Controllers\Api\V1\CartController::class, 'destroy']);

    // Orders & Checkout
    Route::post('/checkout', [\App\Http\Controllers\Api\V1\OrderController::class, 'checkout']);
    Route::get('/orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'index']);
    
});