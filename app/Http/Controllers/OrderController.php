<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'warehouse', 'items.product'])
            ->latest()
            ->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'warehouse', 'items.product']);

        return view('orders.show', compact('order'));
    }

    public function create()
    {
        $warehouses = \App\Models\Warehouse::all();
        $products = \App\Models\Product::with(['inventoryLocations', 'warehouseProducts'])->where('is_active', true)->get();

        return view('orders.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return \DB::transaction(function () use ($request) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'warehouse_id' => $request->warehouse_id,
                'total' => 0,
                'status' => 'completed',
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $stock = \App\Models\WarehouseProduct::where('warehouse_id', $request->warehouse_id)
                    ->where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $stock || $stock->quantity < $item['quantity']) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['items' => 'Not enough stock for product ID '.$item['product_id'].' in selected warehouse.']);
                }

                $stock->decrement('quantity', $item['quantity']);

                $product = \App\Models\Product::findOrFail($item['product_id']);
                $lineSubtotal = $product->price * $item['quantity'];
                $total += $lineSubtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku ?? '',
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ]);

                $product->increment('sold_count', $item['quantity']);
            }

            $order->update(['total' => $total]);

            return redirect()->route('orders.show', $order)->with('success', 'Order created successfully');
        });
    }
}