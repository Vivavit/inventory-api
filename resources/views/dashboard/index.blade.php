@extends('layouts.app')

@section('title','Dashboard')

@section('content')

<style>
    .dashboard-section {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: 1px solid var(--border-soft);
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
        color: var(--text);
    }

    html.dark .dashboard-section {
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
    }

    .dashboard-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #E9FFFA;
        background: #f8fafc;
        padding: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .tab-btn {
        padding: 16px 28px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        margin-bottom: -2px;
    }

    .tab-btn:hover {
        color: #03624C;
        background: rgba(3, 98, 76, 0.04);
    }

    .tab-btn.active {
        color: #03624C;
        font-weight: 700;
        border-bottom-color: #03624C;
    }

    .tab-pane {
        display: none;
        padding: 28px;
        animation: slideInFade 0.4s ease;
    }

    .tab-pane.active {
        display: block;
    }

    @keyframes slideInFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .kpi-card {
        background: var(--surface);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--border-soft);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
        color: var(--text);
    }

    .kpi-card:nth-child(1) { animation-delay: 0.1s; }
    .kpi-card:nth-child(2) { animation-delay: 0.2s; }
    .kpi-card:nth-child(3) { animation-delay: 0.3s; }
    .kpi-card:nth-child(4) { animation-delay: 0.4s; }
    .kpi-card:nth-child(5) { animation-delay: 0.5s; }
    .kpi-card:nth-child(6) { animation-delay: 0.6s; }

    .kpi-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(3, 98, 76, 0.12);
        border-color: #03624C;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #03624C, #0fb9b1);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }

    .kpi-card:hover::before {
        transform: scaleX(1);
    }

    .kpi-card-icon {
        font-size: 32px;
        margin-bottom: 12px;
        color: #03624C;
    }

    .kpi-card-value {
        font-size: 28px;
        font-weight: 800;
        color: #03624C;
        margin: 8px 0 4px;
    }

    .kpi-card-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #E9FFFA;
        transition: all 0.3s ease;
    }

    .chart-card:hover {
        box-shadow: 0 4px 12px rgba(3, 98, 76, 0.08);
        border-color: #03624C;
    }

    .chart-title {
        font-size: 16px;
        font-weight: 700;
        color: #03624C;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stat-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    .stat-table th {
        background: #03624C;
        color: white;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        border: none;
    }

    .stat-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #E9FFFA;
    }

    .stat-table tbody tr:hover {
        background: #E9FFFA;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        overflow-x: auto;
        padding-bottom: 8px;
    }

    .filter-tab {
        padding: 10px 20px;
        background: #E9FFFA;
        border: 2px solid transparent;
        border-radius: 20px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        transition: all 0.25s ease;
        white-space: nowrap;
    }

    .filter-tab:hover {
        color: #03624C;
        border-color: #03624C;
        transform: translateY(-2px);
    }

    .filter-tab.active {
        background: #03624C;
        color: white;
        border-color: #03624C;
        box-shadow: 0 4px 12px rgba(3, 98, 76, 0.3);
    }

    .inventory-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .inventory-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #E9FFFA;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }

    .inventory-card:nth-child(1) { animation-delay: 0.05s; }
    .inventory-card:nth-child(2) { animation-delay: 0.1s; }
    .inventory-card:nth-child(3) { animation-delay: 0.15s; }
    .inventory-card:nth-child(4) { animation-delay: 0.2s; }
    .inventory-card:nth-child(5) { animation-delay: 0.25s; }

    .inventory-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 24px rgba(3, 98, 76, 0.15);
        border-color: #03624C;
    }

    .inventory-card-image {
        width: 100%;
        height: 180px;
        background: linear-gradient(135deg, #E9FFFA, #d6f5ed);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .inventory-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .inventory-card:hover .inventory-card-image img {
        transform: scale(1.1);
    }

    .inventory-card-body {
        padding: 20px;
    }

    .inventory-card-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 6px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .inventory-card-sku {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 10px;
        font-family: Monaco, Consolas, monospace;
        background: #E9FFFA;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-block;
    }

    .inventory-card-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .inventory-card-price {
        font-size: 16px;
        font-weight: 700;
        color: #03624C;
    }

    .inventory-card-stock {
        font-size: 12px;
        color: #64748b;
        background: #E9FFFA;
        padding: 4px 10px;
        border-radius: 12px;
        font-weight: 600;
    }

    .stock-badge {
        display: block;
        text-align: center;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-success {
        background: rgba(52, 199, 89, 0.15);
        color: #34C759;
    }

    .badge-warning {
        background: rgba(255, 204, 0, 0.2);
        color: #FFCC00;
    }

    .badge-danger {
        background: rgba(255, 59, 48, 0.15);
        color: #FF3B31;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 24px;
    }

    .btn-primary-custom {
        background: #03624C;
        color: white;
        padding: 14px 28px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary-custom:hover {
        background: #024538;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(3, 98, 76, 0.3);
    }

    .btn-outline-custom {
        background: white;
        color: #03624C;
        padding: 14px 28px;
        border: 2px solid #03624C;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-outline-custom:hover {
        background: #03624C;
        color: white;
        transform: translateY(-2px);
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.4;
    }

    @media (max-width: 768px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .kpi-card {
            padding: 16px;
        }
        .kpi-card-value {
            font-size: 22px;
        }
        .chart-grid {
            grid-template-columns: 1fr;
        }
        .inventory-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }
        .dashboard-tabs {
            padding: 0;
        }
        .tab-btn {
            padding: 14px 16px;
            font-size: 13px;
        }
        .tab-btn span {
            display: none;
        }
        .tab-btn i {
            font-size: 18px;
        }
        .filter-tabs {
            gap: 8px;
        }
        .filter-tab {
            padding: 8px 16px;
            font-size: 12px;
        }
    }
</style>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-card-icon"><i class="bi bi-box-seam"></i></div>
        <div class="kpi-card-label">Total Items</div>
        <div class="kpi-card-value">{{ number_format($totalStock) }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #3b82f6;"><i class="bi bi-collection"></i></div>
        <div class="kpi-card-label">Product Types</div>
        <div class="kpi-card-value">{{ $stats['total_products'] }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #34C759;"><i class="bi bi-check-circle-fill"></i></div>
        <div class="kpi-card-label">In Stock</div>
        <div class="kpi-card-value">{{ $stats['total_products'] - $outOfStock }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #FFCC00;"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="kpi-card-label">Low Stock</div>
        <div class="kpi-card-value">{{ $lowOnStock }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #9b59b6;"><i class="bi bi-cash-stack"></i></div>
        <div class="kpi-card-label">Inventory Value</div>
        <div class="kpi-card-value">${{ number_format((float)$stats['total_inventory_value'], 0) }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card-icon" style="color: #16a085;"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="kpi-card-label">Total Sales</div>
        <div class="kpi-card-value">{{ number_format($stats['total_sales_count']) }}</div>
    </div>
</div>

<!-- Dashboard Tabs -->
<div class="dashboard-section">
    <div class="dashboard-tabs" id="dashboardTabs">
        <button class="tab-btn active" data-tab="overview" title="Overview">
            <i class="bi bi-speedometer2"></i>
            <span>Overview</span>
        </button>
        <button class="tab-btn" data-tab="analytics" title="Analytics">
            <i class="bi bi-bar-chart-fill"></i>
            <span>Analytics</span>
        </button>
        <button class="tab-btn" data-tab="inventory" title="Inventory">
            <i class="bi bi-boxes"></i>
            <span>Inventory</span>
        </button>
    </div>

    <div class="tab-content">
        <!-- OVERVIEW -->
        <div class="tab-pane active" id="overview">
            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-fire" style="color: #ff6b6b;"></i> Top Selling Products</h3>
                    <div id="topProductsChart" style="height: 300px;"></div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-pie-chart-fill"></i> Stock Status</h3>
                    <div id="stockStatusChart" style="height: 300px;"></div>
                </div>
            </div>

            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-building"></i> Stock by Warehouse</h3>
                    <div id="warehouseChart" style="height: 300px;"></div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-tag-fill" style="color: #9b59b6;"></i> Products by Category</h3>
                    <div id="categoryChart" style="height: 300px;"></div>
                </div>
            </div>

            <!-- Tables -->
            <div class="chart-grid" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-star-fill" style="color: #ffd700;"></i> Top Products</h3>
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="text-align: right;">Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingProducts->take(5) as $product)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($product->name, 30) }}</strong><br>
                                        <small class="text-muted">{{ $product->sku }}</small>
                                    </td>
                                    <td style="text-align: right;">
                                        <span class="badge badge-success">{{ $product->sold_count ?? 0 }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="display: block; margin-bottom: 8px;"></i>
                                        No sales data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-exclamation-circle" style="color: #ef4444;"></i> Low Stock Items</h3>
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="text-align: right;">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts->take(5) as $product)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($product->name, 30) }}</strong><br>
                                        <small class="text-muted">{{ $product->sku }}</small>
                                    </td>
                                    <td style="text-align: right;">
                                        <span class="badge badge-warning">{{ $product->total_stock }} units</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="bi bi-check-circle" style="color: #34C759; display: block; margin-bottom: 8px;"></i>
                                        All stocked well!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ANALYTICS -->
        <div class="tab-pane" id="analytics">
            <div class="mb-4">
                <h3 class="chart-title"><i class="bi bi-graph-up-arrow"></i> Advanced Analytics</h3>
                <p class="text-muted mb-0">Detailed inventory performance metrics</p>
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

        <!-- INVENTORY -->
        <div class="tab-pane" id="inventory">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 style="margin: 0; color: #03624C; font-weight: 700; font-size: 20px;">
                        <i class="bi bi-box-seam" style="margin-right: 8px;"></i> Inventory Overview
                    </h3>
                    <p class="text-muted mb-0 mt-1">Recent products in your catalog</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('products.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-lg"></i> Add Product
                    </a>
                    <a href="{{ route('products.index') }}" class="btn-outline-custom">
                        <i class="bi bi-arrow-right"></i> View All
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-tabs">
                <button class="filter-tab active" onclick="filterInventory(event, 'all')">
                    <i class="bi bi-grid"></i> All
                </button>
                <button class="filter-tab" onclick="filterInventory(event, 'in-stock')">
                    <i class="bi bi-check-circle"></i> In Stock
                </button>
                <button class="filter-tab" onclick="filterInventory(event, 'low-stock')">
                    <i class="bi bi-exclamation-triangle"></i> Low Stock
                </button>
                <button class="filter-tab" onclick="filterInventory(event, 'out-stock')">
                    <i class="bi bi-x-circle"></i> Out of Stock
                </button>
            </div>

            <!-- Grid -->
            <div class="inventory-grid" id="inventory-grid">
                @forelse($recentProducts as $product)
                    @php
                        $stock = $product->total_stock ?? 0;
                        $status = $stock <= 0 ? 'out-stock' : ($stock <= 10 ? 'low-stock' : 'in-stock');
                    @endphp
                    <div class="inventory-card" data-status="{{ $status }}" onclick="location.href='{{ route('products.show', $product) }}'">
                        <div class="inventory-card-image">
                            @if($product->primaryImage && $product->primaryImage->url)
                                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'bi bi-image\' style=\'font-size:48px;color:#ccc\'></i>'">
                            @else
                                <i class="bi bi-image" style="font-size: 48px; color: #ccc;"></i>
                            @endif
                        </div>
                        <div class="inventory-card-body">
                            <h4 class="inventory-card-title" title="{{ $product->name }}">{{ $product->name }}</h4>
                            <div class="inventory-card-sku">{{ $product->sku }}</div>

                            <div class="inventory-card-info">
                                <span class="inventory-card-price">${{ number_format($product->price, 2) }}</span>
                                <span class="inventory-card-stock">{{ $stock }} units</span>
                            </div>

                            @if($stock <= 0)
                                <span class="stock-badge badge-danger">Out of Stock</span>
                            @elseif($stock <= 10)
                                <span class="stock-badge badge-warning">Low Stock</span>
                            @else
                                <span class="stock-badge badge-success">In Stock</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No products found</p>
                        <a href="{{ route('products.create') }}" class="btn-primary-custom" style="margin-top: 16px;">
                            <i class="bi bi-plus"></i> Add Product
                        </a>
                    </div>
                @endforelse
            </div>

            @if($recentProducts->count() > 0)
                <div class="action-buttons">
                    <a href="{{ route('products.index') }}" class="btn-outline-custom">
                        <i class="bi bi-arrow-right-circle"></i> View All Products
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    function switchTab(tabName) {
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-tab') === tabName) btn.classList.add('active');
        });

        tabPanes.forEach(pane => {
            pane.classList.remove('active');
            if (pane.id === tabName) pane.classList.add('active');
        });

        localStorage.setItem('activeDashboardTab', tabName);
        if (tabName === 'overview' || tabName === 'analytics') {
            setTimeout(initCharts, 100);
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            switchTab(this.getAttribute('data-tab'));
        });
    });

    const savedTab = localStorage.getItem('activeDashboardTab') || 'overview';
    switchTab(savedTab);
    setTimeout(initCharts, 300);

    // Filter inventory
    window.filterInventory = function(event, filter) {
        const buttons = document.querySelectorAll('.filter-tab');
        const cards = document.querySelectorAll('.inventory-card');

        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        cards.forEach(card => {
            const status = card.getAttribute('data-status');
            card.style.display = (filter === 'all' || status === filter) ? 'block' : 'none';
        });
    };
});

