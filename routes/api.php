<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\WarehouseApiController;
use App\Http\Controllers\Api\WarehouseInventoryController;
use App\Http\Controllers\Api\PurchaseOrderApiController;
use App\Http\Controllers\Api\OrderApiController;
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
        Route::get('/purchase-orders', [PurchaseOrderApiController::class, 'index']);
        Route::post('/purchase-orders', [PurchaseOrderApiController::class, 'store']);
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'show']);
        Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'update']);
        Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderApiController::class, 'destroy']);
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderApiController::class, 'receive']);
        Route::patch('/purchase-orders/{purchaseOrder}/status', [PurchaseOrderApiController::class, 'updateStatus']);
    });

    // User's Purchase Orders (STAFF + ADMIN)
    Route::get('/my-purchase-orders', [PurchaseOrderApiController::class, 'myOrders']);

    // ORDERS (Mobile App - Customer + Admin/Staff)
    Route::get('/orders', [OrderApiController::class, 'index'])
        ->middleware('permission:view-orders,sanctum');
    Route::get('/orders/{id}', [OrderApiController::class, 'show']);
    Route::patch('/orders/{id}/status', [OrderApiController::class, 'updateStatus'])
        ->middleware('permission:manage-orders,sanctum');
    Route::get('/orders/stats', [OrderApiController::class, 'stats'])
        ->middleware('permission:view-analytics,sanctum');

    // Customer orders (mobile app)
    Route::get('/my-orders', [OrderApiController::class, 'myOrders']);
    Route::post('/orders', [OrderApiController::class, 'store']); // Create order
    Route::post('/orders/{id}/cancel', [OrderApiController::class, 'cancel']);
});
