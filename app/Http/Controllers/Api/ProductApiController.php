<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $warehouses = $user->warehouses()->get(['warehouses.id as id', 'warehouses.name', 'warehouses.code']);
        $warehouseIds = $warehouses->pluck('id')->toArray();

        if (empty($warehouseIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No warehouse assigned to user. Contact administrator.',
            ], 403);
        }

        $productsQuery = Product::whereHas('warehouseProducts', function ($query) use ($warehouseIds) {
            $query->whereIn('warehouse_id', $warehouseIds);
        })
            ->with(['images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->where('is_active', true);

        $products = $productsQuery->get()->map(function ($product) use ($warehouseIds, $user) {
            $primaryImage = $product->primaryImage;
            $imagePath = $primaryImage ? $primaryImage->image_path : 'products/default.jpg';

            if (str_contains($imagePath, 'app/public/')) {
                $imagePath = str_replace('app/public/', '', $imagePath);
            }

            $fullPath = public_path('storage/'.$imagePath);
            if (! file_exists($fullPath)) {
                $imagePath = 'products/default.jpg';
            }

            $imageUrl = url('storage/'.$imagePath);

            if ($user->isAdmin()) {
                $stock = (int) DB::table('warehouse_products')
                    ->where('product_id', $product->id)
                    ->whereIn('warehouse_id', $warehouseIds)
                    ->sum('quantity');
            } else {
                $stock = (int) DB::table('warehouse_products')
                    ->where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseIds[0])
                    ->sum('quantity');
            }

            $item = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->short_description ?? '',
                'price' => (float) $product->price,
                'stock' => $stock,
                'image' => $imageUrl,
            ];

            if (! $user->isAdmin()) {
                $item['warehouse_id'] = $warehouseIds[0];
            }

            return $item;
        });

        $response = [
            'success' => true,
            'data' => $products,
        ];

        if ($user->isAdmin()) {
            $response['warehouses'] = $warehouses;
            $response['warehouse_id'] = null;
            $response['warehouse_name'] = null;
        } else {
            $response['warehouse_id'] = $warehouseIds[0];
            $response['warehouse_name'] = $warehouses->firstWhere('id', $warehouseIds[0])->name;
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
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
        ]);

        $validated = $request->only([
            'name', 'sku', 'category_id', 'brand_id', 'price', 'cost_price',
            'compare_price', 'description', 'short_description', 'weight',
            'default_low_stock_threshold', 'manage_stock', 'is_active', 'is_featured',
        ]);

        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);
        $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $validated['has_variants'] = false;

        DB::beginTransaction();

        try {
            $product = Product::create($validated);

            // Handle image upload
            if ($request->hasFile('images')) {
                $image = $request->file('images')[0];

                if ($image->isValid()) {
                    $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
                    $path = $image->store('products', 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => true,
                        'sort_order' => 0,
                    ]);
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

            DB::commit();

            $product->load(['category', 'brand', 'images', 'warehouseProducts.warehouse']);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, Product $product)
    {
        $user = $request->user();
        $warehouses = $user->warehouses()->get(['warehouses.id as id', 'warehouses.name', 'warehouses.code']);
        $warehouseIds = $warehouses->pluck('id')->toArray();

        // Check if product exists in user's accessible warehouses
        $hasAccess = $product->warehouseProducts()
            ->whereIn('warehouse_id', $warehouseIds)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or you do not have access',
            ], 404);
        }

        $product->load([
            'category',
            'brand',
            'variants',
            'images',
            'warehouseProducts.warehouse',
            'inventoryLocations',
        ]);

        // Calculate stock for accessible warehouses
        $stock = DB::table('warehouse_products')
            ->where('product_id', $product->id)
            ->whereIn('warehouse_id', $warehouseIds)
            ->sum('quantity');

        $productData = $product->toArray();
        $productData['stock'] = (int) $stock;

        if (! $user->isAdmin()) {
            $productData['warehouse_id'] = $warehouseIds[0];
            $productData['warehouse_name'] = $warehouses->firstWhere('id', $warehouseIds[0])->name;
        }

        return response()->json([
            'success' => true,
            'data' => $productData,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->validate($request, [
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

        $validated = $request->only([
            'name', 'sku', 'category_id', 'brand_id', 'price', 'cost_price',
            'compare_price', 'description', 'short_description', 'weight',
            'default_low_stock_threshold', 'manage_stock', 'is_active', 'is_featured',
        ]);

        $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;

        if ($product->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);
        }

        DB::beginTransaction();

        try {
            $product->update($validated);

            // Handle new image upload
            if ($request->hasFile('images')) {
                $files = $request->file('images');
                $newImage = null;

                if (is_array($files)) {
                    foreach ($files as $file) {
                        if ($file && $file->isValid()) {
                            $newImage = $file;
                            break;
                        }
                    }
                } elseif ($files && $files->isValid()) {
                    $newImage = $files;
                }

                if ($newImage) {
                    // Delete existing images
                    foreach ($product->images as $existingImage) {
                        if (Storage::disk('public')->exists($existingImage->image_path)) {
                            Storage::disk('public')->delete($existingImage->image_path);
                        }
                        $existingImage->delete();
                    }

                    // Store new image
                    $imageName = time().'_'.Str::random(10).'.'.$newImage->getClientOriginalExtension();
                    $path = $newImage->storeAs('products', $imageName, 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => true,
                        'sort_order' => 0,
                    ]);
                }
            }

            // Update warehouse stock if provided
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

            DB::commit();

            $product->load(['category', 'brand', 'images', 'warehouseProducts.warehouse', 'inventoryLocations']);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, Product $product)
    {
        DB::beginTransaction();

        try {
            // Delete associated images from storage
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            // Delete inventory locations
            $product->inventoryLocations()->delete();

            // Delete product (soft delete)
            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: '.$e->getMessage(),
            ], 500);
        }
    }
}
