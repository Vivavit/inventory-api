<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryLocation;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'total_sales' => round(Order::sum('total'), 2),
            'total_stock' => InventoryLocation::sum('quantity'),
            'out_of_stock' => Product::whereDoesntHave(
                'inventoryLocations',
                fn ($q) => $q->where('quantity', '>', 0)
            )->count(),
            'low_stock' => Product::whereHas(
                'inventoryLocations',
                fn ($q) => $q->whereBetween('quantity', [1, 10])
            )->count(),
        ]);
    }

    /**
     * SUMMARY (CARDS)
     */
    public function summary()
    {
        return response()->json([
            'in_stock' => Product::whereHas(
                'inventoryLocations',
                fn ($q) => $q->where('quantity', '>', 0)
            )->count(),
            'out_of_stock' => Product::whereDoesntHave(
                'inventoryLocations',
                fn ($q) => $q->where('quantity', '>', 0)
            )->count(),
            'low_stock' => Product::whereHas(
                'inventoryLocations',
                fn ($q) => $q->whereBetween('quantity', [1, 10])
            )->count(),
        ]);
    }

    /**
     * SALES CHART
     */
    public function salesChart()
    {
        $period = request('period', 'week');

        if ($period === 'week') {
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::now()->endOfWeek();

            $orders = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $data = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i);
                $formatted = $date->format('Y-m-d');

                $data[] = [
                    'label' => $date->format('D'),
                    'value' => $orders[$formatted] ?? 0,
                ];
            }

            return response()->json($data);
        } elseif ($period === 'month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            // Fetch daily totals in one query
            $dailyOrders = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $data = [];
            $daysInMonth = $start->daysInMonth;
            for ($week = 1; $week <= 5; $week++) {
                $weekStartDay = ($week - 1) * 7 + 1;
                $weekEndDay = min($week * 7, $daysInMonth);

                $total = 0.0;
                for ($day = $weekStartDay; $day <= $weekEndDay; $day++) {
                    $date = $start->copy()->day($day)->format('Y-m-d');
                    $total += $dailyOrders[$date] ?? 0;
                }

                $data[] = [
                    'label' => "W$week",
                    'value' => $total,
                ];

                if ($weekEndDay == $daysInMonth) {
                    break;
                }
            }

            return response()->json($data);
        }

        return response()->json([]);
    }

    /**
     * TRENDING PRODUCTS
     */
    public function trending()
    {
        return response()->json(
            Product::orderBy('sold_count', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sold' => $p->sold_count,
                ])
        );
    }

    /**
     * ORDERS ANALYTICS
     */
    public function orderAnalytics()
    {
        $orders = Order::with(['user:id,name', 'warehouse:id,name'])
            ->latest()
            ->get(['id', 'user_id', 'warehouse_id', 'total', 'status', 'created_at']);

        return response()->json([
            'orders' => $orders,
            'total_orders' => $orders->count(),
            'total_revenue' => round(Order::sum('total'), 2),
        ]);
    }

    /**
     * PRODUCT ANALYTICS
     */
    public function productAnalytics($productId)
    {
        $product = Product::with('inventoryLocations')->findOrFail($productId);

        $stock = $product->inventoryLocations->sum('quantity');

        return response()->json([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'sold' => $product->sold_count,
            'stock' => $stock,
            'revenue' => round($product->sold_count * $product->price, 2),
        ]);
    }

    /**
     * FULL METRICS (MOBILE / ADMIN)
     */
    public function getMetrics()
    {
        return response()->json([
            'inventory' => [
                'total_items' => InventoryLocation::sum('quantity'),
                'total_value' => InventoryLocation::with('product')
                    ->get()
                    ->sum(fn ($l) => $l->quantity * ($l->product->cost_price ?? 0)),
            ],
            'sales' => [
                'today' => InventoryTransaction::where('type', 'sale')
                    ->whereDate('created_at', today())
                    ->sum(DB::raw('ABS(quantity_change)')),
                'month' => InventoryTransaction::where('type', 'sale')
                    ->whereMonth('created_at', now()->month)
                    ->sum(DB::raw('ABS(quantity_change)')),
            ],
            'alerts' => [
                'low_stock' => Product::whereHas('inventoryLocations', fn ($q) => $q->where('quantity', '<=', 10)->where('quantity', '>', 0)
                )->count(),
                'out_of_stock' => Product::whereDoesntHave('inventoryLocations', fn ($q) => $q->where('quantity', '>', 0)
                )->count(),
            ],
        ]);
    }
}
