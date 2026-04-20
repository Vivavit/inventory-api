<?php

namespace App\Http\Controllers;

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

class SupplierPurchaseController extends Controller
{
    /**
     * Display a listing of supplier purchases
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $purchases = PurchaseOrder::with(['supplier', 'creator', 'items.product'])
            ->whereIn('status', ['pending', 'completed'])
            ->when($search, function ($query, $search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('po_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $suppliers  = Supplier::where('is_active', true)->get();
        $products   = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('supplier-purchases.index', compact(
            'purchases', 'suppliers', 'products', 'warehouses', 'search', 'status'
        ));
    }

    /**
     * Show the form for creating a new supplier purchase
     */
    public function create()
    {
        $suppliers  = Supplier::where('is_active', true)->get();
        $products   = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('supplier-purchases.create', compact('suppliers', 'products', 'warehouses'));
    }

    /**
     * Show the form for editing the specified supplier purchase
     */
    public function edit(PurchaseOrder $purchase, Request $request)
    {
        if ($purchase->status !== 'pending') {
            if ($request->ajax()) {
                return response()->json(['message' => 'Only pending purchases can be edited.'], 422);
            }
            return back()->with('error', 'Only pending purchases can be edited.');
        }

        $purchase->load(['items.product']);
        $suppliers  = Supplier::where('is_active', true)->get();
        $products   = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        if ($request->ajax()) {
            return response()->json(compact('purchase', 'suppliers', 'products', 'warehouses'));
        }

        return view('supplier-purchases.edit', compact('purchase', 'suppliers', 'products', 'warehouses'));
    }

    /**
     * Update the specified supplier purchase in storage
     */
    public function update(Request $request, PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'pending') {
            if ($request->ajax()) {
                return response()->json(['message' => 'Only pending purchases can be updated.'], 422);
            }
            return back()->with('error', 'Only pending purchases can be updated.');
        }

