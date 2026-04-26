<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryLocation;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * ANALYTICS PAGE - Main endpoint used by analytics.js
     */
    public function show(string $period)
    {
        $allowed = ['day', 'week', 'month', 'year'];
        if (!in_array($period, $allowed)) {
            return response()->json(['error' => 'Invalid period'], 422);
        }

        [$start, $end] = $this->getPeriodRange($period);

        return response()->json([
            'kpis'                  => $this->getKpis($start, $end),
            'stock_value_trend'     => $this->getStockValueTrend($period),
            'category_distribution' => $this->getCategoryDistribution(),
            'sales_trend'           => $this->getSalesTrend($period, $start, $end),
            'monthly_performance'   => $this->getMonthlyPerformance(),
            'category_momentum'     => $this->getCategoryMomentum(),
            'warehouse'             => $this->getWarehouseData(),
            'top_products'          => $this->getTopProducts($start, $end),
            'low_stock'             => $this->getLowStock(),
            'category_summary'      => $this->getCategorySummary(),
        ]);
    }

    private function getPeriodRange(string $period): array
    {
        return match ($period) {
            'day'   => [Carbon::today(), Carbon::today()->endOfDay()],
            'week'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year'  => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
        };
    }

    private function getKpis(Carbon $start, Carbon $end): array
    {
        $avgStockValue = InventoryLocation::with('product')
            ->get()
            ->sum(fn ($l) => $l->quantity * ($l->product->cost_price ?? 0));

        return [
            'average_stock_value'   => round($avgStockValue, 2),
            'low_stock_alert_count' => Product::whereHas('inventoryLocations',
                fn ($q) => $q->where('quantity', '<=', 10)->where('quantity', '>', 0)
            )->count(),
            'out_of_stock_count'    => Product::whereDoesntHave('inventoryLocations',
                fn ($q) => $q->where('quantity', '>', 0)
            )->count(),
            'total_sales_today'     => round(Order::whereDate('created_at', today())->sum('total'), 2),
            'total_sales_month'     => round(Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->sum('total'), 2),
            'total_sales_year'      => round(Order::whereYear('created_at', now()->year)->sum('total'), 2),
        ];
    }

    private function getStockValueTrend(string $period): array
    {
        $days = match ($period) {
            'day'   => 24,
            'week'  => 7,
            'month' => 30,
            'year'  => 12,
        };

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            if ($period === 'year') {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M Y');
            } elseif ($period === 'day') {
                $date = Carbon::now()->subHours($i);
                $labels[] = $date->format('H:i');
            } else {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('M d');
            }

            // Use current stock value as approximation (replace with snapshots if available)
            $value = InventoryLocation::with('product')
                ->get()
                ->sum(fn ($l) => $l->quantity * ($l->product->cost_price ?? 0));

            $values[] = round($value, 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function getCategoryDistribution(): array
    {
        $data = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('inventory_locations', 'products.id', '=', 'inventory_locations.product_id')
            ->selectRaw('categories.name, SUM(inventory_locations.quantity) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'values' => $data->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    private function getSalesTrend(string $period, Carbon $start, Carbon $end): array
    {
        if ($period === 'year') {
            $rows = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('MONTH(created_at) as m, SUM(total) as total')
                ->groupBy('m')
                ->pluck('total', 'm');

            $labels = [];
            $values = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = Carbon::create()->month($m)->format('M');
                $values[] = round($rows[$m] ?? 0, 2);
            }
        } elseif ($period === 'month') {
            $rows = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as d, SUM(total) as total')
                ->groupBy('d')
                ->pluck('total', 'd');

            $labels = [];
            $values = [];
            $days = $start->daysInMonth;
            for ($i = 1; $i <= $days; $i++) {
                $date = $start->copy()->day($i);
                $labels[] = $date->format('M d');
                $values[] = round($rows[$date->format('Y-m-d')] ?? 0, 2);
            }
        } elseif ($period === 'week') {
            $rows = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as d, SUM(total) as total')
                ->groupBy('d')
                ->pluck('total', 'd');

            $labels = [];
            $values = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i);
                $labels[] = $date->format('D');
                $values[] = round($rows[$date->format('Y-m-d')] ?? 0, 2);
            }
        } else {
            // day — hourly
            $rows = Order::whereDate('created_at', today())
                ->selectRaw('HOUR(created_at) as h, SUM(total) as total')
                ->groupBy('h')
                ->pluck('total', 'h');

            $labels = [];
            $values = [];
            for ($h = 0; $h < 24; $h++) {
                $labels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                $values[] = round($rows[$h] ?? 0, 2);
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function getMonthlyPerformance(): array
    {
        $rows = Order::whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as m, SUM(total) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $labels = [];
        $values = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = Carbon::create()->month($m)->format('M');
            $values[] = round($rows[$m] ?? 0, 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function getCategoryMomentum(): array
    {
        $thisMonth  = now()->month;
        $thisYear   = now()->year;
        $lastMonth  = now()->subMonth()->month;
        $lastYear   = now()->subMonth()->year;

        $current = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereMonth('orders.created_at', $thisMonth)
            ->whereYear('orders.created_at', $thisYear)
            ->selectRaw('categories.name, SUM(order_items.subtotal) as total')
            ->groupBy('categories.id', 'categories.name')
            ->pluck('total', 'name');

        $previous = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereMonth('orders.created_at', $lastMonth)
            ->whereYear('orders.created_at', $lastYear)
            ->selectRaw('categories.name, SUM(order_items.subtotal) as total')
            ->groupBy('categories.id', 'categories.name')
            ->pluck('total', 'name');

        $labels = [];
        $values = [];

        foreach ($current as $name => $curr) {
            $prev = $previous[$name] ?? 0;
            $growth = $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : 0;
            $labels[] = $name;
            $values[] = $growth;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function getWarehouseData(): array
    {
        $warehouses = Warehouse::withCount('inventoryLocations')
            ->with('inventoryLocations')
            ->get();

        $labels   = [];
        $capacity = [];
        $used     = [];
        $rows     = [];

        foreach ($warehouses as $wh) {
            $items = $wh->inventoryLocations->sum('quantity');
            $cap   = $wh->capacity ?? 1000; // fallback if no capacity column
            $utilization = $cap > 0 ? min(round(($items / $cap) * 100), 100) : 0;

            $labels[]   = $wh->name;
            $capacity[] = $cap;
            $used[]     = $items;

            $rows[] = [
                'id'          => $wh->id,
                'name'        => $wh->name,
                'items'       => $items,
                'capacity'    => $cap,
                'utilization' => $utilization,
            ];
        }

        return [
            'chart' => ['labels' => $labels, 'capacity' => $capacity, 'used' => $used],
            'rows'  => $rows,
        ];
    }

    private function getTopProducts(Carbon $start, Carbon $end): array
    {
        return DB::table('products')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('products.name, SUM(order_items.quantity) as sales, SUM(order_items.subtotal) as value')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sales')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'name'  => $r->name,
                'sales' => (int) $r->sales,
                'value' => round($r->value, 2),
            ])
            ->toArray();
    }

    private function getLowStock(): array
    {
        return Product::whereHas('inventoryLocations', fn ($q) => $q->where('quantity', '<=', 10)->where('quantity', '>', 0))
            ->with(['inventoryLocations' => fn ($q) => $q->where('quantity', '<=', 10)])
            ->get()
            ->map(fn ($p) => [
                'name'      => $p->name,
                'current'   => $p->inventoryLocations->sum('quantity'),
                'threshold' => 10,
            ])
            ->toArray();
    }

    private function getCategorySummary(): array
    {
        return DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('inventory_locations', 'products.id', '=', 'inventory_locations.product_id')
            ->selectRaw('
                categories.name,
                COUNT(DISTINCT products.id) as products,
                SUM(inventory_locations.quantity * products.cost_price) as value,
                AVG(products.price) as avg_price
            ')
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->map(fn ($r) => [
                'name'         => $r->name,
                'products'     => (int) $r->products,
                'value'        => round($r->value ?? 0, 2),
                'avg_price'    => round($r->avg_price ?? 0, 2),
                'stock_health' => 100,
            ])
            ->toArray();
    }

    // ── Existing methods kept intact ──────────────────────────────────────────

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

    public function salesChart()
    {
        $period = request('period', 'week');

        if ($period === 'week') {
            $start = Carbon::now()->startOfWeek();
            $end   = Carbon::now()->endOfWeek();

            $orders = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $data = [];
            for ($i = 0; $i < 7; $i++) {
                $date      = $start->copy()->addDays($i);
                $formatted = $date->format('Y-m-d');
                $data[]    = ['label' => $date->format('D'), 'value' => $orders[$formatted] ?? 0];
            }

            return response()->json($data);

        } elseif ($period === 'month') {
            $start       = Carbon::now()->startOfMonth();
            $end         = Carbon::now()->endOfMonth();
            $dailyOrders = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $data        = [];
            $daysInMonth = $start->daysInMonth;
            for ($week = 1; $week <= 5; $week++) {
                $weekStartDay = ($week - 1) * 7 + 1;
                $weekEndDay   = min($week * 7, $daysInMonth);
                $total        = 0.0;
                for ($day = $weekStartDay; $day <= $weekEndDay; $day++) {
                    $date  = $start->copy()->day($day)->format('Y-m-d');
                    $total += $dailyOrders[$date] ?? 0;
                }
                $data[] = ['label' => "W$week", 'value' => $total];
                if ($weekEndDay == $daysInMonth) break;
            }

            return response()->json($data);
        }

        return response()->json([]);
    }

    public function trending()
    {
        return response()->json(
            Product::orderBy('sold_count', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'sold' => $p->sold_count])
        );
    }

    public function orderAnalytics()
    {
        $orders = Order::with(['user:id,name', 'warehouse:id,name'])
            ->latest()
            ->get(['id', 'user_id', 'warehouse_id', 'total', 'status', 'created_at']);

        return response()->json([
            'orders'        => $orders,
            'total_orders'  => $orders->count(),
            'total_revenue' => round(Order::sum('total'), 2),
        ]);
    }

    public function productAnalytics($productId)
    {
        $product = Product::with('inventoryLocations')->findOrFail($productId);
        $stock   = $product->inventoryLocations->sum('quantity');

        return response()->json([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'price'        => $product->price,
            'sold'         => $product->sold_count,
            'stock'        => $stock,
            'revenue'      => round($product->sold_count * $product->price, 2),
        ]);
    }

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
                'low_stock'    => Product::whereHas('inventoryLocations', fn ($q) => $q->where('quantity', '<=', 10)->where('quantity', '>', 0))->count(),
                'out_of_stock' => Product::whereDoesntHave('inventoryLocations', fn ($q) => $q->where('quantity', '>', 0))->count(),
            ],
        ]);
    }
}