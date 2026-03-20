@extends('layouts.app')
@section('title','Dashboard')

@section('content')

<style>
    .dashboard-section {
        background: white;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 4px 12px rgba(0,0,0,.04);
        overflow: hidden;
    }

    .dashboard-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #e0e0e0;
        background: white;
        padding: 0;
        margin: 0;
    }

    .tab-btn {
        padding: 16px 24px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #999;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
        margin-bottom: 0;
    }

    .tab-btn:hover {
        color: var(--green);
        background: rgba(3, 98, 76, 0.03);
    }

    .tab-btn.active {
        color: var(--green);
        border-bottom-color: var(--green);
        font-weight: 700;
    }

    .tab-pane {
        display: none;
        animation: slideUp 0.3s ease;
        padding: 24px;
    }

    .tab-pane.active {
        display: block;
    }

    @keyframes slideUp {
        from { 
            opacity: 0; 
            transform: translateY(10px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }

    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        overflow-x: auto;
        padding-bottom: 8px;
    }

    .filter-tab {
        padding: 8px 16px;
        background: #f0f0f0;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.25s ease;
        white-space: nowrap;
    }

    .filter-tab:hover {
        background: var(--mint);
        color: var(--green);
    }

    .filter-tab.active {
        background: var(--green);
        color: white;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .kpi-card {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        transition: all 0.3s ease;
    }

    .kpi-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,.08);
        transform: translateY(-2px);
    }

    .kpi-card-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--green);
        margin: 8px 0;
    }

    .kpi-card-label {
        font-size: 12px;
        color: #999;
        font-weight: 500;
    }

    .kpi-card-icon {
        font-size: 32px;
        margin-bottom: 8px;
        opacity: 0.8;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 4px 12px rgba(0,0,0,.04);
        margin-bottom: 24px;
    }

    .chart-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--green);
        margin: 0 0 16px 0;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }

    .inventory-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .inventory-card {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .inventory-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0,0,0,.12);
    }

    .inventory-card-image {
        width: 100%;
        height: 160px;
        background: var(--mint);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .inventory-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .inventory-card-body {
        padding: 16px;
    }

    .inventory-card-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin: 0 0 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .inventory-card-sku {
        font-size: 11px;
        color: #999;
        margin-bottom: 8px;
    }

    .inventory-card-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .inventory-card-price {
        font-size: 14px;
        font-weight: 600;
        color: var(--green);
    }

    .inventory-card-stock {
        font-size: 12px;
        color: #666;
    }

    .stat-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }

    .stat-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #e0e0e0;
        font-size: 12px;
        color: #666;
    }

    .stat-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .stat-table tr:hover {
        background: #f8f9fa;
    }

    .upgrade-section {
        background: linear-gradient(135deg, var(--green), #0fb9b1);
        border-radius: 12px;
        padding: 32px;
        color: white;
        text-align: center;
        margin-bottom: 32px;
    }

    .upgrade-section h3 {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 12px;
    }

    .upgrade-section p {
        margin: 0 0 20px;
        opacity: 0.9;
    }

    @media (max-width: 768px) {
        .dashboard-tabs {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .tab-btn {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .chart-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- TOP KPI CARDS -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: var(--teal);"><i class="bi bi-box"></i></div>
        <div class="kpi-card-label">Total Items</div>
        <div class="kpi-card-value">{{ $totalStock }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: var(--blue);"><i class="bi bi-collection"></i></div>
        <div class="kpi-card-label">Product Types</div>
        <div class="kpi-card-value">{{ $stats['total_products'] }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #2ecc71;"><i class="bi bi-check-circle"></i></div>
        <div class="kpi-card-label">In Stock</div>
        <div class="kpi-card-value">{{ $stats['total_products'] - $outOfStock }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #ff6b6b;"><i class="bi bi-exclamation-circle"></i></div>
        <div class="kpi-card-label">Low Stock Alerts</div>
        <div class="kpi-card-value">{{ $lowOnStock }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #9b59b6;"><i class="bi bi-currency-dollar"></i></div>
        <div class="kpi-card-label">Inventory Value</div>
        <div class="kpi-card-value">${{ $stats['total_inventory_value'] }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #16a085;"><i class="bi bi-graph-up"></i></div>
        <div class="kpi-card-label">Total Sales</div>
        <div class="kpi-card-value">{{ $stats['total_sales_count'] }}</div>
    </div>
</div>

<!-- DASHBOARD SECTION WITH TABS -->
<div class="dashboard-section">
    <!-- TABS -->
    <div class="dashboard-tabs" id="dashboardTabs">
        <button class="tab-btn active" data-tab="overview" title="Overview">
            <i class="bi bi-speedometer2"></i>
            <span>Overview</span>
        </button>
        <button class="tab-btn" data-tab="analytics" title="Advanced Analytics">
            <i class="bi bi-bar-chart"></i>
            <span>Analytics</span>
        </button>
        <button class="tab-btn" data-tab="inventory" title="Inventory">
            <i class="bi bi-boxes"></i>
            <span>Inventory</span>
        </button>
    </div>

    <!-- TAB CONTENT -->
    <div class="tab-content">
        
        <!-- OVERVIEW TAB -->
        <div class="tab-pane active" id="overview">
            <div class="chart-grid">
                <!-- Top Selling Products Chart -->
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-fire" style="color: #ff6b6b;"></i> Top Selling Products</h3>
                    <div id="topProductsChart" style="height: 300px;"></div>
                </div>

                <!-- Stock Status Pie Chart -->
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-pie-chart"></i> Stock Status</h3>
                    <div id="stockStatusChart" style="height: 300px;"></div>
                </div>
            </div>

            <div class="chart-grid">
                <!-- Warehouse Distribution -->
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-building"></i> Stock by Warehouse</h3>
                    <div id="warehouseChart" style="height: 300px;"></div>
                </div>

                <!-- Products by Category -->
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-tag"></i> Products by Category</h3>
                    <div id="categoryChart" style="height: 300px;"></div>
                </div>
            </div>

            <!-- Top Selling & Low Stock Items -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-star-fill" style="color: ffd700;"></i> Top Products</h3>
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingProducts->take(5) as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td><span class="badge badge-success">{{ $product->sold_count ?? 0 }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #999;">No sales data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-exclamation" style="color: #ff6b6b;"></i> Low Stock Items</h3>
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td><span class="badge badge-warning">{{ $product->total_stock }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #999;">All stocked well!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ANALYTICS TAB -->
        <div class="tab-pane" id="analytics">
            <div style="margin-bottom: 24px;">
                <h3 class="chart-title"><i class="bi bi-graph-up"></i> Detailed Analytics</h3>
            </div>

            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title">Sales Trend (Top 8 Products)</h3>
                    <div id="salesTrendChart" style="height: 350px;"></div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">Warehouse Stock Levels</h3>
                    <div id="warehouseDetailChart" style="height: 350px;"></div>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">Category Performance</h3>
                <div id="categoryDetailChart" style="height: 350px;"></div>
            </div>
        </div>

        <!-- INVENTORY TAB -->
        <div class="tab-pane" id="inventory">
            <div style="margin-bottom: 16px;">
                <h3 style="margin: 0; color: var(--green); font-weight: 700;">Inventory Items</h3>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="filterInventory(event, 'all')">All Items</button>
                <button class="filter-tab" onclick="filterInventory(event, 'in-stock')">In Stock</button>
                <button class="filter-tab" onclick="filterInventory(event, 'low-stock')">Low Stock</button>
                <button class="filter-tab" onclick="filterInventory(event, 'out-stock')">Out of Stock</button>
            </div>

            <!-- Inventory Grid -->
            <div class="inventory-grid" id="inventory-grid">
                @forelse($recentProducts as $product)
                    @php 
                        $stock = $product->total_stock ?? 0;
                        $status = $stock <= 0 ? 'out-stock' : ($stock <= 10 ? 'low-stock' : 'in-stock');
                    @endphp
                    <div class="inventory-card" onclick="location.href='{{ route('products.show', $product) }}'" data-status="{{ $status }}">
                        <div class="inventory-card-image">
                            @if($product->primaryImage)
                                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}">
                            @else
                                <i class="bi bi-image" style="font-size: 48px; color: #ddd;"></i>
                            @endif
                        </div>
                        <div class="inventory-card-body">
                            <h4 class="inventory-card-title" title="{{ $product->name }}">{{ $product->name }}</h4>
                            <div class="inventory-card-sku">SKU: {{ $product->sku }}</div>
                            
                            <div class="inventory-card-info">
                                <span class="inventory-card-price">${{ number_format($product->price, 2) }}</span>
                                <span class="inventory-card-stock">{{ $stock }} units</span>
                            </div>

                            @if($stock <= 0)
                                <span class="badge badge-danger" style="width: 100%; text-align: center; display: block;">Out of Stock</span>
                            @elseif($stock <= 10)
                                <span class="badge badge-warning" style="width: 100%; text-align: center; display: block;">Low Stock</span>
                            @else
                                <span class="badge badge-success" style="width: 100%; text-align: center; display: block;">In Stock</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 48px 24px;">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 12px;"></i>
                        <p style="color: #999; margin: 0;">No products found</p>
                    </div>
                @endforelse
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-right"></i> View All Products
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script>
    // Initialize tabs
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                tabButtons.forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                });
                
                this.classList.add('active');
                const pane = document.getElementById(tabName);
                if (pane) pane.classList.add('active');
                
                localStorage.setItem('activeDashboardTab', tabName);
            });
        });
        
        const savedTab = localStorage.getItem('activeDashboardTab') || 'overview';
        const savedButton = document.querySelector(`[data-tab="${savedTab}"]`);
        if (savedButton) savedButton.click();

        // Initialize Charts
        initCharts();
    });

    function initCharts() {
        // 1. Top Products Bar Chart
        const topProducts = @json($topSellingProducts->map(function($p) { return ['name' => $p->name, 'count' => $p->sold_count ?? 0]; }));
        
        new ApexCharts({
            chart: { type: 'bar', height: 300 },
            series: [{
                name: 'Sales',
                data: topProducts.map(p => p.count)
            }],
            xaxis: {
                categories: topProducts.map(p => p.name.substring(0, 12))
            },
            colors: ['#0fb9b1'],
            tooltip: { theme: 'light' },
            grid: { borderColor: '#f0f0f0' }
        }, document.getElementById('topProductsChart')).render();

        // 2. Stock Status Pie Chart
        const stockStatus = @json($stockStatusDistribution);
        
        new ApexCharts({
            chart: { type: 'donut', height: 300 },
            series: Object.values(stockStatus),
            labels: Object.keys(stockStatus),
            colors: ['#2ecc71', '#f39c12', '#e74c3c'],
            tooltip: { theme: 'light' },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%'
                    }
                }
            }
        }, document.getElementById('stockStatusChart')).render();

        // 3. Warehouse Stock Bar Chart
        const warehouseData = @json($stockByWarehouse);
        
        new ApexCharts({
            chart: { type: 'bar', height: 300 },
            series: [{
                name: 'Stock Units',
                data: Object.values(warehouseData)
            }],
            xaxis: {
                categories: Object.keys(warehouseData)
            },
            colors: ['#3498db'],
            tooltip: { theme: 'light' },
            grid: { borderColor: '#f0f0f0' }
        }, document.getElementById('warehouseChart')).render();

        // 4. Category Distribution Pie Chart
        const categoryData = @json($productsByCategory);
        
        new ApexCharts({
            chart: { type: 'pie', height: 300 },
            series: Object.values(categoryData),
            labels: Object.keys(categoryData),
            colors: ['#03624C', '#0fb9b1', '#2ecc71', '#3498db', '#9b59b6', '#f39c12'],
            tooltip: { theme: 'light' }
        }, document.getElementById('categoryChart')).render();

        // 5. Sales Trend Line Chart (Analytics Tab)
        const topProductsDetail = @json($topSellingProducts->take(8)->map(function($p) { return ['name' => $p->name, 'count' => $p->sold_count ?? 0]; }));
        
        new ApexCharts({
            chart: { type: 'area', height: 350 },
            series: [{
                name: 'Sales',
                data: topProductsDetail.map(p => p.count)
            }],
            xaxis: {
                categories: topProductsDetail.map(p => p.name.substring(0, 15))
            },
            colors: ['#0fb9b1'],
            fill: { opacity: 0.2 },
            stroke: { curve: 'smooth' },
            tooltip: { theme: 'light' },
            grid: { borderColor: '#f0f0f0' }
        }, document.getElementById('salesTrendChart')).render();

        // 6. Warehouse Detail Bar Chart
        const warehouseDetail = @json($stockByWarehouse);
        
        new ApexCharts({
            chart: { type: 'column', height: 350 },
            series: [{
                name: 'Stock Quantity',
                data: Object.values(warehouseDetail)
            }],
            xaxis: {
                categories: Object.keys(warehouseDetail)
            },
            colors: ['#16a085'],
            tooltip: { theme: 'light' },
            grid: { borderColor: '#f0f0f0' }
        }, document.getElementById('warehouseDetailChart')).render();

        // 7. Category Performance Bar Chart
        const categoryDetail = @json($productsByCategory);
        
        new ApexCharts({
            chart: { type: 'bar', height: 350 },
            series: [{
                name: 'Product Count',
                data: Object.values(categoryDetail)
            }],
            xaxis: {
                categories: Object.keys(categoryDetail)
            },
            colors: ['#9b59b6'],
            tooltip: { theme: 'light' },
            grid: { borderColor: '#f0f0f0' }
        }, document.getElementById('categoryDetailChart')).render();
    }

    function filterInventory(event, filter) {
        const cards = document.querySelectorAll('#inventory-grid .inventory-card');
        const buttons = document.querySelectorAll('.filter-tabs .filter-tab');
        
        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        cards.forEach(card => {
            if (filter === 'all') {
                card.style.display = 'block';
            } else {
                const status = card.getAttribute('data-status');
                card.style.display = filter === status ? 'block' : 'none';
            }
        });
    }
</script>
@endpush

@endsection