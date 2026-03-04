<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\WarehouseApiController;
use App\Http\Controllers\Api\WarehouseInventoryController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);

    // PRODUCTS (ADMIN ONLY)
    Route::middleware('permission:manage-products')->group(function () {
        Route::post('/products', [ProductApiController::class, 'store']);
    });

    // inventory
    Route::get('/products', [ProductApiController::class, 'index']);

    // warehouses
    Route::get('/warehouses', [WarehouseApiController::class, 'index']);

    // CHECKOUT (STAFF + ADMIN, MOBILE ONLY FOR STAFF)
    Route::post('/checkout', [CheckoutController::class, 'checkout'])
        ->middleware(['permission:checkout', 'mobile.only']);

    // ANALYTICS (ADMIN ONLY)
    Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);

    Route::get('/analytics/summary', [AnalyticsController::class, 'summary']);
    Route::get('/analytics/sales-chart', [AnalyticsController::class, 'salesChart']);
    Route::get('/analytics/trending', [AnalyticsController::class, 'trending']);

    // WAREHOUSE INVENTORY MANAGEMENT (ADMIN ONLY)
    Route::middleware('permission:manage-inventory')->group(function () {
        Route::post('/warehouse-inventory/add', [WarehouseInventoryController::class, 'addProductToWarehouse']);
        Route::get('/warehouse-inventory/{warehouseId}', [WarehouseInventoryController::class, 'getWarehouseInventory']);
        Route::put('/warehouse-inventory/{warehouseId}/product/{productId}', [WarehouseInventoryController::class, 'updateProductQuantity']);
    });
});
