@extends('layouts.app')

@section('title', 'Inventory Analytics')

@section('content')

<style>
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

    .kpi-card-icon {
        font-size: 32px;
        margin-bottom: 8px;
        opacity: 0.8;
    }

    .kpi-card-label {
        font-size: 12px;
        color: #999;
        font-weight: 500;
    }

    .kpi-card-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--green);
        margin: 8px 0 0 0;
    }

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

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }

    .chart-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--green);
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
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

    .warehouse-card {
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 20px;
        background: white;
    }

    .warehouse-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .warehouse-name {
        margin: 0;
        color: var(--green);
        font-weight: 600;
    }

    .warehouse-bar {
        width: 100%;
        height: 6px;
        background: #f0f0f0;
        border-radius: 3px;
        overflow: hidden;
        margin: 8px 0 12px 0;
    }

    .warehouse-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--green), var(--teal));
    }

    .warehouse-stats {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #999;
    }

    .upgrade-section {
        background: linear-gradient(135deg, var(--green), #0fb9b1);
        border-radius: 12px;
        padding: 32px;
        color: white;
        text-align: center;
        margin-top: 32px;
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

<!-- TOP KPI METRICS -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: var(--teal);"><i class="bi bi-cash-coin"></i></div>
        <div class="kpi-card-label">Average Stock Value</div>
        <div class="kpi-card-value">${{ number_format($metrics['average_stock_value'], 0) }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #ff9800;"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="kpi-card-label">Low Stock Items</div>
        <div class="kpi-card-value">{{ $metrics['low_stock_alert_count'] }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #ff6b6b;"><i class="bi bi-x-circle"></i></div>
        <div class="kpi-card-label">Out of Stock</div>
        <div class="kpi-card-value">{{ $metrics['out_of_stock_count'] ?? 0 }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #2ecc71;"><i class="bi bi-bag-check"></i></div>
        <div class="kpi-card-label">Sales Today</div>
        <div class="kpi-card-value">{{ $metrics['total_sales_today'] }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: var(--blue);"><i class="bi bi-calendar-check"></i></div>
        <div class="kpi-card-label">Sales This Month</div>
        <div class="kpi-card-value">{{ $metrics['total_sales_month'] }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: var(--green);"><i class="bi bi-graph-up"></i></div>
        <div class="kpi-card-label">Sales This Year</div>
        <div class="kpi-card-value">{{ $metrics['total_sales_year'] }}</div>
    </div>
</div>

<!-- ANALYTICS SECTION WITH TABS -->
<div class="dashboard-section">
    <!-- TABS -->
    <div class="dashboard-tabs" id="analyticsTabs">
        <button class="tab-btn active" data-tab="overview" title="Overview">
            <i class="bi bi-speedometer2"></i>
            <span>Overview</span>
        </button>
        <button class="tab-btn" data-tab="trends" title="Trends">
            <i class="bi bi-graph-up"></i>
            <span>Trends</span>
        </button>
        <button class="tab-btn" data-tab="warehouse" title="Warehouse">
            <i class="bi bi-building"></i>
            <span>Warehouse</span>
        </button>
        <button class="tab-btn" data-tab="reports" title="Reports">
            <i class="bi bi-file-text"></i>
            <span>Reports</span>
        </button>
    </div>

    <!-- TAB CONTENT -->
    <div class="tab-content">
        
        <!-- OVERVIEW TAB -->
<div class="tab-pane active" id="overview">
    <!-- Period Filter -->
    <div style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="color: #666; font-weight: 500;">Period:</span>
            <div class="filter-tabs" style="margin: 0;">
                @foreach(['day' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $key => $label)
                    <a href="?period={{ $key }}" class="filter-tab {{ $period == $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="chart-grid">
        <!-- Stock Value Trend -->
        <div class="chart-card">
            <h3 class="chart-title">
                <i class="bi bi-graph-up"></i> Stock Value Trend
            </h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="stockValueChart"></canvas>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="chart-card">
            <h3 class="chart-title">
                <i class="bi bi-pie-chart"></i> Inventory by Category
            </h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Sales Distribution -->
    <div class="chart-card" style="margin-bottom: 24px;">
        <h3 class="chart-title">
            <i class="bi bi-bag-check"></i> Sales Trend (Last 7 Days)
        </h3>
        <div style="position: relative; height: 250px; width: 100%;">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <!-- Top Products & Low Stock -->
    <div class="chart-grid">
        <div class="chart-card">
            <h3 class="chart-title">
                <i class="bi bi-star-fill" style="color: #ffd700;"></i> Top Products
            </h3>
            <table class="stat-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Sales</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($charts['top_products'] ?? [] as $product)
                        <tr>
                            <td>{{ substr($product['name'], 0, 20) }}</td>
                            <td><span class="badge badge-success">{{ $product['count'] }}</span></td>
                            <td>${{ number_format($product['value'], 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="chart-card">
            <h3 class="chart-title">
                <i class="bi bi-exclamation-circle" style="color: #ff6b6b;"></i> Low Stock Items
            </h3>
            <table class="stat-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock</th>
                        <th>Threshold</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($charts['low_stock_items'] ?? [] as $item)
                        <tr>
                            <td>{{ substr($item['name'], 0, 20) }}</td>
                            <td><span class="badge badge-warning">{{ $item['current'] }}</span></td>
                            <td>{{ $item['threshold'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">All items well stocked!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Add this to your stylesheet to fix chart sizing */
    .chart-card canvas {
        max-width: 100%;
    }
</style>

<script>
// Update your chart initialization with proper options
function initCharts() {
    // Stock Value Trend
    const stockValueData = @json($charts['stock_value_trend'] ?? []);
    if (document.getElementById('stockValueChart') && stockValueData.labels) {
        const stockCtx = document.getElementById('stockValueChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'line',
            data: {
                labels: stockValueData.labels || [],
                datasets: [{
                    label: 'Stock Value ($)',
                    data: stockValueData.values || [],
                    borderColor: '#03624C',
                    backgroundColor: 'rgba(3, 98, 76, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#03624C',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { 
                    legend: { display: false } 
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { drawBorder: false } 
                    },
                    x: { 
                        grid: { display: false } 
                    }
                }
            }
        });
    }

    // Category Distribution
    const categoryData = @json($charts['category_distribution'] ?? []);
    if (document.getElementById('categoryChart') && categoryData.labels) {
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.labels || [],
                datasets: [{
                    data: categoryData.values || [],
                    backgroundColor: ['#03624C', '#0fb9b1', '#4facfe', '#ff6b6b', '#ffd93d', '#6b5b95'],
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: { 
                    legend: { position: 'bottom' } 
                }
            }
        });
    }

    // Sales Trend
    const salesTrendData = @json($charts['sales_trend'] ?? []);
    if (document.getElementById('salesTrendChart') && salesTrendData.labels) {
        const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: salesTrendData.labels || [],
                datasets: [{
                    label: 'Sales',
                    data: salesTrendData.values || [],
                    backgroundColor: 'rgba(3, 98, 76, 0.2)',
                    borderColor: '#03624C',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { 
                    legend: { display: false } 
                },
                scales: { 
                    y: { beginAtZero: true }, 
                    x: { grid: { display: false } } 
                }
            }
        });
    }
}

// Call after tab content loads
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
});
</script>

        <!-- TRENDS TAB -->
        <div class="tab-pane" id="trends">
            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="bi bi-graph-up"></i> Monthly Sales Trend
                    </h3>
                    <canvas id="monthlySalesChart" height="300"></canvas>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="bi bi-graph-down"></i> Stock Movement
                    </h3>
                    <canvas id="stockMovementChart" height="300"></canvas>
                </div>
            </div>

            <div class="chart-card" style="margin-bottom: 24px;">
                <h3 class="chart-title">
                    <i class="bi bi-bar-chart"></i> Category Performance
                </h3>
                <canvas id="categoryPerformanceChart" height="350"></canvas>
            </div>
        </div>

        <!-- WAREHOUSE TAB -->
        <div class="tab-pane" id="warehouse">
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0 0 16px; color: var(--green); font-weight: 700;">Warehouse Performance</h3>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                @forelse($charts['warehouse_utilization'] ?? [] as $warehouse)
                    <div class="warehouse-card">
                        <div class="warehouse-header">
                            <h4 class="warehouse-name">{{ $warehouse->name }}</h4>
                            <span class="badge {{ $warehouse->utilization > 80 ? 'badge-danger' : ($warehouse->utilization > 60 ? 'badge-warning' : 'badge-success') }}"
                                  style="font-size: 12px;">
                                {{ $warehouse->utilization }}%
                            </span>
                        </div>
                        
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #999; margin-bottom: 8px;">
                                <span>Capacity</span>
                                <span>{{ $warehouse->used_capacity }} / {{ $warehouse->total_capacity }}</span>
                            </div>
                            <div class="warehouse-bar">
                                <div class="warehouse-fill" style="width: {{ $warehouse->utilization }}%;"></div>
                            </div>
                        </div>
                        
                        <div class="warehouse-stats">
                            <span><i class="bi bi-box"></i> {{ $warehouse->item_count }} items</span>
                            <span>{{ $warehouse->utilization > 80 ? 'Overutilized' : ($warehouse->utilization > 60 ? 'Moderate' : 'Optimal') }}</span>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;">
                        <i class="bi bi-warehouse" style="font-size: 48px; margin-bottom: 12px; display: block;"></i>
                        <p>No warehouse data available</p>
                    </div>
                @endforelse
            </div>

            <!-- Warehouse Comparison Chart -->
            <div class="chart-card" style="margin-top: 24px;">
                <h3 class="chart-title">
                    <i class="bi bi-building"></i> Warehouse Comparison
                </h3>
                <canvas id="warehouseComparisonChart" height="300"></canvas>
            </div>
        </div>

        <!-- REPORTS TAB -->
        <div class="tab-pane" id="reports">
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0 0 16px; color: var(--green); font-weight: 700;">Analytics Summary</h3>
            </div>

            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="bi bi-pie-chart"></i> Stock Distribution by Category
                    </h3>
                    <canvas id="categoryDistributionChart" height="300"></canvas>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="bi bi-boxes"></i> Inventory Summary
                    </h3>
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total Items in Stock</td>
                                <td><strong>{{ $charts['total_items_count'] ?? 0 }}</strong></td>
                            </tr>
                            <tr>
                                <td>Total Stock Value</td>
                                <td><strong>${{ number_format($charts['total_stock_value'] ?? 0, 0) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Items in Low Stock</td>
                                <td><span class="badge badge-warning">{{ $charts['low_stock_count'] ?? 0 }}</span></td>
                            </tr>
                            <tr>
                                <td>Out of Stock Items</td>
                                <td><span class="badge badge-danger">{{ $charts['out_of_stock_count'] ?? 0 }}</span></td>
                            </tr>
                            <tr>
                                <td>Active Warehouses</td>
                                <td><strong>{{ count($charts['warehouse_utilization'] ?? []) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Average Warehouse Utilization</td>
                                <td><strong>{{ number_format(collect($charts['warehouse_utilization'] ?? [])->avg('utilization'), 1) }}%</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detailed Report -->
            <div class="chart-card" style="margin-top: 24px;">
                <h3 class="chart-title">
                    <i class="bi bi-file-text"></i> Detailed Metrics
                </h3>
                <div style="overflow-x: auto;">
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Items</th>
                                <th>Value</th>
                                <th>Avg Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charts['category_summary'] ?? [] as $cat)
                                <tr>
                                    <td>{{ $cat['name'] }}</td>
                                    <td>{{ $cat['count'] }}</td>
                                    <td>${{ number_format($cat['value'], 0) }}</td>
                                    <td>${{ number_format($cat['avg_price'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #999;">No category data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                
                localStorage.setItem('activeAnalyticsTab', tabName);
            });
        });
        
        const savedTab = localStorage.getItem('activeAnalyticsTab') || 'overview';
        const savedButton = document.querySelector(`[data-tab="${savedTab}"]`);
        if (savedButton) savedButton.click();

        // Initialize Charts
        initCharts();
    });

    function initCharts() {
        // Stock Value Trend
        const stockValueData = @json($charts['stock_value_trend'] ?? []);
        if (document.getElementById('stockValueChart') && stockValueData.labels) {
            new Chart(document.getElementById('stockValueChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: stockValueData.labels || [],
                    datasets: [{
                        label: 'Stock Value ($)',
                        data: stockValueData.values || [],
                        borderColor: '#03624C',
                        backgroundColor: 'rgba(3, 98, 76, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#03624C',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { drawBorder: false } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Category Distribution
        const categoryData = @json($charts['category_distribution'] ?? []);
        if (document.getElementById('categoryChart') && categoryData.labels) {
            new Chart(document.getElementById('categoryChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: categoryData.labels || [],
                    datasets: [{
                        data: categoryData.values || [],
                        backgroundColor: ['#03624C', '#0fb9b1', '#4facfe', '#ff6b6b', '#ffd93d', '#6b5b95'],
                        borderWidth: 1,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // Sales Trend
        const salesTrendData = @json($charts['sales_trend'] ?? []);
        if (document.getElementById('salesTrendChart') && salesTrendData.labels) {
            new Chart(document.getElementById('salesTrendChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: salesTrendData.labels || [],
                    datasets: [{
                        label: 'Sales',
                        data: salesTrendData.values || [],
                        backgroundColor: 'rgba(3, 98, 76, 0.2)',
                        borderColor: '#03624C',
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
                }
            });
        }
    }
</script>
@endpush

@endsection