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

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'server' => 'running'
    ]);
})->name('health');

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

Route::get('/debug/images', function() {
    echo "<h1>🔍 Image Upload Diagnostic Report</h1>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;margin:20px 0;}th,td{padding:8px;border:1px solid #ddd;text-align:left;}th{background:#f4f4f4;}.success{background:#d4edda;}.error{background:#f8d7da;}.warning{background:#fff3cd;}code{background:#f8f9fa;padding:2px 4px;border-radius:3px;}</style>";
    
    // 1. Check Storage Directory
    echo "<h2>📁 Storage Directory Check</h2>";
    $storagePath = storage_path('app/public/products');
    $publicStoragePath = public_path('storage/products');
    $symlinkExists = is_link(public_path('storage')) || (file_exists(public_path('storage')) && is_dir(public_path('storage')));
    
    echo "<table>";
    echo "<tr><th>Check</th><th>Result</th><th>Status</th></tr>";
    
    echo "<tr><td>Storage directory exists</td><td><code>{$storagePath}</code></td><td>" . (is_dir($storagePath) ? '<span style="color:green">✅ YES</span>' : '<span style="color:red">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Storage directory writable</td><td><code>{$storagePath}</code></td><td>" . (is_writable($storagePath) ? '<span style="color:green">✅ YES</span>' : '<span style="color:red">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Public storage symlink exists</td><td><code>public/storage</code></td><td>" . ($symlinkExists ? '<span style="color:green">✅ YES</span>' : '<span style="color:red">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Symlink target exists</td><td><code>storage/app/public</code></td><td>" . (is_dir(storage_path('app/public')) ? '<span style="color:green">✅ YES</span>' : '<span style="color:red">❌ NO</span>') . "</td></tr>";
    
    if ($symlinkExists) {
        $symlinkTarget = readlink(public_path('storage'));
        echo "<tr><td>Symlink points to</td><td><code>{$symlinkTarget}</code></td><td>" . ($symlinkTarget === '../storage/app/public' ? '<span style="color:green">✅ CORRECT</span>' : '<span style="color:orange">⚠️ CHECK</span>') . "</td></tr>";
    }
    
    echo "</table>";
    
    // 2. Check Files in Storage
    echo "<h2>📸 Files in Storage</h2>";
    $files = glob($storagePath . '/*');
    if (empty($files)) {
        echo "<p style='color:orange'>⚠️ No files found in storage/app/public/products/</p>";
    } else {
        echo "<table>";
        echo "<tr><th>File</th><th>Size</th><th>Permissions</th><th>Readable</th></tr>";
        foreach ($files as $file) {
            $filename = basename($file);
            $size = number_format(filesize($file) / 1024, 2) . ' KB';
            $perms = substr(sprintf('%o', fileperms($file)), -4);
            $readable = is_readable($file) ? '✅' : '❌';
            echo "<tr><td><code>{$filename}</code></td><td>{$size}</td><td><code>{$perms}</code></td><td>{$readable}</td></tr>";
        }
        echo "</table>";
    }
    
    // 3. Check Database
    echo "<h2>🗄️ Database Check</h2>";
    try {
        $products = DB::table('products')->whereNotNull('image_path')->get();
        $productImages = DB::table('product_images')->get();
        
        echo "<table>";
        echo "<tr><th>Table</th><th>Records</th><th>Status</th></tr>";
        echo "<tr><td>Products with image_path</td><td>{$products->count()}</td><td>" . ($products->count() > 0 ? '<span style="color:green">✅</span>' : '<span style="color:orange">⚠️</span>') . "</td></tr>";
        echo "<tr><td>Product Images</td><td>{$productImages->count()}</td><td>" . ($productImages->count() > 0 ? '<span style="color:green">✅</span>' : '<span style="color:orange">⚠️</span>') . "</td></tr>";
        echo "</table>";
        
        if ($productImages->count() > 0) {
            echo "<h3>Product Images Gallery:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Product ID</th><th>image_path</th><th>is_primary</th><th>File Exists?</th><th>Test Image</th></tr>";
            foreach ($productImages as $image) {
                $fullPath = storage_path('app/public/' . $image->image_path);
                $exists = file_exists($fullPath);
                $assetUrl = asset('storage/' . $image->image_path);
                
                echo "<tr style='background:" . ($exists ? '#d4edda' : '#f8d7da') . ";'>";
                echo "<td>{$image->id}</td>";
                echo "<td>{$image->product_id}</td>";
                echo "<td><code>{$image->image_path}</code></td>";
                echo "<td>" . ($image->is_primary ? '🌟' : 'NO') . "</td>";
                echo "<td>" . ($exists ? '✅ YES' : '❌ NO') . "</td>";
                echo "<td>";
                if ($exists) {
                    echo "<img src='{$assetUrl}' width='50' height='50' style='object-fit:cover;' onerror='this.style.background=\"#ff0000\"'>";
                } else {
                    echo "❌";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        if ($products->count() > 0) {
            echo "<h3>Products with Images:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>image_path</th><th>File Exists?</th><th>Storage URL</th><th>Asset URL</th><th>Test Image</th></tr>";
            foreach ($products as $product) {
                $fullPath = storage_path('app/public/' . $product->image_path);
                $exists = file_exists($fullPath);
                $storageUrl = Storage::disk('public')->url($product->image_path);
                $assetUrl = asset('storage/' . $product->image_path);
                
                echo "<tr style='background:" . ($exists ? '#d4edda' : '#f8d7da') . ";'>";
                echo "<td>{$product->id}</td>";
                echo "<td>{$product->name}</td>";
                echo "<td><code>{$product->image_path}</code></td>";
                echo "<td>" . ($exists ? '✅ YES' : '❌ NO') . "</td>";
                echo "<td><a href='{$storageUrl}' target='_blank'><code>{$storageUrl}</code></a></td>";
                echo "<td><a href='{$assetUrl}' target='_blank'><code>{$assetUrl}</code></a></td>";
                echo "<td>";
                if ($exists) {
                    echo "<img src='{$assetUrl}' width='50' height='50' style='object-fit:cover;' onerror='this.style.background=\"#ff0000\"'>";
                } else {
                    echo "❌";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>🔧 Quick Fix Commands</h2>";
    echo "<div style='background:#f8f9fa;padding:15px;border-radius:5px;'>";
    echo "<h3>Run these commands in your terminal:</h3>";
    echo "<code>php artisan storage:link</code><br>";
    echo "<code>php artisan cache:clear</code><br>";
    echo "<code>php artisan config:clear</code><br>";
    echo "<code>php artisan view:clear</code><br>";
    echo "</div>";
    
    echo "<p><small><em>Generated at: " . date('Y-m-d H:i:s') . "</em></small></p>";
});

Route::get('/debug/fix-images', function() {
    echo "<h1>🔧 Fix Product Images</h1>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;margin:20px 0;}th,td{padding:8px;border:1px solid #ddd;text-align:left;}th{background:#f4f4f4;}</style>";
    
    // Get all files in storage
    $storagePath = storage_path('app/public/products');
    $files = glob($storagePath . '/*');
    $fileNames = array_map('basename', $files);
    
    // Get all ProductImages records with wrong paths
    $wrongImages = DB::table('product_images')
        ->where('image_path', 'like', 'products/img_%')
        ->get();
    
    echo "<h2>📁 Available Files</h2>";
    echo "<table>";
    echo "<tr><th>File Name</th><th>Used</th></tr>";
    
    $usedFiles = [];
    foreach ($wrongImages as $img) {
        $usedFiles[] = basename($img->image_path);
    }
    
    foreach ($fileNames as $fileName) {
        $isUsed = in_array($fileName, $usedFiles);
        echo "<tr style='background:" . ($isUsed ? '#fff3cd' : '#d4edda') . ";'>";
        echo "<td><code>{$fileName}</code></td>";
        echo "<td>" . ($isUsed ? '❌ Wrong path' : '✅ Available') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>🔄 Fix Process</h2>";
    
    $fixed = 0;
    $availableFiles = array_diff($fileNames, $usedFiles);
    
    foreach ($wrongImages as $image) {
        if (!empty($availableFiles)) {
            $newFileName = array_shift($availableFiles);
            $newPath = 'products/' . $newFileName;
            
            // Update the record
            DB::table('product_images')
                ->where('id', $image->id)
                ->update(['image_path' => $newPath]);
            
            echo "<p style='color:green'>✅ Fixed Image ID {$image->id}: {$image->image_path} → {$newPath}</p>";
            $fixed++;
        }
    }
    
    echo "<h3>📊 Results</h3>";
    echo "<p><strong>Fixed:</strong> {$fixed} images</p>";
    echo "<p><strong>Remaining unused files:</strong> " . count($availableFiles) . "</p>";
    
    if ($fixed > 0) {
        echo "<p><a href='/debug/images' style='color:blue;text-decoration:underline;'>🔍 Check Results</a></p>";
    }
    
    echo "<p><small><em>Generated at: " . date('Y-m-d H:i:s') . "</em></small></p>";
});

Route::get('/debug/final-fix', function() {
    echo "<h1>🔧 Final Image Fix</h1>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;}p{margin:10px 0;}</style>";
    
    // Get remaining wrong paths
    $wrongImages = DB::table('product_images')
        ->where('image_path', 'like', 'products/img_%')
        ->get();
    
    // Get available unused files
    $storagePath = storage_path('app/public/products');
    $allFiles = glob($storagePath . '/*');
    $allFileNames = array_map('basename', $allFiles);
    
    $usedFiles = DB::table('product_images')->pluck('image_path')->map(function($path) {
        return basename($path);
    })->toArray();
    
    $availableFiles = array_diff($allFileNames, $usedFiles);
    
    echo "<h2>🔄 Final Fix Process</h2>";
    
    $fixed = 0;
    foreach ($wrongImages as $image) {
        if (!empty($availableFiles)) {
            $newFileName = array_shift($availableFiles);
            $newPath = 'products/' . $newFileName;
            
            DB::table('product_images')
                ->where('id', $image->id)
                ->update(['image_path' => $newPath]);
            
            echo "<p style='color:green'>✅ Fixed Image ID {$image->id}: {$image->image_path} → {$newPath}</p>";
            $fixed++;
        }
    }
    
    echo "<h3>📊 Results</h3>";
    echo "<p><strong>Total Fixed:</strong> {$fixed} images</p>";
    echo "<p><strong>All ProductImages should now work!</strong></p>";
    
    echo "<p><a href='/debug/images' style='color:blue;text-decoration:underline;'>🔍 Verify Results</a></p>";
    echo "<p><a href='/products' style='color:green;text-decoration:underline;'>🛍️ View Products</a></p>";
    
    echo "<p><small><em>Generated at: " . date('Y-m-d H:i:s') . "</em></small></p>";
});

Route::get('/debug/check-file', function() {
    echo "<h1>🔍 Specific File Check</h1>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;margin:20px 0;}th,td{padding:8px;border:1px solid #ddd;text-align:left;}th{background:#f4f4f4;}</style>";
    
    $specificFile = 'products/1777227990_1FsMPq4LhE.png';
    $fullPath = storage_path('app/public/' . $specificFile);
    
    echo "<h2>📁 File System Check</h2>";
    echo "<table>";
    echo "<tr><th>Check</th><th>Result</th><th>Status</th></tr>";
    
    echo "<tr><td>Database Path</td><td><code>{$specificFile}</code></td><td>✅</td></tr>";
    echo "<tr><td>Full Path</td><td><code>{$fullPath}</code></td><td>✅</td></tr>";
    echo "<tr><td>File Exists</td><td>" . (file_exists($fullPath) ? 'YES' : 'NO') . "</td><td>" . (file_exists($fullPath) ? '<span style="color:green">✅</span>' : '<span style="color:red">❌</span>') . "</td></tr>";
    echo "<tr><td>File Readable</td><td>" . (is_readable($fullPath) ? 'YES' : 'NO') . "</td><td>" . (is_readable($fullPath) ? '<span style="color:green">✅</span>' : '<span style="color:red">❌</span>') . "</td></tr>";
    echo "<tr><td>File Size</td><td>" . (file_exists($fullPath) ? number_format(filesize($fullPath) / 1024, 2) . ' KB' : 'N/A') . "</td><td>✅</td></tr>";
    
    echo "</table>";
    
    // Check all files in directory
    $storagePath = storage_path('app/public/products');
    $files = glob($storagePath . '/*');
    
    echo "<h2>📸 All Files in Directory</h2>";
    echo "<table>";
    echo "<tr><th>File Name</th><th>Size</th><th>Match</th></tr>";
    
    foreach ($files as $file) {
        $fileName = basename($file);
        $isMatch = $fileName === '1777227990_1FsMPq4LhE.png';
        $size = number_format(filesize($file) / 1024, 2) . ' KB';
        
        echo "<tr style='background:" . ($isMatch ? '#d4edda' : '#fff') . ";'>";
        echo "<td><code>{$fileName}</code></td>";
        echo "<td>{$size}</td>";
        echo "<td>" . ($isMatch ? '🎯 FOUND' : '') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Test URL generation
    $assetUrl = asset('storage/' . $specificFile);
    $storageUrl = Storage::disk('public')->url($specificFile);
    
    echo "<h2>🔗 URL Test</h2>";
    echo "<table>";
    echo "<tr><th>URL Type</th><th>URL</th><th>Test</th></tr>";
    echo "<tr><td>Asset URL</td><td><a href='{$assetUrl}' target='_blank'><code>{$assetUrl}</code></a></td><td><img src='{$assetUrl}' width='50' height='50' onerror='this.style.background=\"#ff0000\"'></td></tr>";
    echo "<tr><td>Storage URL</td><td><a href='{$storageUrl}' target='_blank'><code>{$storageUrl}</code></a></td><td><img src='{$storageUrl}' width='50' height='50' onerror='this.style.background=\"#ff0000\"'></td></tr>";
    echo "</table>";
    
    echo "<p><small><em>Generated at: " . date('Y-m-d H:i:s') . "</em></small></p>";
});