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
        $products = Product::with(['category', 'brand', 'inventoryLocations', 'warehouseProducts'])
            ->latest()
            ->paginate(20);

        $categories = Category::all();
        $brands = Brand::all();
        $warehouses = Warehouse::all();

        return view('products.index', compact('products', 'categories', 'brands', 'warehouses'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $warehouses = Warehouse::all();
        $recentProducts = Product::latest()->take(5)->get();

        return view('products.create', compact('categories', 'brands', 'warehouses', 'recentProducts'));
    }

// In ProductController.php - store method
public function store(Request $request)
{
    // Update validation to handle images properly
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
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Generate slug
    $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);
    
    // Set default values
    $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 0;
    $validated['is_active'] = $request->has('is_active') ? 1 : 0;
    $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;
    $validated['has_variants'] = false;

    // Create product
    $product = Product::create($validated);

    // Handle image upload - FIXED
    if ($request->hasFile('images')) {
        $files = $request->file('images');
        
        // Handle both single file and array
        if (!is_array($files)) {
            $files = [$files];
        }
        
        foreach ($files as $index => $image) {
            if ($image && $image->isValid()) {
                try {
                    $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
                    $path = $image->storeAs('products', $imageName, 'public');
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                } catch (\Exception $e) {
                    // Log error but continue
                    Log::error('Image upload failed: '.$e->getMessage());
                }
            }
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

                // Sync warehouse_products table
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
        $product->load(['category', 'brand', 'variants', 'images', 'warehouseProducts.warehouse']);
        $warehouses = Warehouse::all();

        return view('products.show', compact('product', 'warehouses'));
    }

// In ProductController.php
public function edit(Product $product)
{
    $categories = Category::all();
    $brands = Brand::all();
    $warehouses = Warehouse::all();
    
    // Eager load all necessary relationships
    $product->load([
        'category', 
        'brand', 
        'images', 
        'inventoryLocations',
        'warehouseProducts'
    ]);
    
    // Prepare warehouse stock data
    $warehouseStock = [];
    foreach ($warehouses as $warehouse) {
        $inventoryLocation = $product->inventoryLocations
            ->where('warehouse_id', $warehouse->id)
            ->first();
            
        $warehouseStock[$warehouse->id] = [
            'quantity' => $inventoryLocation ? $inventoryLocation->quantity : 0,
            'location_code' => $inventoryLocation ? $inventoryLocation->location_code : '',
        ];
    }

    // Always return JSON for popup operations
    return response()->json([
        'product' => $product,
        'warehouse_stock' => $warehouseStock,
        'categories' => $categories,
        'brands' => $brands,
        'warehouses' => $warehouses,
        'primary_image_url' => $product->primaryImage ? $product->primaryImage->url : null,
    ]);
}

    public function update(Request $request, Product $product)
    {
        // Validation
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload efficiently
        $hasNewImage = false;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $request->file('image');
            if ($newImage && $newImage->isValid()) {
                $hasNewImage = true;
            }
        }

        // Update slug if name changed
        if ($product->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);
        }

        // Set boolean values
        $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;

        // Update product
        $product->update($validated);
        // Handle new image if uploaded
        if ($hasNewImage && $newImage) {
            try {
                // Delete existing images efficiently
                $product->images()->each(function ($existingImage) {
                    Storage::disk('public')->delete($existingImage->image_path);
                    $existingImage->delete();
                });

                // Store new image
                $imageName = time().'_'.Str::random(10).'.'.$newImage->getClientOriginalExtension();
                $path = $newImage->storeAs('products', $imageName, 'public');

                // Create new image record
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update image: ' . $e->getMessage()
                ], 500);
            }
        }

        // Update stock locations efficiently
        if ($request->has('warehouse_stock')) {
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
                        
                        // Sync warehouse product quantity
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
                        
                        // Set warehouse product quantity to 0
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
                    
                    // Sync warehouse product quantity
                    WarehouseProduct::updateOrCreate([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $product->id,
                    ], [
                        'quantity' => $quantity,
                    ]);
                }
            }
        }

        // Return JSON response for popup operations
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully!',
            'product' => $product->fresh(['category', 'brand', 'images', 'inventoryLocations'])
        ]);
    }

    public function getForModal(Product $product)
    {
        $product->load(['category', 'brand', 'inventoryLocations', 'images']);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        try {
            // Delete associated images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Delete inventory locations
            $product->inventoryLocations()->delete();

            // Delete product
            $product->delete();

            // Return JSON response for popup operations
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
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