function initCharts() {
    if (typeof ApexCharts === 'undefined') return;

    const chartDefaults = {
        chart: { toolbar: { show: false }, animations: { enabled: true, easing: 'easeout', speed: 800 }, background: 'transparent' },
        theme: { mode: 'light' },
        dataLabels: { enabled: false },
        grid: { borderColor: '#E9FFFA', strokeDashArray: 4 },
        tooltip: { theme: 'light', borderRadius: 8, borderColor: '#E9FFFA' }
    };

    try {
        // Top Products
        const topProducts = @json($topSellingProducts->map(fn($p) => ['name' => $p->name, 'count' => (int)($p->sold_count ?? 0)]));
        if (topProducts.length > 0) {
            new ApexCharts({
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 300, type: 'bar' },
                series: [{ name: 'Sold', data: topProducts.map(p => p.count) }],
                xaxis: { categories: topProducts.map(p => p.name.length > 12 ? p.name.substring(0, 12) + '...' : p.name) },
                colors: ['#03624C'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } }
            }, document.getElementById('topProductsChart')).render();
        }

        // Stock Status
        const stockStatus = @json($stockStatusDistribution);
        if (Object.values(stockStatus).some(v => v > 0)) {
            new ApexCharts({
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 300, type: 'donut' },
                series: Object.values(stockStatus),
                labels: Object.keys(stockStatus),
                colors: ['#34C759', '#FFCC00', '#FF3B31'],
                plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                legend: { position: 'bottom', fontSize: '12px' }
            }, document.getElementById('stockStatusChart')).render();
        }

        // Warehouse Chart
        const warehouseData = @json($stockByWarehouse);
        if (Object.keys(warehouseData).length > 0) {
            new ApexCharts({
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 300, type: 'bar' },
                series: [{ name: 'Stock', data: Object.values(warehouseData) }],
                xaxis: { categories: Object.keys(warehouseData) },
                colors: ['#3b82f6'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } }
            }, document.getElementById('warehouseChart')).render();
        }

        // Category Chart
        const categoryData = @json($productsByCategory);
        if (Object.keys(categoryData).length > 0) {
            new ApexCharts({
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 300, type: 'pie' },
                series: Object.values(categoryData),
                labels: Object.keys(categoryData),
                colors: ['#03624C', '#0fb9b1', '#2ecc71', '#3498db', '#9b59b6', '#f39c12', '#e74c3c', '#1abc9c', '#e67e22', '#95a5a6'],
                legend: { position: 'bottom', fontSize: '12px' }
            }, document.getElementById('categoryChart')).render();
        }

        // Analytics charts
        const topProductsDetail = @json($topSellingProducts->take(8)->map(fn($p) => ['name' => $p->name, 'count' => (int)($p->sold_count ?? 0)]));
        if (topProductsDetail.length > 0) {
            new ApexCharts({
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 350, type: 'area' },
                series: [{ name: 'Sales', data: topProductsDetail.map(p => p.count) }],
                xaxis: { categories: topProductsDetail.map(p => p.name.length > 12 ? p.name.substring(0, 12) + '...' : p.name) },
                colors: ['#0fb9b1'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } },
                stroke: { curve: 'smooth', width: 3 }
            }, document.getElementById('salesTrendChart')).render();
        }

        const warehouseDetail = @json($stockByWarehouse);
        if (Object.keys(warehouseDetail).length > 0) {
            new ApexCharts({
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 350, type: 'column' },
                series: [{ name: 'Stock', data: Object.values(warehouseDetail) }],
                xaxis: { categories: Object.keys(warehouseDetail) },
                colors: ['#16a085'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } }
            }, document.getElementById('warehouseDetailChart')).render();
        }

        const categoryDetail = @json($productsByCategory);
        if (Object.keys(categoryDetail).length > 0) {
            new ApexCharts({
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 350, type: 'bar' },
                series: [{ name: 'Products', data: Object.values(categoryDetail) }],
                xaxis: { categories: Object.keys(categoryDetail) },
                colors: ['#9b59b6'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } }
            }, document.getElementById('categoryDetailChart')).render();
        }

    } catch (error) {
        console.error('Chart error:', error);
    }
}
</script>
@endpush

@endsection
