<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storage Files with CORS
|--------------------------------------------------------------------------
*/
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/'.$path);
    if (file_exists($filePath) && is_readable($filePath)) {
        $content = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        return response($content, 200, [
            'Content-Type' => $mimeType,
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
    abort(404);
})->middleware([])->where('path', '.*');

Route::get('/debug-products', function () {
    return app(\App\Http\Controllers\Api\ProductApiController::class)
        ->index(request());
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data');

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:view-products')->group(function () {
        Route::resource('products', ProductController::class)->only(['index', 'show']);
    });
    Route::middleware('permission:manage-products')->group(function () {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });
    Route::get('products/{product}/modal', [ProductController::class, 'getForModal'])->name('products.modal');

    /*
    |--------------------------------------------------------------------------
    | Warehouses
    |--------------------------------------------------------------------------
    */
    Route::prefix('warehouses')->name('warehouses.')->group(function () {

        // ===== MANAGE (CREATE MUST COME FIRST) =====
        Route::middleware('permission:manage-warehouses')->group(function () {
            Route::get('/create', [WarehouseController::class, 'create'])->name('create');
            Route::post('/', [WarehouseController::class, 'store'])->name('store');
            Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
            Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
            Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');

            Route::post('/{warehouse}/add-stock', [WarehouseController::class, 'addStock'])->name('add-stock');
            Route::post('/{warehouse}/remove-stock', [WarehouseController::class, 'removeStock'])->name('remove-stock');
            Route::post('/{warehouse}/update-stock', [WarehouseController::class, 'updateStock'])->name('update-stock');
            Route::post('/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{warehouse}/set-default', [WarehouseController::class, 'setDefault'])->name('set-default');
            Route::post('/{warehouse}/assign-users', [WarehouseController::class, 'assignUsers'])->name('assign-users');
        });

        // ===== VIEW (DYNAMIC ROUTES LAST) =====
        Route::middleware('permission:view-warehouses')->group(function () {
            Route::get('/', [WarehouseController::class, 'index'])->name('index');
            Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {

        // ===== MANAGE FIRST =====
        Route::middleware('permission:manage-users')->group(function () {
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        });

        // ===== VIEW LAST =====
        Route::middleware('permission:view-users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Purchase Orders
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:manage-inventory')->group(function () {
        Route::resource('purchase-orders', \App\Http\Controllers\PurchaseOrderController::class);
        Route::post('purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::patch('purchase-orders/{purchaseOrder}/status', [\App\Http\Controllers\PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
    });

    Route::get('/images/{path}', function ($path) {
        $fullPath = storage_path('app/public/products/'.$path);

        if (! file_exists($fullPath)) {
            $fullPath = public_path('images/product-default.svg');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Access-Control-Allow-Origin' => '*',
        ]);
    })->where('path', '.*');

});

/*
|--------------------------------------------------------------------------
| Orders (outside auth for testing)
|--------------------------------------------------------------------------
*/
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
Route::get('/orders/{order}/data', [OrderController::class, 'getData'])->name('orders.data');
Route::get('/orders/export/excel', [OrderController::class, 'exportExcel'])->name('orders.export.excel');
