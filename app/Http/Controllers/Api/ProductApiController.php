<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        // Get user's warehouses
        $warehouses = $user->warehouses()->get(['warehouses.id as id', 'warehouses.name', 'warehouses.code']);
        $warehouseIds = $warehouses->pluck('id')->toArray();

        if (empty($warehouseIds) && ! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No warehouse assigned to user. Contact administrator.',
            ], 403);
        }

        // Build query
        $productsQuery = Product::query()->with(['images' => function ($q) {
            $q->where('is_primary', true);
        }])->where('is_active', true);

        // For non-admin users, filter by their warehouses only
        if (! $user->isAdmin() && ! empty($warehouseIds)) {
            $productsQuery->whereHas('warehouseProducts', function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            });
        }
        // For admin users, show all products (no warehouse filter)

        $products = $productsQuery->get()->map(function ($product) use ($warehouseIds, $user, $warehouses, $request) {
            $primaryImage = $product->primaryImage;
            $imagePath = $primaryImage ? $primaryImage->image_path : null;

            // Normalize path: remove 'storage/' or 'public/' prefixes if present
            if ($imagePath) {
                $imagePath = preg_replace('/^(storage\/|public\/)/', '', $imagePath);
            }

            $imageExists = $imagePath
                ? Storage::disk('public')->exists($imagePath)
                : false;

            $imageUrl = $imageExists
                ? $request->getSchemeAndHttpHost().'/storage/'.$imagePath
                : asset('images/product-default.svg');

            // Calculate stock
            if ($user->isAdmin()) {
                // Admin sees total stock across all warehouses
                $stock = (int) DB::table('warehouse_products')
                    ->where('product_id', $product->id)
                    ->sum('quantity');
            } else {
                // Non-admin sees stock from their assigned warehouses only
                if (empty($warehouseIds)) {
                    $stock = 0;
                } else {
                    $stock = (int) DB::table('warehouse_products')
                        ->where('product_id', $product->id)
                        ->whereIn('warehouse_id', $warehouseIds)
                        ->sum('quantity');
                }
            }

            $item = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->short_description ?? '',
                'price' => (float) $product->price,
                'stock' => $stock,
                'image' => $imageUrl,
                'sku' => $product->sku,
                'category' => $product->category ? $product->category->name : null,
                'is_active' => (bool) $product->is_active,
                'is_featured' => (bool) $product->is_featured,
            ];

            // Add warehouse info for non-admin users
            if (! $user->isAdmin() && ! empty($warehouseIds)) {
                $item['warehouse_id'] = $warehouseIds[0];
                $item['warehouse_name'] = $warehouses->firstWhere('id', $warehouseIds[0])->name ?? null;
            }

            return $item;
        });

        $response = [
            'success' => true,
            'data' => $products,
        ];

        // Add warehouses info for admin users
        if ($user->isAdmin()) {
            $response['warehouses'] = $warehouses;
            $response['warehouse_id'] = null;
            $response['warehouse_name'] = null;
        } elseif (! empty($warehouseIds)) {
            $response['warehouse_id'] = $warehouseIds[0];
            $response['warehouse_name'] = $warehouses->firstWhere('id', $warehouseIds[0])->name ?? null;
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $user = $request->user();

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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'warehouse_stock' => 'nullable|array',
            'warehouse_stock.*' => 'integer|min:0',
            'location_code' => 'nullable|array',
            'location_code.*' => 'string|max:50',
        ]);

        $validated = $request->only([
            'name', 'sku', 'category_id', 'brand_id', 'price', 'cost_price',
            'compare_price', 'description', 'short_description', 'weight',
            'default_low_stock_threshold',
        ]);

        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);
        $validated['manage_stock'] = $request->has('manage_stock') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $validated['has_variants'] = false;

        DB::beginTransaction();

        try {
            $product = Product::create($validated);

            // Handle image upload(s)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    if ($image && $image->isValid()) {
                        $imageName = time().'_'.Str::random(10).'.'.$image->getClientOriginalExtension();
                        $path = $image->store('products', 'public');

                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $path,
                            'is_primary' => $index === 0, // First image is primary
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // Add initial stock to warehouses if provided
            if ($request->has('warehouse_stock')) {
                foreach ($request->warehouse_stock as $warehouseId => $quantity) {
                    if ($quantity > 0) {
                        $locationCode = $request->location_code[$warehouseId] ?? null;

                        // Validate warehouse access for admin/non-admin
                        if (! $user->isAdmin() && ! in_array($warehouseId, $user->warehouses()->pluck('id')->toArray())) {
                            continue; // Skip warehouses user doesn't have access to
                        }

                        $inventoryLocation = InventoryLocation::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouseId,
                            'quantity' => $quantity,
                            'reserved_quantity' => 0,
                            'location_code' => $locationCode,
                        ]);

                        // Update warehouse product summary
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

        // Admin can see any product, others need warehouse access
        if (! $user->isAdmin()) {
            $warehouses = $user->warehouses()->get(['warehouses.id as id', 'warehouses.name', 'warehouses.code']);
            $warehouseIds = $warehouses->pluck('id')->toArray();

            if (empty($warehouseIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No warehouse assigned to user. Contact administrator.',
                ], 403);
            }

            $hasAccess = $product->warehouseProducts()
                ->whereIn('warehouse_id', $warehouseIds)
                ->exists();

            if (! $hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or you do not have access',
                ], 404);
            }
        }

        $product->load([
            'category',
            'brand',
            'variants',
            'images',
            'warehouseProducts.warehouse',
            'inventoryLocations',
        ]);

        // Calculate stock
        $stockQuery = DB::table('warehouse_products')->where('product_id', $product->id);

        if (! $user->isAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('id')->toArray();
            if (! empty($warehouseIds)) {
                $stockQuery->whereIn('warehouse_id', $warehouseIds);
            }
        }

        $stock = (int) $stockQuery->sum('quantity');

        $productData = $product->toArray();
        $productData['stock'] = $stock;

        // Add warehouse info for non-admin users
        if (! $user->isAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('id')->toArray();
            if (! empty($warehouseIds)) {
                $firstWarehouse = $user->warehouses()->first();
                $productData['warehouse_id'] = $warehouseIds[0];
                $productData['warehouse_name'] = $firstWarehouse ? $firstWarehouse->name : null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $productData,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $user = $request->user();

        // Admin can update any product, others need warehouse access
        if (! $user->isAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('id')->toArray();

            $hasAccess = $product->warehouseProducts()
                ->whereIn('warehouse_id', $warehouseIds)
                ->exists();

            if (! $hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or you do not have permission to update',
                ], 404);
            }
        }

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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'warehouse_stock' => 'nullable|array',
            'warehouse_stock.*' => 'integer|min:0',
            'location_code' => 'nullable|array',
            'location_code.*' => 'string|max:50',
        ]);

        $validated = $request->only([
            'name', 'sku', 'category_id', 'brand_id', 'price', 'cost_price',
            'compare_price', 'description', 'short_description', 'weight',
            'default_low_stock_threshold',
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

            // Handle new image upload (replace existing images)
            if ($request->hasFile('images')) {
                // Delete existing images
                foreach ($product->images as $existingImage) {
                    if (Storage::disk('public')->exists($existingImage->image_path)) {
                        Storage::disk('public')->delete($existingImage->image_path);
                    }
                    $existingImage->delete();
                }

                // Store new images
                $files = $request->file('images');
                if (is_array($files)) {
                    foreach ($files as $index => $newImage) {
                        if ($newImage && $newImage->isValid()) {
                            $imageName = time().'_'.Str::random(10).'.'.$newImage->getClientOriginalExtension();
                            $path = $newImage->storeAs('products', $imageName, 'public');

                            ProductImage::create([
                                'product_id' => $product->id,
                                'image_path' => $path,
                                'is_primary' => $index === 0,
                                'sort_order' => $index,
                            ]);
                        }
                    }
                }
            }

            // Update warehouse stock if provided
            if ($request->has('warehouse_stock')) {
                foreach ($request->warehouse_stock as $warehouseId => $quantity) {
                    // Check warehouse access for non-admin
                    if (! $user->isAdmin() && ! in_array($warehouseId, $user->warehouses()->pluck('id')->toArray())) {
                        continue;
                    }

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
        $user = $request->user();

        // Admin can delete any product, others need warehouse access
        if (! $user->isAdmin()) {
            $warehouseIds = $user->warehouses()->pluck('id')->toArray();

            $hasAccess = $product->warehouseProducts()
                ->whereIn('warehouse_id', $warehouseIds)
                ->exists();

            if (! $hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or you do not have permission to delete',
                ], 404);
            }
        }

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

            // Delete warehouse products
            $product->warehouseProducts()->delete();

            // Delete product (soft delete if model uses SoftDeletes)
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
