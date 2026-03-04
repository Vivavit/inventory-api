<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Determine which warehouses to consider. Staff members only have one
        // while admins may manage many. The client app expects a single warehouse
        // id/metadata for staff, but mobile admin should retrieve inventory across
        // all assigned warehouses.
        // load the user's warehouses once; prefix the id column with table name
        // because the pivot table also has an "id" field which would make the
        // column ambiguous when querying.
        $warehouses = $user->warehouses()->get(['warehouses.id as id', 'warehouses.name', 'warehouses.code']);
        $warehouseIds = $warehouses->pluck('id')->toArray();

        if (empty($warehouseIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No warehouse assigned to user. Contact administrator.',
            ], 403);
        }

        // Build base query for products that exist in any of the user's warehouses
        $productsQuery = Product::whereHas('warehouseProducts', function ($query) use ($warehouseIds) {
            $query->whereIn('warehouse_id', $warehouseIds);
        })
            ->with(['images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->where('is_active', true);

        $products = $productsQuery->get()->map(function ($product) use ($warehouseIds, $user) {
            // IMAGE LOGIC unchanged
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

            // compute stock differently for admins vs staff
            if ($user->isAdmin()) {
                // sum across all accessible warehouses
                $stock = (int) DB::table('warehouse_products')
                    ->where('product_id', $product->id)
                    ->whereIn('warehouse_id', $warehouseIds)
                    ->sum('quantity');
            } else {
                // for staff, sum quantities in case there are multiple rows (e.g. the
                // product boot observer creates a zero record then another is added).
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

        // attach warehouse info for staff, or list for admins
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
}
