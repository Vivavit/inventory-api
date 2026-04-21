<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Refund;
use App\Models\TillTransaction;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * Create a refund request for an order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::with('items')->findOrFail($request->order_id);

            // Check if order can be refunded
            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already cancelled',
                ], 400);
            }

            // Check if refund already exists
            $existingRefund = Refund::where('order_id', $order->id)
                ->where('status', '!=', 'rejected')
                ->first();

            if ($existingRefund) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund already exists for this order',
                ], 400);
            }

            // Restore stock for all items
            foreach ($order->items as $item) {
                $stock = WarehouseProduct::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->increment('quantity', $item->quantity);
                }
            }

            // Create refund record
            $refund = Refund::create([
                'order_id' => $order->id,
                'reason' => $request->reason,
                'amount' => $order->total,
                'status' => 'completed',
            ]);

            // Update order status
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'refunded',
            ]);

            // Record refund transaction in till if order has associated till
            $till = $order->warehouse->tills()
                ->whereNull('closed_at')
                ->latest('opened_at')
                ->first();

            if ($till) {
                TillTransaction::create([
                    'till_id' => $till->id,
                    'type' => 'refund',
                    'amount' => $order->total,
                    'order_id' => $order->id,
                    'description' => 'Refund: ' . $request->reason,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'refund' => $refund,
            ], 201);
        });
    }

    /**
     * Get refund details.
     */
    public function show(Refund $refund)
    {
        $refund->load('order');

        return response()->json([
            'success' => true,
            'refund' => $refund,
        ]);
    }

    /**
     * Get refunds for an order.
     */
    public function getOrderRefunds(Order $order)
    {
        $refunds = $order->refund()
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'refund' => $refunds,
        ]);
    }
}

