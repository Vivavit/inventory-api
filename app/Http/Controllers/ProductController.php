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
        $products = Product::with(['category', 'brand', 'inventoryLocations', 'warehouseProducts', 'images'])
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . ($request->id ?? 'null'),
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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Set default values
        $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $validated['has_variants'] = false;

        // Handle primary image upload (for image_path column)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            if ($image && $image->isValid()) {
                try {
                    $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
                    $path = $image->storeAs('products', $imageName, 'public');
                    $validated['image_path'] = $path;
                    Log::info('Primary image stored: '.$path);
                } catch (\Exception $e) {
                    Log::error('Primary image upload failed: '.$e->getMessage());
                    return back()->with('error', 'Failed to upload primary image');
                }
            }
        }

        // Create product
        $product = Product::create($validated);

        // Handle gallery images (ProductImage table)
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            
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
                        
                        Log::info('Gallery image stored: '.$path);
                    } catch (\Exception $e) {
                        Log::error('Gallery image upload failed: '.$e->getMessage());
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

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $warehouses = Warehouse::all();
        
        $product->load([
            'category', 
            'brand', 
            'images', 
            'inventoryLocations',
            'warehouseProducts'
        ]);
        
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

        return response()->json([
            'product' => $product,
            'warehouse_stock' => $warehouseStock,
            'categories' => $categories,
            'brands' => $brands,
            'warehouses' => $warehouses,
            'primary_image_url' => $product->image_url,
            'gallery_images' => $product->images->map(function($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->url,
                    'is_primary' => $img->is_primary
                ];
            }),
        ]);
    }

    public function update(Request $request, Product $product)
    {
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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Set boolean values
        $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;

        // Handle primary image update (image_path column)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            if ($image && $image->isValid()) {
                try {
                    // Delete old primary image if exists
                    if ($product->image_path) {
                        Storage::disk('public')->delete($product->image_path);
                    }

                    $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
                    $path = $image->storeAs('products', $imageName, 'public');
                    $validated['image_path'] = $path;
                    Log::info('Primary image updated: '.$path);
                } catch (\Exception $e) {
                    Log::error('Primary image update failed: '.$e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update primary image: '.$e->getMessage()
                    ], 500);
                }
            }
        }

        // Update product
        $product->update($validated);

        // Handle gallery images update (ProductImage table)
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            
            if (!is_array($files)) {
                $files = [$files];
            }
            
            // Delete old gallery images
            foreach ($product->images as $oldImage) {
                Storage::disk('public')->delete($oldImage->image_path);
                $oldImage->delete();
            }
            
            // Upload new gallery images
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
                        
                        Log::info('Gallery image updated: '.$path);
                    } catch (\Exception $e) {
                        Log::error('Gallery image upload failed: '.$e->getMessage());
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to update gallery images: '.$e->getMessage()
                        ], 500);
                    }
                }
            }
        }

        // Update stock locations
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
                    
                    WarehouseProduct::updateOrCreate([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $product->id,
                    ], [
                        'quantity' => $quantity,
                    ]);
                }
            }
        }

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
            // Delete primary image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            // Delete gallery images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Delete inventory locations
            $product->inventoryLocations()->delete();
            
            // Delete warehouse products
            $product->warehouseProducts()->delete();

            // Delete product permanently
            $product->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Product delete failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: '.$e->getMessage()
            ], 500);
        }
    }

    public function deleteImage(ProductImage $image)
    {
        try {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
            return back()->with('success', 'Image deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Image delete failed: '.$e->getMessage());
            return back()->with('error', 'Failed to delete image');
        }
    }

    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        try {
            ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);

            return back()->with('success', 'Primary image updated!');
        } catch (\Exception $e) {
            Log::error('Set primary image failed: '.$e->getMessage());
            return back()->with('error', 'Failed to update primary image');
        }
    }
}