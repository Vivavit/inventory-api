<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $query = Order::with(['user', 'warehouse', 'items.product']);

        // Apply filters
        if (request('status')) {
            $query->where('status', request('status'));
        }
        
        if (request('customer')) {
            $query->where('user_id', request('customer'));
        }
        
        if (request('warehouse')) {
            $query->where('warehouse_id', request('warehouse'));
        }
        
        if (request('from_date')) {
            $query->whereDate('created_at', '>=', request('from_date'));
        }
        
        if (request('to_date')) {
            $query->whereDate('created_at', '<=', request('to_date'));
        }

        $orders = $query->latest()->paginate(20);

        // Get data for the modal
        $customers = \App\Models\User::where('user_type', 'customer')->get();
        $warehouses = \App\Models\Warehouse::all();
        $products = \App\Models\Product::with(['inventoryLocations', 'warehouseProducts'])->where('is_active', true)->get();

        return view('orders.index', compact('orders', 'customers', 'warehouses', 'products'));
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
            'user_id' => 'nullable|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'nullable|in:pending,processing,completed,cancelled',
        ]);

        try {
            $result = \DB::transaction(function () use ($request) {
                $order = Order::create([
                    'user_id' => $request->user_id ?: 1, // Use user_id from request or default to 1
                    'warehouse_id' => $request->warehouse_id,
                    'total' => 0,
                    'status' => $request->status ?? 'pending',
                ]);

                $total = 0;

                foreach ($request->items as $item) {
                    $stock = \App\Models\WarehouseProduct::where('warehouse_id', $request->warehouse_id)
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$stock || $stock->quantity < $item['quantity']) {
                        throw new \Exception('Not enough stock for product ID ' . $item['product_id'] . ' in selected warehouse.');
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

                return $order;
            });

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'order' => $result,
                    'redirect' => route('orders.show', $result)
                ]);
            }

            return redirect()->route('orders.show', $result)->with('success', 'Order created successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['general' => [$e->getMessage()]]
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => $e->getMessage()]);
        }
    }

    public function edit(Order $order)
    {
        $order->load(['user', 'warehouse', 'items.product']);
        
        // Get data for the modal
        $customers = \App\Models\User::where('user_type', 'customer')->get();
        $warehouses = \App\Models\Warehouse::all();
        $products = \App\Models\Product::with(['inventoryLocations', 'warehouseProducts'])->where('is_active', true)->get();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'order' => $order,
                'customers' => $customers,
                'warehouses' => $warehouses,
                'products' => $products
            ]);
        }

        return view('orders.edit', compact('order', 'customers', 'warehouses', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'nullable|in:pending,processing,completed,cancelled',
        ]);

        try {
            $result = \DB::transaction(function () use ($request, $order) {
                // First, restore stock for existing items
                foreach ($order->items as $existingItem) {
                    \App\Models\WarehouseProduct::where('warehouse_id', $order->warehouse_id)
                        ->where('product_id', $existingItem->product_id)
                        ->increment('quantity', $existingItem->quantity);
                    
                    $existingItem->product->decrement('sold_count', $existingItem->quantity);
                }

                // Delete existing items
                $order->items()->delete();

                // Update order basic info
                $order->update([
                    'user_id' => $request->user_id ?: $order->user_id,
                    'warehouse_id' => $request->warehouse_id,
                    'status' => $request->status ?? $order->status,
                ]);

                $total = 0;

                foreach ($request->items as $item) {
                    $stock = \App\Models\WarehouseProduct::where('warehouse_id', $request->warehouse_id)
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$stock || $stock->quantity < $item['quantity']) {
                        throw new \Exception('Not enough stock for product ID ' . $item['product_id'] . ' in selected warehouse.');
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

                return $order->fresh(['user', 'warehouse', 'items.product']);
            });

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order updated successfully',
                    'order' => $result
                ]);
            }

            return redirect()->route('orders.show', $result)->with('success', 'Order updated successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['general' => [$e->getMessage()]]
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => $e->getMessage()]);
        }
    }

    public function destroy(Order $order)
    {
        try {
            \DB::transaction(function () use ($order) {
                // Restore stock
                foreach ($order->items as $item) {
                    \App\Models\WarehouseProduct::where('warehouse_id', $order->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->increment('quantity', $item->quantity);
                    
                    $item->product->decrement('sold_count', $item->quantity);
                }

                $order->items()->delete();
                $order->delete();
            });

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order deleted successfully'
                ]);
            }

            return redirect()->route('orders.index')->with('success', 'Order deleted successfully');

        } catch (\Exception $e) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->withErrors(['general' => $e->getMessage()]);
        }
    }

    public function getData(Order $order)
    {
        $order->load(['user', 'warehouse', 'items.product']);
        
        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    public function exportExcel()
    {
        $orderIds = request('orders', []);
        
        if (empty($orderIds)) {
            return redirect()->back()->with('error', 'No orders selected for export');
        }

        $orders = Order::with(['user', 'warehouse', 'items.product'])
            ->whereIn('id', $orderIds)
            ->get();

        // Create CSV content
        $csvData = [];
        
        // Header row
        $csvData[] = ['Order ID', 'Customer', 'Customer Email', 'Warehouse', 'Status', 'Total', 'Order Date', 'Product', 'SKU', 'Quantity', 'Unit Price', 'Subtotal'];
        
        // Data rows
        foreach ($orders as $order) {
            foreach ($order->items as $index => $item) {
                $csvData[] = [
                    $order->id,
                    $order->user ? $order->user->name : 'N/A',
                    $order->user ? $order->user->email : '',
                    $order->warehouse ? $order->warehouse->name : 'N/A',
                    ucfirst($order->status),
                    '$' . number_format($order->total, 2),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $item->product_name,
                    $item->product_sku,
                    $item->quantity,
                    '$' . number_format($item->price, 2),
                    '$' . number_format($item->subtotal, 2)
                ];
            }
        }

        // Generate CSV
        $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';
        $output = fopen('php://temp', 'w');
        
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
