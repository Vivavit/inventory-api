<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;

class WarehouseInventoryController extends Controller
{
    public function addProductToWarehouse(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $warehouseProduct = WarehouseProduct::updateOrCreate(
            [
                'warehouse_id' => $request->warehouse_id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to warehouse inventory',
            'data' => $warehouseProduct,
        ]);
    }

    public function getWarehouseInventory($warehouseId)
    {
        $inventory = WarehouseProduct::where('warehouse_id', $warehouseId)
            ->with('product:id,name,sku,price')
            ->get();

        return response()->json([
            'warehouse_id' => $warehouseId,
            'total_products' => count($inventory),
            'inventory' => $inventory,
        ]);
    }

    public function updateProductQuantity(Request $request, $warehouseId, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $warehouseProduct = WarehouseProduct::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->firstOrFail();

        $warehouseProduct->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated',
            'data' => $warehouseProduct,
        ]);
    }
}
