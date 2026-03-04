<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryLocation;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $metrics = [
            'inventory_turnover'   => $this->calculateInventoryTurnover(),
            'stock_out_rate'       => $this->getStockOutRate(),
            'average_stock_value'  => $this->getInventoryValue(),
            'low_stock_alert_count'=> $this->getLowStockCount(),
            'fast_moving_items'    => $this->getFastMovingItems(),
            'slow_moving_items'    => $this->getSlowMovingItems(),
            'total_sales_today'    => $this->getTotalSalesToday(),
            'total_sales_month'    => $this->getTotalSalesMonth(),
            'total_sales_year'     => $this->getTotalSalesYear(),
            'out_of_stock_count'   => $this->getOutOfStockCount(),
        ];

        $charts = [
            'stock_value_trend'      => $this->getStockValueTrend(),
            'category_distribution'  => $this->getCategoryDistribution(),
            'warehouse_utilization'  => $this->getWarehouseUtilization(),
            'sales_trend'            => $this->getSalesTrend(),
        ];

        return view('analytics.index', [
            'metrics' => $metrics,
            'charts'  => $charts,
            'period'  => $request->get('period', 'day'),
        ]);
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------------------------

    private function calculateInventoryTurnover()
    {
        // TODO: implement real COGS / average inventory calculation
        return 1.5;
    }

    private function getStockOutRate()
    {
        $totalProducts = Product::count();
        $outOfStock    = $this->getOutOfStockCount();

        return $totalProducts > 0 ? round(($outOfStock / $totalProducts) * 100, 1) : 0;
    }

    private function getInventoryValue(): float
    {
        $value = DB::table('inventory_locations')
            ->join('products', 'products.id', '=', 'inventory_locations.product_id')
            ->sum(DB::raw('inventory_locations.quantity * COALESCE(products.cost_price, 0)'));

        return (float) $value;
    }

    private function getLowStockCount(): int
    {
        return Product::whereHas('inventoryLocations', function ($q) {
            $q->where('quantity', '>', 0)
              ->where('quantity', '<=', 10);
        })->count();
    }

    private function getOutOfStockCount(): int
    {
        // Use a single aggregating query instead of PHP-level chunking
        return (int) Product::whereDoesntHave('inventoryLocations', function ($q) {
            $q->where('quantity', '>', 0);
        })->count();
    }

    private function getFastMovingItems()
    {
        return collect([]);
    }

    private function getSlowMovingItems()
    {
        return collect([]);
    }

    private function getStockValueTrend(): array
    {
        return [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'values' => [10000, 12000, 11000, 13000, 12500, 14000, 15000],
        ];
    }

    private function getCategoryDistribution(): array
    {
        $categories = Category::withCount('products')->get();

        return [
            'labels' => $categories->pluck('name'),
            'values' => $categories->pluck('products_count'),
        ];
    }

    private function getWarehouseUtilization()
    {
        return Warehouse::withCount('inventoryLocations')
            ->get()
            ->map(function ($warehouse) {
                $totalCapacity   = $warehouse->capacity ?? 100;
                $usedCapacity    = $warehouse->inventory_locations_count;
                $utilization     = $totalCapacity > 0
                    ? min(100, ($usedCapacity / $totalCapacity) * 100)
                    : 0;

                $warehouse->used_capacity  = $usedCapacity;
                $warehouse->total_capacity = $totalCapacity;
                $warehouse->utilization    = round($utilization, 1);
                $warehouse->item_count     = $usedCapacity;

                return $warehouse;
            });
    }

    private function getTotalSalesToday()
    {
        $tx = InventoryTransaction::where('type', 'sale')
            ->whereDate('created_at', today())
            ->sum(DB::raw('ABS(quantity_change)'));

        return $tx ?: Product::sum('sold_count');
    }

    private function getTotalSalesMonth()
    {
        $tx = InventoryTransaction::where('type', 'sale')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum(DB::raw('ABS(quantity_change)'));

        return $tx ?: Product::sum('sold_count');
    }

    private function getTotalSalesYear()
    {
        $tx = InventoryTransaction::where('type', 'sale')
            ->whereYear('created_at', now()->year)
            ->sum(DB::raw('ABS(quantity_change)'));

        return $tx ?: Product::sum('sold_count');
    }

    private function getTotalSalesWeek()
    {
        $tx = InventoryTransaction::where('type', 'sale')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum(DB::raw('ABS(quantity_change)'));

        return $tx ?: Product::sum('sold_count');
    }

    private function getSalesTrend(): array
    {
        $hasTx = InventoryTransaction::where('type', 'sale')->exists();
        $sales  = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            if ($hasTx) {
                $sales[] = (int) InventoryTransaction::where('type', 'sale')
                    ->whereDate('created_at', $date)
                    ->sum(DB::raw('ABS(quantity_change)'));
            } else {
                $sales[] = (int) round(Product::sum('sold_count') / 7);
            }
        }

        return [
            'labels' => ['6 days ago', '5 days ago', '4 days ago', '3 days ago', '2 days ago', 'Yesterday', 'Today'],
            'values' => $sales,
        ];
    }

    private function getInStockCount(): int
    {
        return Product::whereHas('inventoryLocations', fn ($q) => $q->where('quantity', '>', 0))->count();
    }

    private function getTotalRevenue(): float
    {
        // Single DB-level aggregation; avoids loading all rows into memory
        $revenue = (float) DB::table('inventory_transactions')
            ->join('products', 'products.id', '=', 'inventory_transactions.product_id')
            ->where('inventory_transactions.type', 'sale')
            ->sum(DB::raw('ABS(inventory_transactions.quantity_change) * COALESCE(products.price, 0)'));

        if ($revenue == 0) {
            $revenue = (float) Product::sum(DB::raw('sold_count * price'));
        }

        return $revenue;
    }

    private function getTotalInventoryItems()
    {
        return InventoryLocation::sum('quantity');
    }

    private function getAverageStockLevel(): float
    {
        return round((float) InventoryLocation::avg('quantity'), 2);
    }

    private function getExpiringSoonCount(): int
    {
        return 0; // TODO: implement expiry tracking
    }

    private function getFillRate(): float
    {
        $totalProducts = Product::count();
        $inStock       = $this->getInStockCount();

        return $totalProducts > 0 ? round(($inStock / $totalProducts) * 100, 1) : 0;
    }

    private function getCategoryValue(int $categoryId): float
    {
        return (float) DB::table('products')
            ->join('inventory_locations', 'inventory_locations.product_id', '=', 'products.id')
            ->where('products.category_id', $categoryId)
            ->sum(DB::raw('inventory_locations.quantity * COALESCE(products.cost_price, 0)'));
    }

    private function getTopProductsByValue(int $limit = 5)
    {
        return Product::with('inventoryLocations')
            ->get()
            ->map(function ($product) {
                $totalQuantity = $product->inventoryLocations->sum('quantity');

                return [
                    'id'         => $product->id,
                    'name'       => $product->name,
                    'quantity'   => $totalQuantity,
                    'value'      => $totalQuantity * $product->cost_price,
                    'cost_price' => $product->cost_price,
                ];
            })
            ->sortByDesc('value')
            ->take($limit)
            ->values();
    }

    private function warehouseId(): int
    {
        return request('warehouse_id') ?? auth()->user()->warehouse_id;
    }

    // -------------------------------------------------------------------------
    // PUBLIC API ENDPOINTS
    // -------------------------------------------------------------------------

    public function getSummary(Request $request)
    {
        return response()->json([
            'in_stock'            => $this->getInStockCount(),
            'out_of_stock'        => $this->getOutOfStockCount(),
            'low_stock'           => $this->getLowStockCount(),
            'total_products'      => Product::count(),
            'average_stock_value' => $this->getInventoryValue(),
            'total_sales_today'   => $this->getTotalSalesToday(),
            'total_sales_month'   => $this->getTotalSalesMonth(),
            'total_sales_year'    => $this->getTotalSalesYear(),
            'total_revenue'       => $this->getTotalRevenue(),
        ]);
    }

    public function getSalesChart(Request $request)
    {
        $days = $request->get('period', 'week') === 'week' ? 7 : 30;
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date  = now()->subDays($i)->toDateString();
            $sales = (float) InventoryTransaction::where('type', 'sale')
                ->whereDate('created_at', $date)
                ->sum(DB::raw('ABS(quantity_change)'));

            if ($sales == 0) {
                $sales = (float) DB::table('products')
                    ->whereDate('updated_at', $date)
                    ->sum('sold_count');
            }

            $data[] = ['label' => date('d M', strtotime($date)), 'value' => $sales, 'date' => $date];
        }

        return response()->json($data);
    }

    public function getTrending(Request $request)
    {
        $products = Product::with('category')
            ->where('sold_count', '>', 0)
            ->orderBy('sold_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'sold_count', 'price', 'cost_price', 'category_id'])
            ->map(fn ($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'sold'     => $p->sold_count,
                'revenue'  => $p->sold_count * $p->price,
                'profit'   => ($p->price - $p->cost_price) * $p->sold_count,
                'category' => $p->category->name ?? 'Uncategorized',
            ]);

        return response()->json($products);
    }

    /**
     * Comprehensive metrics for dashboard / mobile / admin.
     * (Merged the two duplicate getMetrics methods into one.)
     */
    public function getMetrics(Request $request)
    {
        $wid = $this->warehouseId();

        return response()->json([
            'inventory' => [
                'total_value'         => $this->getInventoryValue(),
                'total_items'         => $this->getTotalInventoryItems(),
                'average_stock_level' => $this->getAverageStockLevel(),
                // Warehouse-scoped totals
                'warehouse_items'     => WarehouseProduct::where('warehouse_id', $wid)->sum('quantity'),
                'warehouse_value'     => WarehouseProduct::where('warehouse_id', $wid)
                    ->join('products', 'products.id', '=', 'warehouse_products.product_id')
                    ->sum(DB::raw('warehouse_products.quantity * COALESCE(products.cost_price, 0)')),
            ],
            'sales' => [
                'today'      => (float) Order::where('warehouse_id', $wid)->whereDate('created_at', today())->sum(DB::raw('ABS(total)')),
                'this_week'  => $this->getTotalSalesWeek(),
                'this_month' => (float) Order::where('warehouse_id', $wid)->whereMonth('created_at', now()->month)->sum(DB::raw('ABS(total)')),
                'this_year'  => $this->getTotalSalesYear(),
            ],
            'alerts' => [
                'out_of_stock'  => WarehouseProduct::where('warehouse_id', $wid)->where('quantity', 0)->count(),
                'low_stock'     => WarehouseProduct::where('warehouse_id', $wid)->whereBetween('quantity', [1, 10])->count(),
                'expiring_soon' => $this->getExpiringSoonCount(),
            ],
            'performance' => [
                'inventory_turnover' => $this->calculateInventoryTurnover(),
                'stock_out_rate'     => $this->getStockOutRate(),
                'fill_rate'          => $this->getFillRate(),
            ],
        ]);
    }

    public function getInventoryStats()
    {
        $categories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(8)
            ->get()
            ->map(fn ($c) => [
                'name'  => $c->name,
                'count' => $c->products_count,
                'value' => $this->getCategoryValue($c->id),
            ]);

        $warehouses = Warehouse::withCount('inventoryLocations')
            ->get()
            ->map(function ($warehouse) {
                $totalCapacity = $warehouse->capacity ?? 1000;
                $usedCapacity  = $warehouse->inventory_locations_count;
                $utilization   = $totalCapacity > 0
                    ? min(100, ($usedCapacity / $totalCapacity) * 100)
                    : 0;

                return [
                    'id'             => $warehouse->id,
                    'name'           => $warehouse->name,
                    'utilization'    => round($utilization, 1),
                    'used_capacity'  => $usedCapacity,
                    'total_capacity' => $totalCapacity,
                    'status'         => $utilization > 80 ? 'Overutilized'
                                      : ($utilization > 60 ? 'Moderate' : 'Optimal'),
                ];
            });

        return response()->json([
            'categories'            => $categories,
            'warehouses'            => $warehouses,
            'total_inventory_value' => $this->getInventoryValue(),
            'top_products'          => $this->getTopProductsByValue(5),
        ]);
    }

    public function dashboard()
    {
        $wid = $this->warehouseId();

        return response()->json([
            'total_sales' => round(Order::where('warehouse_id', $wid)->sum('total'), 2),
            'total_stock' => WarehouseProduct::where('warehouse_id', $wid)->sum('quantity'),
            'out_of_stock'=> WarehouseProduct::where('warehouse_id', $wid)->where('quantity', 0)->count(),
            'low_stock'   => WarehouseProduct::where('warehouse_id', $wid)->whereBetween('quantity', [1, 10])->count(),
        ]);
    }

    public function summary()
    {
        $wid = $this->warehouseId();

        return response()->json([
            'in_stock'    => WarehouseProduct::where('warehouse_id', $wid)->where('quantity', '>', 0)->count(),
            'out_of_stock'=> WarehouseProduct::where('warehouse_id', $wid)->where('quantity', 0)->count(),
            'low_stock'   => WarehouseProduct::where('warehouse_id', $wid)->whereBetween('quantity', [1, 10])->count(),
        ]);
    }

    public function salesChart()
    {
        $wid    = $this->warehouseId();
        $period = request('period', 'week');

        if ($period === 'week') {
            $start  = Carbon::now()->startOfWeek();
            $orders = Order::where('warehouse_id', $wid)
                ->whereBetween('created_at', [$start, Carbon::now()->endOfWeek()])
                ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $data = [];
            for ($i = 0; $i < 7; $i++) {
                $date   = $start->copy()->addDays($i);
                $data[] = ['label' => $date->format('D'), 'value' => $orders[$date->format('Y-m-d')] ?? 0];
            }

            return response()->json($data);
        }

        if ($period === 'month') {
            $start       = Carbon::now()->startOfMonth();
            $daysInMonth = $start->daysInMonth;
            $dailyOrders = Order::where('warehouse_id', $wid)
                ->whereBetween('created_at', [$start, Carbon::now()->endOfMonth()])
                ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $data = [];
            for ($week = 1; $week <= 5; $week++) {
                $weekStartDay = ($week - 1) * 7 + 1;
                $weekEndDay   = min($week * 7, $daysInMonth);
                $total        = 0.0;

                for ($day = $weekStartDay; $day <= $weekEndDay; $day++) {
                    $total += $dailyOrders[$start->copy()->day($day)->format('Y-m-d')] ?? 0;
                }

                $data[] = ['label' => "W$week", 'value' => $total];

                if ($weekEndDay === $daysInMonth) {
                    break;
                }
            }

            return response()->json($data);
        }

        return response()->json([]);
    }

    public function trending()
    {
        $wid = $this->warehouseId();

        return response()->json(
            WarehouseProduct::where('warehouse_id', $wid)
                ->with('product')
                ->orderBy('sold_count', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($wp) => [
                    'id'   => $wp->product->id,
                    'name' => $wp->product->name,
                    'sold' => $wp->sold_count ?? 0,
                ])
        );
    }

    public function orderAnalytics()
    {
        $wid    = $this->warehouseId();
        $orders = Order::where('warehouse_id', $wid)
            ->with(['user:id,name', 'warehouse:id,name'])
            ->latest()
            ->get(['id', 'user_id', 'warehouse_id', 'total', 'status', 'created_at']);

        return response()->json([
            'orders'        => $orders,
            'total_orders'  => $orders->count(),
            'total_revenue' => round(Order::where('warehouse_id', $wid)->sum('total'), 2),
        ]);
    }

    public function productAnalytics(int $productId)
    {
        $wid              = $this->warehouseId();
        $product          = Product::findOrFail($productId);
        $warehouseProduct = WarehouseProduct::where('warehouse_id', $wid)
            ->where('product_id', $productId)
            ->first();

        return response()->json([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'price'        => $product->price,
            'sold'         => $warehouseProduct->sold_count ?? 0,
            'stock'        => $warehouseProduct->quantity ?? 0,
            'revenue'      => round(($warehouseProduct->sold_count ?? 0) * $product->price, 2),
        ]);
    }
}