<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\SupplierPurchaseApiController;
use App\Http\Controllers\Api\WarehouseApiController;
use App\Http\Controllers\Api\WarehouseInventoryController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/products', [ProductApiController::class, 'index']);

    // Product detail view - all authenticated users (with warehouse access check in controller)
    Route::get('/products/{product}', [ProductApiController::class, 'show']);

    // Product CRUD operations - admin only
    Route::middleware('permission:manage-products')->group(function () {
        Route::post('/products', [ProductApiController::class, 'store']);
        Route::put('/products/{product}', [ProductApiController::class, 'update']);
        Route::delete('/products/{product}', [ProductApiController::class, 'destroy']);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/brands', [BrandController::class, 'index']);

    // warehouses
    Route::get('/warehouses', [WarehouseApiController::class, 'index']);

    // CHECKOUT (STAFF + ADMIN, MOBILE ONLY FOR STAFF)
    Route::post('/checkout', [CheckoutController::class, 'checkout'])
        ->middleware(['permission:checkout,sanctum', 'mobile.only']);

    // ANALYTICS (ADMIN ONLY)
    Route::middleware('permission:view-analytics,sanctum')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('/analytics/summary', [AnalyticsController::class, 'summary']);
        Route::get('/analytics/sales-chart', [AnalyticsController::class, 'salesChart']);
        Route::get('/analytics/trending', [AnalyticsController::class, 'trending']);
    });

    // WAREHOUSE INVENTORY MANAGEMENT (ADMIN ONLY)
    Route::middleware('permission:manage-inventory,sanctum')->group(function () {
        Route::post('/warehouse-inventory/add', [WarehouseInventoryController::class, 'addProductToWarehouse']);
        Route::get('/warehouse-inventory/{warehouseId}', [WarehouseInventoryController::class, 'getWarehouseInventory']);
        Route::put('/warehouse-inventory/{warehouseId}/product/{productId}', [WarehouseInventoryController::class, 'updateProductQuantity']);
    });

    // SUPPLIER PURCHASE MANAGEMENT (ADMIN ONLY)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('supplier-purchases', SupplierPurchaseApiController::class)
    ->names('api.supplier-purchases');
        Route::post('supplier-purchases/{purchase}/confirm', [SupplierPurchaseApiController::class, 'confirm']);
    });
});
