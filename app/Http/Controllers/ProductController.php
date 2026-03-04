<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand', 'inventoryLocations'])
            ->latest()
            ->paginate(20);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $warehouses = Warehouse::all();
        $recentProducts = Product::latest()->take(5)->get();

        return view('products.create', compact('categories', 'brands', 'warehouses', 'recentProducts'));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'weight' => 'nullable|numeric|min:0',
            'default_low_stock_threshold' => 'nullable|integer|min:1',
            'manage_stock' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images' => 'nullable|array|max:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'images.max' => 'You can only upload 1 product image.',
            'images.*.max' => 'Image size should not exceed 2MB.',
            'images.*.mimes' => 'Only JPEG, PNG, JPG, GIF images are allowed.',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);

        // Set default values
        $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 1;
        $validated['is_active'] = $request->has('is_active') ? 1 : 1;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $validated['has_variants'] = false;

        // Create product
        $product = Product::create($validated);

        // Handle image upload
        if ($request->hasFile('images')) {
            try {
                $image = $request->file('images')[0];

                // Additional server-side validation
                if ($image->getSize() > 2097152) {
                    return back()->with('error', 'Image size should not exceed 2MB.')->withInput();
                }

                $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
                $path = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);

            } catch (\Exception $e) {
                return back()->with('error', 'Failed to upload image: '.$e->getMessage())->withInput();
            }
        }

        // Add initial stock to warehouses if provided
        if ($request->has('warehouse_stock')) {
            foreach ($request->warehouse_stock as $warehouseId => $quantity) {
                if ($quantity > 0) {
                    $locationCode = $request->location_code[$warehouseId] ?? null;

                    InventoryLocation::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                        'quantity' => $quantity,
                        'reserved_quantity' => 0,
                        'location_code' => $locationCode,
                    ]);

                    // Sync warehouse_products table to reflect the inventory locations
                    $totalQty = InventoryLocation::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouseId)
                        ->sum('quantity');

                    WarehouseProduct::updateOrCreate([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $product->id,
                    ], [
                        'quantity' => $totalQty,
                    ]);
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'variants', 'images', 'inventoryLocations.warehouse']);
        $warehouses = Warehouse::all();

        // Calculate total stock
        $totalStock = InventoryLocation::where('product_id', $product->id)->sum('quantity');
        $product->total_stock = $totalStock;

        return view('products.show', compact('product', 'warehouses'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $warehouses = Warehouse::all();
        $product->load(['inventoryLocations', 'images']);

        return view('products.edit', compact('product', 'categories', 'brands', 'warehouses'));
    }

    public function update(Request $request, Product $product)
    {
        Log::info('Update request received for product ID: '.$product->id);
        Log::info('Request data:', $request->all());
        Log::info('Files in request:', $request->allFiles());

        // Validation - SIMPLIFIED - Remove image validation from main validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,'.$product->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'weight' => 'nullable|numeric|min:0',
            'default_low_stock_threshold' => 'nullable|integer|min:1',
            'manage_stock' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        Log::info('Validated data:', $validated);

        // Check for image upload SEPARATELY
        $hasNewImage = false;
        $newImage = null;

        // Debug file upload
        Log::info('Checking for uploaded files...');

        if ($request->hasFile('images')) {
            Log::info('Request has files in "images" key');
            $files = $request->file('images');
            Log::info('Files array:', is_array($files) ? ['count' => count($files)] : ['type' => gettype($files)]);

            // Handle both array and single file
            if (is_array($files)) {
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $hasNewImage = true;
                        $newImage = $file;
                        Log::info('Valid image found in array:', [
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'type' => $file->getMimeType(),
                        ]);
                        break;
                    }
                }
            } elseif ($files && $files->isValid()) {
                $hasNewImage = true;
                $newImage = $files;
                Log::info('Valid single image found:', [
                    'name' => $files->getClientOriginalName(),
                    'size' => $files->getSize(),
                    'type' => $files->getMimeType(),
                ]);
            }
        }

        // Also check for single file upload
        if (! $hasNewImage && $request->file('images.0')) {
            $file = $request->file('images.0');
            if ($file && $file->isValid()) {
                $hasNewImage = true;
                $newImage = $file;
                Log::info('Valid image found in images.0:', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        Log::info('Image upload status:', [
            'hasNewImage' => $hasNewImage,
            'imageFound' => ! is_null($newImage),
        ]);

        // Validate image separately if uploaded
        if ($hasNewImage && $newImage) {
            Log::info('Validating uploaded image...');

            // Check file size
            if ($newImage->getSize() > 2097152) { // 2MB
                Log::warning('Image too large: '.$newImage->getSize().' bytes');

                return back()->with('error', 'Image size should not exceed 2MB.')->withInput();
            }

            // Check file type
            $validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (! in_array($newImage->getMimeType(), $validTypes)) {
                Log::warning('Invalid image type: '.$newImage->getMimeType());

                return back()->with('error', 'Only JPEG, PNG, JPG, GIF images are allowed.')->withInput();
            }

            Log::info('Image validation passed');
        }

        // Update slug if name changed
        if ($product->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);
        }

        // Set boolean values
        $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;

        // ALWAYS UPDATE THE PRODUCT - don't check for changes
        $product->update($validated);
        Log::info('Product basic info updated');

        // Handle new image if uploaded
        if ($hasNewImage && $newImage) {
            try {
                Log::info('Processing new image upload...');

                // Delete existing images
                if ($product->images->count() > 0) {
                    Log::info('Deleting '.$product->images->count().' existing images');
                    foreach ($product->images as $existingImage) {
                        // Delete physical file
                        if (Storage::disk('public')->exists($existingImage->image_path)) {
                            Storage::disk('public')->delete($existingImage->image_path);
                            Log::info('Deleted physical file: '.$existingImage->image_path);
                        }
                        // Delete database record
                        $existingImage->delete();
                        Log::info('Deleted database record for image ID: '.$existingImage->id);
                    }
                } else {
                    Log::info('No existing images to delete');
                }

                // Store new image
                $imageName = time().'_'.Str::random(10).'.'.$newImage->getClientOriginalExtension();
                $path = $newImage->storeAs('products', $imageName, 'public');

                Log::info('New image stored:', [
                    'path' => $path,
                    'name' => $imageName,
                    'full_path' => storage_path('app/public/'.$path),
                ]);

                // Create new image record
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);

                Log::info('New image record created in database');

            } catch (\Exception $e) {
                Log::error('Image update failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return back()->with('error', 'Failed to update image: '.$e->getMessage())->withInput();
            }
        } else {
            Log::info('No new image uploaded, skipping image update');
        }

        // Update stock locations
        if ($request->has('warehouse_stock')) {
            Log::info('Processing warehouse stock updates');
            foreach ($request->warehouse_stock as $warehouseId => $quantity) {
                $locationCode = $request->location_code[$warehouseId] ?? null;

                $inventoryLocation = InventoryLocation::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($inventoryLocation) {
                    if ($quantity > 0) {
                        $inventoryLocation->update([
                            'quantity' => $quantity,
                            'location_code' => $locationCode,
                        ]);
                        Log::info("Updated warehouse {$warehouseId} quantity to {$quantity}");
                        // Sync warehouse product quantity after update
                        $totalQty = InventoryLocation::where('product_id', $product->id)
                            ->where('warehouse_id', $warehouseId)
                            ->sum('quantity');

                        WarehouseProduct::updateOrCreate([
                            'warehouse_id' => $warehouseId,
                            'product_id' => $product->id,
                        ], [
                            'quantity' => $totalQty,
                        ]);
                    } else {
                        $inventoryLocation->delete();
                        Log::info("Deleted inventory location for warehouse {$warehouseId}");
                        // Ensure warehouse product quantity is in sync (set to 0)
                        WarehouseProduct::updateOrCreate([
                            'warehouse_id' => $warehouseId,
                            'product_id' => $product->id,
                        ], [
                            'quantity' => 0,
                        ]);
                    }
                } elseif ($quantity > 0) {
                    InventoryLocation::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                        'quantity' => $quantity,
                        'reserved_quantity' => 0,
                        'location_code' => $locationCode,
                    ]);
                    Log::info("Created new inventory location for warehouse {$warehouseId} with quantity {$quantity}");
                    // Sync warehouse product quantity after creation
                    $totalQty = InventoryLocation::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouseId)
                        ->sum('quantity');

                    WarehouseProduct::updateOrCreate([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $product->id,
                    ], [
                        'quantity' => $totalQty,
                    ]);
                }
            }
        }

        Log::info('Product update completed successfully');

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Delete associated images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        // Delete inventory locations
        $product->inventoryLocations()->delete();

        // Delete product
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    public function deleteImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }

    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        // Reset all images to non-primary
        ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);

        // Set selected image as primary
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Primary image updated!');
    }
}
