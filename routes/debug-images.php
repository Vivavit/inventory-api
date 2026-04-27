<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// Comprehensive Image Diagnostic Route
Route::get('/debug/images', function() {
    echo "<h1>🔍 Image Upload Diagnostic Report</h1>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;margin:20px 0;}th,td{padding:8px;border:1px solid #ddd;text-align:left;}th{background:#f4f4f4;}.success{background:#d4edda;}.error{background:#f8d7da;}.warning{background:#fff3cd;}code{background:#f8f9fa;padding:2px 4px;border-radius:3px;}</style>";
    
    // 1. Check Storage Directory
    echo "<h2>📁 Storage Directory Check</h2>";
    $storagePath = storage_path('app/public/products');
    $publicStoragePath = public_path('storage/products');
    $symlinkExists = is_link(public_path('storage'));
    
    echo "<table>";
    echo "<tr><th>Check</th><th>Result</th><th>Status</th></tr>";
    
    echo "<tr><td>Storage directory exists</td><td><code>{$storagePath}</code></td><td>" . (is_dir($storagePath) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Storage directory writable</td><td><code>{$storagePath}</code></td><td>" . (is_writable($storagePath) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Public storage symlink exists</td><td><code>public/storage</code></td><td>" . ($symlinkExists ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "</td></tr>";
    echo "<tr><td>Symlink target exists</td><td><code>storage/app/public</code></td><td>" . (is_dir(storage_path('app/public')) ? '<span class="success">✅ YES</span>' : '<span class="error">❌ NO</span>') . "</td></tr>";
    
    if ($symlinkExists) {
        $symlinkTarget = readlink(public_path('storage'));
        echo "<tr><td>Symlink points to</td><td><code>{$symlinkTarget}</code></td><td>" . ($symlinkTarget === '../storage/app/public' ? '<span class="success">✅ CORRECT</span>' : '<span class="warning">⚠️ CHECK</span>') . "</td></tr>";
    }
    
    echo "</table>";
    
    // 2. Check Files in Storage
    echo "<h2>📸 Files in Storage</h2>";
    $files = glob($storagePath . '/*');
    if (empty($files)) {
        echo "<p class='warning'>⚠️ No files found in storage/app/public/products/</p>";
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
    
    // 3. Check Database - Products Table
    echo "<h2>🗄️ Products Table (image_path column)</h2>";
    $products = DB::table('products')->whereNotNull('image_path')->get();
    
    if ($products->isEmpty()) {
        echo "<p class='warning'>⚠️ No products with image_path found</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>image_path</th><th>File Exists?</th><th>Storage URL</th><th>Asset URL</th></tr>";
        
        foreach ($products as $product) {
            $fullPath = storage_path('app/public/' . $product->image_path);
            $exists = file_exists($fullPath);
            $storageUrl = Storage::disk('public')->url($product->image_path);
            $assetUrl = asset('storage/' . $product->image_path);
            $status = $exists ? 'success' : 'error';
            
            echo "<tr class='{$status}'>";
            echo "<td>{$product->id}</td>";
            echo "<td>{$product->name}</td>";
            echo "<td><code>{$product->image_path}</code></td>";
            echo "<td>" . ($exists ? '✅ YES' : '❌ NO') . "</td>";
            echo "<td><a href='{$storageUrl}' target='_blank'><code>{$storageUrl}</code></a></td>";
            echo "<td><a href='{$assetUrl}' target='_blank'><code>{$assetUrl}</code></a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Check Database - ProductImages Table
    echo "<h2>🖼️ ProductImages Table</h2>";
    $productImages = DB::table('product_images')->get();
    
    if ($productImages->isEmpty()) {
        echo "<p class='warning'>⚠️ No product images found</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Product ID</th><th>image_path</th><th>is_primary</th><th>File Exists?</th><th>Storage URL</th></tr>";
        
        foreach ($productImages as $image) {
            $fullPath = storage_path('app/public/' . $image->image_path);
            $exists = file_exists($fullPath);
            $storageUrl = Storage::disk('public')->url($image->image_path);
            $status = $exists ? 'success' : 'error';
            
            echo "<tr class='{$status}'>";
            echo "<td>{$image->id}</td>";
            echo "<td>{$image->product_id}</td>";
            echo "<td><code>{$image->image_path}</code></td>";
            echo "<td>" . ($image->is_primary ? '🌟 YES' : 'NO') . "</td>";
            echo "<td>" . ($exists ? '✅ YES' : '❌ NO') . "</td>";
            echo "<td><a href='{$storageUrl}' target='_blank'><code>{$storageUrl}</code></a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Test URL Generation
    echo "<h2>🔗 URL Generation Test</h2>";
    $testPath = 'products/test.jpg';
    
    echo "<table>";
    echo "<tr><th>Method</th><th>Result</th><th>Expected</th></tr>";
    
    $storageUrl = Storage::disk('public')->url($testPath);
    echo "<tr><td>Storage::url()</td><td><code>{$storageUrl}</code></td><td>/storage/products/test.jpg</td></tr>";
    
    $assetUrl = asset('storage/' . $testPath);
    echo "<tr><td>asset()</td><td><code>{$assetUrl}</code></td><td>http://localhost/storage/products/test.jpg</td></tr>";
    
    $url = url('storage/' . $testPath);
    echo "<tr><td>url()</td><td><code>{$url}</code></td><td>http://localhost/storage/products/test.jpg</td></tr>";
    
    echo "</table>";
    
    // 6. Environment Check
    echo "<h2>⚙️ Environment Check</h2>";
    echo "<table>";
    echo "<tr><th>Setting</th><th>Value</th></tr>";
    echo "<tr><td>APP_URL</td><td><code>" . env('APP_URL') . "</code></td></tr>";
    echo "<tr><td>FILESYSTEM_DRIVER</td><td><code>" . env('FILESYSTEM_DRIVER', 'local') . "</code></td></tr>";
    echo "<tr><td>Current Environment</td><td><code>" . app()->environment() . "</code></td></tr>";
    echo "</table>";
    
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
