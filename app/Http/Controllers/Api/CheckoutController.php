<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {

            $order = Order::create([
                'user_id' => auth()->id(),
                'warehouse_id' => $request->warehouse_id,
                'total' => 0,
                'status' => 'completed',
            ]);

            $total = 0;

            foreach ($request->items as $item) {

                $stock = WarehouseProduct::where('warehouse_id', $request->warehouse_id)
                    ->where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    abort(400, 'Product not available in this warehouse');
                }

                if ($stock->quantity < $item['quantity']) {
                    abort(400, 'Not enough stock');
                }

                $stock->decrement('quantity', $item['quantity']);

                $product = Product::findOrFail($item['product_id']);
                $lineSubtotal = $product->price * $item['quantity'];
                $total += $lineSubtotal;

                // Create order item record
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? '',
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ]);

                // 🔥 THIS is what increases analytics
                $product->increment('sold_count', $item['quantity']);
            }

            $order->update(['total' => $total]);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'total' => $total,
            ]);
        });
    }
}
