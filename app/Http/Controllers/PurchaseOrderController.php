<?php

namespace App\Http\Controllers;

use App\Models\InventoryLocation;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier', 'creator', 'items.product'])
            ->latest()
            ->paginate(20);

        return view('purchase-orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate PO number
            $poNumber = 'PO-'.date('Ymd').'-'.strtoupper(uniqid());

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $taxAmount = $request->tax_amount ?? 0;
            $shippingCost = $request->shipping_cost ?? 0;
            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            // Create purchase order
            $order = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            // Add items
            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'received_quantity' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-orders.show', $order)
                ->with('success', 'Purchase order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to create purchase order: '.$e->getMessage())->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'creator',
            'items.product',
            'items.variant',
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['ordered', 'partially_received'])) {
            return back()->with('error', 'Only ordered or partially received orders can be received.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.received' => 'required|integer|min:0',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        try {
            DB::beginTransaction();

            $allReceived = true;
            $orderReceived = 0;

            foreach ($validated['items'] as $itemData) {
                $item = $purchaseOrder->items()->findOrFail($itemData['id']);

                $totalReceived = $item->received_quantity + $itemData['received'];

                if ($totalReceived > $item->quantity) {
                    throw new \Exception("Cannot receive more than ordered quantity for item: {$item->product->name}");
                }

                // Update received quantity
                $item->update(['received_quantity' => $totalReceived]);
                $orderReceived += $itemData['received'];

                // If received quantity > 0, update inventory
                if ($itemData['received'] > 0) {
                    // Get or create inventory location
                    $location = InventoryLocation::firstOrCreate(
                        [
                            'product_id' => $item->product_id,
                            'warehouse_id' => $validated['warehouse_id'],
                            'product_variant_id' => $item->product_variant_id,
                        ],
                        ['quantity' => 0, 'reserved_quantity' => 0]
                    );

                    // Update inventory
                    $quantityBefore = $location->quantity;
                    $quantityAfter = $quantityBefore + $itemData['received'];
                    $location->update([
                        'quantity' => $quantityAfter,
                        'last_purchase_cost' => $item->unit_price,
                        // Update average cost
                        'average_cost' => $this->calculateAverageCost(
                            $quantityBefore,
                            $location->average_cost ?? 0,
                            $itemData['received'],
                            $item->unit_price
                        ),
                    ]);

                    // Create transaction record
                    InventoryTransaction::create([
                        'type' => 'purchase',
                        'product_id' => $item->product_id,
                        'warehouse_id' => $validated['warehouse_id'],
                        'product_variant_id' => $item->product_variant_id,
                        'quantity_change' => $itemData['received'],
                        'quantity_before' => $quantityBefore,
                        'quantity_after' => $quantityAfter,
                        'user_id' => auth()->id(),
                        'notes' => "Purchase order #{$purchaseOrder->po_number} receipt",
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                    ]);
                }

                // Check if all items are fully received
                if ($item->received_quantity < $item->quantity) {
                    $allReceived = false;
                }
            }

            // Update order status
            $status = $allReceived ? 'received' : 'partially_received';
            $purchaseOrder->update([
                'status' => $status,
                'actual_delivery_date' => $allReceived ? now() : null,
            ]);

            DB::commit();

            return back()->with('success', "Received {$orderReceived} items successfully!");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to receive items: '.$e->getMessage());
        }
    }

    public function updateStatus(PurchaseOrder $purchaseOrder, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ordered,received,cancelled',
        ]);

        $purchaseOrder->update(['status' => $validated['status']]);

        return back()->with('success', 'Order status updated successfully!');
    }

    private function calculateAverageCost($currentQty, $currentCost, $newQty, $newCost)
    {
        if ($currentQty + $newQty == 0) {
            return 0;
        }

        return (($currentQty * $currentCost) + ($newQty * $newCost)) / ($currentQty + $newQty);
    }
}
