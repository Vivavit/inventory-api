<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\Product;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request)
    {
        return view('analytics.index');
    }

    /**
     * API endpoint for analytics data.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function data(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $allowedPeriods = ['day', 'week', 'month', 'year'];
        
        if (!in_array($period, $allowedPeriods)) {
            return response()->json(['error' => 'Invalid period'], 422);
        }

        // Cache for 5 minutes
        $cacheKey = "analytics:{$period}";
        
        $data = Cache::remember($cacheKey, 300, function () use ($period) {
            [$startDate, $endDate] = $this->getDateRange($period);
            
            return [
                'kpis' => $this->getKPIs($startDate, $endDate),
                'stock_value_trend' => $this->getStockValueTrend($period, $startDate, $endDate),
                'category_distribution' => $this->getCategoryDistribution(),
                'sales_trend' => $this->getSalesTrend($period, $startDate, $endDate),
                'monthly_performance' => $this->getMonthlyPerformance(),
                'category_momentum' => $this->getCategoryMomentum(),
                'warehouse' => $this->getWarehouseData(),
                'top_products' => $this->getTopProducts($startDate, $endDate),
                'low_stock' => $this->getLowStockItems(),
                'category_summary' => $this->getCategorySummary(),
            ];
        });

        return response()->json($data);
    }

    /**
     * Get date range for specified period.
     */
    private function getDateRange(string $period): array
    {
        $now = Carbon::now();
        
        $start = match ($period) {
            'day' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            'year' => $now->copy()->startOfYear(),
        };
        
        return [$start, $now];
    }

    /**
     * Get date format for grouping.
     */
    private function getGroupFormat(string $period): string
    {
        return match ($period) {
            'day' => '%H:00',
            'week' => '%a',
            'month' => '%d %b',
            'year' => '%b',
        };
    }

    /**
     * Calculate KPI metrics.
     */
    private function getKPIs(Carbon $start, Carbon $end): array
    {
        // Average stock value per product
        $avgStockValue = DB::table('warehouse_products')
            ->join('products', 'products.id', '=', 'warehouse_products.product_id')
            ->where('products.is_active', true)
            ->avg(DB::raw('products.price * warehouse_products.quantity')) ?? 0;

        // Low stock items (current stock <= threshold)
        $lowStockCount = DB::table('warehouse_products')
            ->join('products', 'products.id', '=', 'warehouse_products.product_id')
            ->where('products.is_active', true)
            ->whereRaw('warehouse_products.quantity > 0 AND warehouse_products.quantity <= products.default_low_stock_threshold')
            ->distinct('warehouse_products.product_id')
            ->count('warehouse_products.product_id');

        // Out of stock items
        $outOfStockCount = DB::table('warehouse_products')
            ->join('products', 'products.id', '=', 'warehouse_products.product_id')
            ->where('products.is_active', true)
            ->where('warehouse_products.quantity', '<=', 0)
            ->distinct('warehouse_products.product_id')
            ->count('warehouse_products.product_id');

        // Sales today
        $salesToday = DB::table('orders')
            ->where('orders.status', 'completed')
            ->whereDate('orders.created_at', Carbon::today())
            ->sum('orders.total') ?? 0;

        // Sales this month
        $salesMonth = DB::table('orders')
            ->where('orders.status', 'completed')
            ->whereMonth('orders.created_at', Carbon::now()->month)
            ->whereYear('orders.created_at', Carbon::now()->year)
            ->sum('orders.total') ?? 0;

        // Sales this year
        $salesYear = DB::table('orders')
            ->where('orders.status', 'completed')
            ->whereYear('orders.created_at', Carbon::now()->year)
            ->sum('orders.total') ?? 0;

        return [
            'average_stock_value' => round($avgStockValue, 2),
            'low_stock_alert_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_sales_today' => round($salesToday, 2),
            'total_sales_month' => round($salesMonth, 2),
            'total_sales_year' => round($salesYear, 2),
        ];
    }

    /**
     * Get stock value trend data.
     */
    private function getStockValueTrend(string $period, Carbon $start, Carbon $end): array
    {
        $format = $this->getGroupFormat($period);
        
        // Calculate daily stock value snapshots
        $rows = DB::table('warehouse_products')
            ->join('products', 'products.id', '=', 'warehouse_products.product_id')
            ->select(DB::raw("DATE_FORMAT(warehouse_products.updated_at, '{$format}') as label"))
            ->selectRaw('AVG(products.price * warehouse_products.quantity) as value')
            ->where('products.is_active', true)
            ->whereBetween('warehouse_products.updated_at', [$start, $end])
            ->groupBy('label')
            ->orderBy(DB::raw('MIN(warehouse_products.updated_at)'))
            ->get();

        return [
            'labels' => $rows->pluck('label')->toArray(),
            'values' => $rows->pluck('value')->map(fn($v) => round($v, 2))->toArray(),
        ];
    }

    /**
     * Get category distribution data.
     */
    private function getCategoryDistribution(): array
    {
        $rows = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->select('categories.name as label')
            ->selectRaw('COUNT(products.id) as value')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('value')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->toArray(),
            'values' => $rows->pluck('value')->toArray(),
        ];
    }

    /**
     * Get sales trend data.
     */
    private function getSalesTrend(string $period, Carbon $start, Carbon $end): array
    {
        $format = $this->getGroupFormat($period);
        
        $rows = DB::table('orders')
            ->select(DB::raw("DATE_FORMAT(created_at, '{$format}') as label"))
            ->selectRaw('SUM(total) as value')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('label')
            ->orderBy(DB::raw('MIN(created_at)'))
            ->get();

        return [
            'labels' => $rows->pluck('label')->toArray(),
            'values' => $rows->pluck('value')->map(fn($v) => round($v, 2))->toArray(),
        ];
    }

    /**
     * Get monthly performance data (last 12 months).
     */
    private function getMonthlyPerformance(): array
    {
        $rows = DB::table('orders')
            ->select(DB::raw("DATE_FORMAT(created_at, '%b %Y') as label"))
            ->selectRaw('SUM(total) as value')
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MIN(created_at)'))
            ->get();

        return [
            'labels' => $rows->pluck('label')->toArray(),
            'values' => $rows->pluck('value')->map(fn($v) => round($v, 2))->toArray(),
        ];
    }

    /**
     * Get category momentum (growth percentage).
     */
    private function getCategoryMomentum(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = $currentMonth->copy()->subSecond();

        $currentSales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', 'completed')
            ->where('orders.created_at', '>=', $currentMonth)
            ->groupBy('categories.id', 'categories.name')
            ->select('categories.name')
            ->selectRaw('SUM(order_items.subtotal) as total')
            ->pluck('total', 'name');

        $lastMonthSales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$lastMonth, $endOfLastMonth])
            ->groupBy('categories.id', 'categories.name')
            ->select('categories.name')
            ->selectRaw('SUM(order_items.subtotal) as total')
            ->pluck('total', 'name');

        $labels = [];
        $values = [];

        foreach ($currentSales as $category => $current) {
            $previous = $lastMonthSales[$category] ?? 0;
            $labels[] = $category;
            
            if ($previous == 0) {
                $values[] = $current > 0 ? 100 : 0;
            } else {
                $values[] = round((($current - $previous) / $previous) * 100, 1);
            }
        }

        return compact('labels', 'values');
    }

    /**
     * Get warehouse utilization data.
     */
    private function getWarehouseData(): array
    {
        $warehouses = DB::table('warehouses')
            ->leftJoin('warehouse_products', 'warehouses.id', '=', 'warehouse_products.warehouse_id')
            ->select(
                'warehouses.id',
                'warehouses.name',
                DB::raw('COALESCE(SUM(warehouse_products.quantity), 0) as items')
            )
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get();

        $rows = $warehouses->map(function ($wh) {
            // Assume typical warehouse capacity of 1000 units for demo
            $capacity = 1000;
            $utilization = $capacity > 0 
                ? round(($wh->items / $capacity) * 100, 1) 
                : 0;
            
            return [
                'id' => $wh->id,
                'name' => $wh->name,
                'capacity' => (int) $capacity,
                'items' => (int) $wh->items,
                'utilization' => min($utilization, 100),
            ];
        });

        return [
            'rows' => $rows->toArray(),
            'chart' => [
                'labels' => $rows->pluck('name')->toArray(),
                'capacity' => $rows->pluck('capacity')->toArray(),
                'used' => $rows->pluck('items')->toArray(),
            ],
        ];
    }

    /**
     * Get top performing products.
     */
    private function getTopProducts(Carbon $start, Carbon $end): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as sales'),
                DB::raw('SUM(order_items.subtotal) as value')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('value')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'sales' => (int) $product->sales,
                    'value' => round($product->value, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Get low stock items.
     */
    private function getLowStockItems(): array
    {
        return DB::table('warehouse_products')
            ->join('products', 'products.id', '=', 'warehouse_products.product_id')
            ->where('products.is_active', true)
            ->whereRaw('warehouse_products.quantity <= products.default_low_stock_threshold')
            ->select(
                'products.name',
                DB::raw('SUM(warehouse_products.quantity) as current'),
                'products.default_low_stock_threshold as threshold'
            )
            ->groupBy('products.id', 'products.name', 'products.default_low_stock_threshold')
            ->orderBy('current')
            ->limit(20)
            ->get()
            ->map(fn($item) => [
                'name' => $item->name,
                'current' => (int) $item->current,
                'threshold' => (int) $item->threshold,
            ])
            ->toArray();
    }

    /**
     * Get category summary with stock health.
     */
    private function getCategorySummary(): array
    {
        return DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('warehouse_products', 'products.id', '=', 'warehouse_products.product_id')
            ->where('products.is_active', true)
            ->select(
                'categories.name',
                DB::raw('COUNT(DISTINCT products.id) as products'),
                DB::raw('SUM(products.price * warehouse_products.quantity) as value'),
                DB::raw('AVG(products.price) as avg_price'),
                DB::raw('AVG(CASE WHEN warehouse_products.quantity > products.default_low_stock_threshold THEN 100 
                     WHEN warehouse_products.quantity > 0 THEN 50 
                     ELSE 0 END) as stock_health')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('value')
            ->get()
            ->map(fn($cat) => [
                'name' => $cat->name,
                'products' => (int) $cat->products,
                'value' => round($cat->value ?? 0, 2),
                'avg_price' => round($cat->avg_price ?? 0, 2),
                'stock_health' => round($cat->stock_health ?? 0, 1),
            ])
            ->toArray();
    }
}