        $validated = $request->validate([
            'supplier_id'              => 'required|exists:suppliers,id',
            'warehouse_id'             => 'required|exists:warehouses,id',
            'order_date'               => 'required|date',
            'expected_delivery_date'   => 'nullable|date|after_or_equal:order_date',
            'shipping_cost'            => 'nullable|numeric|min:0',
            'payment_terms'            => 'nullable|string|max:50',
            'notes'                    => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_price'       => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total (items + optional shipping)
            $itemsTotal = 0;
            foreach ($validated['items'] as $item) {
                $itemsTotal += $item['quantity'] * $item['unit_price'];
            }
            $shippingCost = $validated['shipping_cost'] ?? 0;
            $totalAmount  = $itemsTotal + $shippingCost;

            // Update purchase order
            $purchase->update([
                'supplier_id'            => $validated['supplier_id'],
                'warehouse_id'           => $validated['warehouse_id'] ?? null,
                'order_date'             => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'total_amount'           => $totalAmount,
                'shipping_cost'          => $shippingCost,
                'payment_terms'          => $validated['payment_terms'] ?? null,
                'notes'                  => $validated['notes'] ?? null,
            ]);

            // Delete existing items and recreate
            $purchase->items()->delete();

            // Create line items
            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchase->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                    'total_price'       => $item['quantity'] * $item['unit_price'],
                    'received_quantity' => 0,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'id' => $purchase->id]);
            }

            return redirect()->route('supplier-purchases.index')
                ->with('success', "Purchase order {$purchase->po_number} updated successfully!");

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to update purchase: ' . $e->getMessage()], 422);
            }

            return back()->with('error', 'Failed to update purchase: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Store a newly created supplier purchase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'              => 'required|exists:suppliers,id',
            'warehouse_id'             => 'required|exists:warehouses,id',
            'order_date'               => 'required|date',
            'expected_delivery_date'   => 'nullable|date|after_or_equal:order_date',
            'shipping_cost'            => 'nullable|numeric|min:0',
            'payment_terms'            => 'nullable|string|max:50',
            'notes'                    => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_price'       => 'required|numeric|min:0',  // matches blade field name
        ]);

        try {
            DB::beginTransaction();

            // Calculate total (items + optional shipping)
            $itemsTotal = 0;
            foreach ($validated['items'] as $item) {
                $itemsTotal += $item['quantity'] * $item['unit_price'];
            }
            $shippingCost = $validated['shipping_cost'] ?? 0;
            $totalAmount  = $itemsTotal + $shippingCost;

            // Create purchase order
            $purchase = PurchaseOrder::create([
                'po_number'              => PurchaseOrder::generatePONumber(),
                'supplier_id'            => $validated['supplier_id'],
                'warehouse_id'           => $validated['warehouse_id'] ?? null,
                'status'                 => 'pending',
                'order_date'             => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'total_amount'           => $totalAmount,
                'shipping_cost'          => $shippingCost,
                'payment_terms'          => $validated['payment_terms'] ?? null,
                'created_by'             => auth()->id(),
                'notes'                  => $validated['notes'] ?? null,
            ]);

            // Create line items
            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchase->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                    'total_price'       => $item['quantity'] * $item['unit_price'],
                    'received_quantity' => 0,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'id' => $purchase->id]);
            }

            return redirect()->route('supplier-purchases.index')
                ->with('success', "Purchase order {$purchase->po_number} created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to create purchase: ' . $e->getMessage()], 422);
            }

            return back()->with('error', 'Failed to create purchase: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified supplier purchase (supports AJAX for detail modal)
     */
    public function show(Request $request, PurchaseOrder $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'creator', 'warehouse']);

        if ($request->ajax()) {
            // Format dates for the JS detail modal
            $data = $purchase->toArray();
            $data['order_date_formatted']            = $purchase->order_date->format('M d, Y');
            $data['expected_delivery_date_formatted'] = $purchase->expected_delivery_date
                ? $purchase->expected_delivery_date->format('M d, Y')
                : null;

            return response()->json($data);
        }

        return view('supplier-purchases.show', compact('purchase'));
    }

    /**
     * Return product stock history for AJAX history modal
     */
    public function productHistory(Product $product)
    {
        $history = InventoryTransaction::with('warehouse', 'user')
            ->where('product_id', $product->id)
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn($record) => [
                'date' => $record->created_at->format('M d, Y H:i'),
                'warehouse' => $record->warehouse?->name,
                'type' => ucfirst($record->type),
                'change' => $record->quantity_change,
                'before' => $record->quantity_before,
                'after' => $record->quantity_after,
                'notes' => $record->notes,
                'user' => $record->user?->name,
            ]);

        return response()->json([
            'product' => ['id' => $product->id, 'name' => $product->name],
            'history' => $history,
        ]);
    }

    /**
     * Confirm the supplier purchase and increase stock
     */
    public function confirm(PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'pending') {
            return back()->with('error', 'Only pending purchases can be confirmed.');
        }

        if ($purchase->items->isEmpty()) {
            return back()->with('error', 'Cannot confirm an empty purchase.');
        }

        try {
            DB::beginTransaction();

            $purchase->update(['status' => 'completed']);

            foreach ($purchase->items as $item) {
                // Get or create warehouse product record
                $warehouseProduct = WarehouseProduct::firstOrCreate([
                    'warehouse_id' => $purchase->warehouse_id,
                    'product_id'   => $item->product_id,
                ], ['quantity' => 0]);

                // Get quantity before update for transaction record
                $quantityBefore = $warehouseProduct->quantity;

                // Update stock quantity
                $warehouseProduct->increment('quantity', $item->quantity);
                $quantityAfter = $warehouseProduct->quantity;

                // Create inventory transaction record for stock movement tracking
                InventoryTransaction::create([
                    'product_id'       => $item->product_id,
                    'warehouse_id'     => $purchase->warehouse_id,
                    'type'             => 'purchase',
                    'price'            => $item->unit_price,
                    'total'            => $item->quantity * $item->unit_price,
                    'user_id'          => Auth::id(),
                    'reference_type'   => 'purchase_order',
                    'reference_id'     => $purchase->id,
                    'quantity_change'  => $item->quantity,
                    'quantity_before'  => $quantityBefore,
                    'quantity_after'   => $quantityAfter,
                    'notes'            => "Stock increase from PO {$purchase->po_number}",
                ]);

                // Update received quantity on purchase item
                $item->update(['received_quantity' => $item->quantity]);
            }

            DB::commit();

            return back()->with('success', 'Purchase confirmed and stock updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to confirm purchase: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified supplier purchase (pending only)
     */
    public function destroy(PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'pending') {
            return back()->with('error', 'Only pending purchases can be deleted.');
        }

        $poNumber = $purchase->po_number;
        $purchase->delete();

        return redirect()->route('supplier-purchases.index')
            ->with('success', "Purchase order {$poNumber} deleted successfully!");
    }
}