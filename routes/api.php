<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CustomerAuthController;
use App\Http\Controllers\Api\V1\ShopController;

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
    Route::post('/shops/nearest', [ShopController::class, 'nearest']);
    Route::post('/maintenance-requests', [\App\Http\Controllers\Api\V1\MaintenanceRequestController::class, 'store']);
    Route::get('/maintenance-requests', [\App\Http\Controllers\Api\V1\MaintenanceRequestController::class, 'index']);
    Route::get('/maintenance-requests/{id}', [\App\Http\Controllers\Api\V1\MaintenanceRequestController::class, 'show']);
    Route::post('/maintenance-requests/{id}/cancel', [\App\Http\Controllers\Api\V1\MaintenanceRequestController::class, 'cancel']);
});