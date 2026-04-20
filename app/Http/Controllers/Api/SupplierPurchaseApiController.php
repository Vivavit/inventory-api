<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierPurchaseApiController extends Controller
{
    /**
     * Display a listing of supplier purchases
     */
    public function index()
    {
        $purchases = PurchaseOrder::with(['supplier', 'creator', 'items.product'])
            ->whereIn('status', ['pending', 'completed'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Supplier purchases retrieved successfully',
            'data' => $purchases
        ]);
    }

    /**
     * Store a newly created supplier purchase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required_without:items.*.unit_price|numeric|min:0',
            'items.*.unit_price' => 'required_without:items.*.price|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate PO number
            $poNumber = PurchaseOrder::generatePONumber();

            // Calculate total amount
            $totalAmount = 0;
            foreach ($validated['items'] as $index => $item) {
                $price = $item['price'] ?? $item['unit_price'];
                $validated['items'][$index]['price'] = $price;
                $totalAmount += $item['quantity'] * $price;
            }

            // Create purchase order with status 'pending'
            $purchase = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'status' => 'pending',
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'total_amount' => $totalAmount,
                'created_by' => auth()->id(),
                'notes' => $validated['notes'],
            ]);

            // Add items
            foreach ($validated['items'] as $item) {
                $price = $item['price'] ?? $item['unit_price'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'total_price' => $item['quantity'] * $price,
                    'received_quantity' => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier purchase created successfully',
                'data' => $purchase->load('supplier', 'warehouse', 'items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified supplier purchase
     */
    public function edit(PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending purchases can be edited.'
            ], 422);
        }

        $suppliers  = Supplier::where('is_active', true)->get();
        $products   = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => compact('purchase', 'suppliers', 'products', 'warehouses')
        ]);
    }

    /**
     * Update the specified supplier purchase in storage
     */
    public function update(Request $request, PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending purchases can be updated.'
            ], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required_without:items.*.unit_price|numeric|min:0',
            'items.*.unit_price' => 'required_without:items.*.price|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = 0;
            foreach ($validated['items'] as $index => $item) {
                $price = $item['price'] ?? $item['unit_price'];
                $validated['items'][$index]['price'] = $price;
                $totalAmount += $item['quantity'] * $price;
            }

            // Update purchase order
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'],
            ]);

            // Delete existing items and recreate
            $purchase->items()->delete();

            // Add items
            foreach ($validated['items'] as $item) {
                $price = $item['price'] ?? $item['unit_price'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'total_price' => $item['quantity'] * $price,
                    'received_quantity' => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier purchase updated successfully',
                'data' => $purchase->load('supplier', 'warehouse', 'items.product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified supplier purchase
     */
    public function show(PurchaseOrder $purchase)
    {
        return response()->json([
            'success' => true,
            'message' => 'Supplier purchase retrieved successfully',
            'data' => $purchase->load(['supplier', 'creator', 'warehouse', 'items.product'])
        ]);
    }

    /**
     * Confirm the supplier purchase and increase stock
     */
    public function confirm(PurchaseOrder $purchase)
    {
        // Check if purchase can be confirmed
        if ($purchase->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending purchases can be confirmed'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Update purchase status to completed
            $purchase->update(['status' => 'completed']);

            // Increase stock for each item
            foreach ($purchase->items as $item) {
                // Update warehouse product stock
                WarehouseProduct::where('warehouse_id', $purchase->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->update(['quantity' => DB::raw('quantity + ' . $item->quantity)]);

                // Update received quantity to match ordered quantity
                $item->update(['received_quantity' => $item->quantity]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase confirmed and stock updated successfully',
                'data' => $purchase->load('items')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified supplier purchase
     */
    public function destroy(PurchaseOrder $purchase)
    {
        // Only allow deletion of pending purchases
        if ($purchase->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending purchases can be deleted'
            ], 400);
        }

        $purchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase deleted successfully'
        ]);
    }
}
