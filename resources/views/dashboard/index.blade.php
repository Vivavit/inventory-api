@extends('layouts.app')

@section('title','Dashboard')

@section('content')

@push('styles')
    @vite(['resources/css/features/dashboard.css'])
@endpush

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
            <!-- Charts Row 1 -->
            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-fire" style="color: #ff6b6b;"></i> Top Selling Products</h3>
                    <div id="topProductsChart" style="height: 300px; position: relative; z-index: 2;"></div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-pie-chart-fill"></i> Stock Status</h3>
                    <div id="stockStatusChart" style="height: 300px; position: relative; z-index: 2;"></div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-building"></i> Stock by Warehouse</h3>
                    <div id="warehouseChart" style="height: 300px; position: relative; z-index: 2;"></div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-tag-fill" style="color: #9b59b6;"></i> Products by Category</h3>
                    <div id="categoryChart" style="height: 300px; position: relative; z-index: 2;"></div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="chart-grid" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));">
                <!-- Top Products Table -->
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-star-fill" style="color: #ffd700;"></i> Top Products</h3>
                    <div style="position: relative; z-index: 2; overflow-x: auto;">
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
                </div>

                <!-- Low Stock Items Table -->
                <div class="chart-card">
                    <h3 class="chart-title"><i class="bi bi-exclamation-circle" style="color: #ef4444;"></i> Low Stock Items</h3>
                    <div style="position: relative; z-index: 2; overflow-x: auto;">
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
        </div>

        <!-- ANALYTICS -->
        <div class="tab-pane" id="analytics">
            <div class="mb-4" style="position: relative; z-index: 2;">
                <h3 class="chart-title"><i class="bi bi-graph-up-arrow"></i> Advanced Analytics</h3>
                <p class="text-muted mb-0">Detailed inventory performance metrics</p>
            </div>

            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title">Sales Trend (Top 8 Products)</h3>
                    <div id="salesTrendChart" style="height: 350px; position: relative; z-index: 2;"></div>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Warehouse Stock Levels</h3>
                    <div id="warehouseDetailChart" style="height: 350px; position: relative; z-index: 2;"></div>
                </div>
            </div>

            <div class="chart-card">
                <h3 class="chart-title">Category Performance</h3>
                <div id="categoryDetailChart" style="height: 350px; position: relative; z-index: 2;"></div>
            </div>
        </div>

        <!-- INVENTORY -->
        <div class="tab-pane" id="inventory">
            <div class="d-flex justify-content-between align-items-center mb-3" style="position: relative; z-index: 2;">
                <div>
                    <h3 style="margin: 0; color: #03624C; font-weight: 700; font-size: 20px;">
                        <i class="bi bi-box-seam" style="margin-right: 8px;"></i> Inventory Overview
                    </h3>
                    <p class="text-muted mb-0 mt-1">Recent products in your catalog</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('products.index') }}" class="btn-outline-custom">
                       View All  <i class="bi bi-arrow-right"></i>
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
    window.DashboardData = {
        topProducts: @json($topSellingProducts->map(fn($p) => ['name' => $p->name, 'count' => (int)($p->sold_count ?? 0)])),
        stockStatus: @json($stockStatusDistribution),
        warehouseData: @json($stockByWarehouse),
        categoryData: @json($productsByCategory),
        topProductsDetail: @json($topSellingProducts->take(8)->map(fn($p) => ['name' => $p->name, 'count' => (int)($p->sold_count ?? 0)]))
    };
</script>
@vite(['resources/js/features/dashboard.js'])
@endpush

@endsection