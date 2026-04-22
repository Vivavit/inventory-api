<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $purchaseOrderService)
    {
    }

    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $orders = $this->purchaseOrderService
            ->applyFilters($this->purchaseOrderService->query(), $search, $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $dependencies = $this->purchaseOrderService->getFormDependencies();

        return view('purchase-orders.index', array_merge($dependencies, compact('orders', 'search', 'status')));
    }

    /**
     * Show the form for creating a new purchase order
     */
    public function create()
    {
        return view('purchase-orders.create', $this->purchaseOrderService->getFormDependencies());
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'status' => 'nullable|in:draft,pending',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'shipping_cost' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->purchaseOrderService->create($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'id' => $order->id]);
            }

            return redirect()->route('purchase-orders.show', $order)
                ->with('success', 'Purchase order created successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to create purchase: ' . $e->getMessage()], 422);
            }

            return back()->with('error', 'Failed to create purchase order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified purchase order
     */
    public function show(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($request->ajax()) {
            return response()->json($this->purchaseOrderService->toDetailPayload($purchaseOrder));
        }

        $purchaseOrder->load(['supplier', 'creator', 'items.product', 'warehouse']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified purchase order
     */
    public function edit(PurchaseOrder $purchaseOrder, Request $request)
    {
        if ($purchaseOrder->status !== 'pending') {
            if ($request->ajax()) {
                return response()->json(['message' => 'Only pending purchase orders can be edited.'], 422);
            }
            return back()->with('error', 'Only pending purchase orders can be edited.');
        }

        $purchaseOrder->load(['items.product']);
        $dependencies = $this->purchaseOrderService->getFormDependencies();

        if ($request->ajax()) {
            return response()->json(array_merge(['purchaseOrder' => $purchaseOrder], $dependencies));
        }

        return view('purchase-orders.edit', array_merge(['purchaseOrder' => $purchaseOrder], $dependencies));
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            if ($request->ajax()) {
                return response()->json(['message' => 'Only pending purchase orders can be updated.'], 422);
            }
            return back()->with('error', 'Only pending purchase orders can be updated.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $purchaseOrder = $this->purchaseOrderService->update($purchaseOrder, $validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'id' => $purchaseOrder->id]);
            }

            return redirect()->route('purchase-orders.index')
                ->with('success', "Purchase order {$purchaseOrder->po_number} updated successfully!");

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to update purchase: ' . $e->getMessage()], 422);
            }

            return back()->with('error', 'Failed to update purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['ordered', 'partially_received'], true)) {
            return back()->with('error', 'Only ordered or partially received orders can be received.');
        }

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:purchase_order_items,id',
            'items.*.received' => 'required|integer|min:0',
        ]);

        if ((int) $validated['warehouse_id'] !== (int) $purchaseOrder->warehouse_id) {
            return back()->with('error', 'Warehouse mismatch for this purchase order.');
        }

        try {
            $this->purchaseOrderService->receive($purchaseOrder, $validated['items']);

            return back()->with('success', 'Stock received successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to receive stock: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ordered,cancelled',
        ]);

        $allowedTransitions = [
            'draft' => ['pending', 'ordered', 'cancelled'],
            'pending' => ['ordered', 'cancelled'],
        ];

        $currentStatus = $purchaseOrder->status;
        $newStatus = $validated['status'];

        if (! in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            return response()->json(['success' => false, 'message' => 'This status change is not allowed.'], 422);
        }

        $this->purchaseOrderService->updateStatus($purchaseOrder, $newStatus);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    /**
     * Confirm the purchase order and increase stock
     */
    public function confirm(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'Only pending purchase orders can be confirmed.');
        }

        if ($purchaseOrder->items->isEmpty()) {
            return back()->with('error', 'Cannot confirm an empty purchase order.');
        }

        try {
            $this->purchaseOrderService->confirm($purchaseOrder);

            return back()->with('success', 'Purchase order confirmed and stock updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to confirm purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Return product stock history for AJAX history modal
     */
    public function productHistory(Product $product)
    {
        return response()->json($this->purchaseOrderService->getProductHistory($product));
    }

    /**
     * Remove the specified purchase order (pending only)
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'Only pending purchase orders can be deleted.');
        }

        $poNumber = $purchaseOrder->po_number;
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', "Purchase order {$poNumber} deleted successfully!");
    }
}
