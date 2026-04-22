@extends('layouts.app')

@section('title','Dashboard')

@push('styles')
    @vite(['resources/css/features/dashboard.css'])
@endpush

@section('content')
<div class="page-shell dashboard-page">
    <section class="page-hero">
        <div>
            <p class="page-eyebrow">Overview</p>
            <h1 class="page-title">Inventory command center</h1>
            <p class="page-subtitle">Monitor stock health, warehouse distribution, sales activity, and product movement from one consistent teal-led dashboard.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add product
            </a>
            <a href="{{ route('analytics') }}" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up"></i> Open analytics
            </a>
        </div>
    </section>

    <section class="stats-grid">
        <article class="stat-card">
            <span class="icon-chip"><i class="bi bi-box-seam"></i></span>
            <p class="stat-label">Total items</p>
            <div class="stat-value">{{ number_format($totalStock) }}</div>
            <p class="stat-meta">Units currently distributed across warehouses</p>
        </article>
        <article class="stat-card">
            <span class="icon-chip"><i class="bi bi-collection"></i></span>
            <p class="stat-label">Product types</p>
            <div class="stat-value">{{ $stats['total_products'] }}</div>
            <p class="stat-meta">Distinct products currently active in the catalog</p>
        </article>
        <article class="stat-card">
            <span class="icon-chip success"><i class="bi bi-check-circle-fill"></i></span>
            <p class="stat-label">In stock</p>
            <div class="stat-value">{{ $stats['total_products'] - $outOfStock }}</div>
            <p class="stat-meta">Products available without immediate replenishment</p>
        </article>
        <article class="stat-card">
            <span class="icon-chip warning"><i class="bi bi-exclamation-triangle-fill"></i></span>
            <p class="stat-label">Low stock</p>
            <div class="stat-value">{{ $lowOnStock }}</div>
            <p class="stat-meta">Products near reorder point that need attention</p>
        </article>
        <article class="stat-card">
            <span class="icon-chip"><i class="bi bi-cash-stack"></i></span>
            <p class="stat-label">Inventory value</p>
            <div class="stat-value">${{ number_format((float) $stats['total_inventory_value'], 0) }}</div>
            <p class="stat-meta">Current stock value based on cost price</p>
        </article>
        <article class="stat-card">
            <span class="icon-chip"><i class="bi bi-graph-up-arrow"></i></span>
            <p class="stat-label">Total sales</p>
            <div class="stat-value">{{ number_format($stats['total_sales_count']) }}</div>
            <p class="stat-meta">Accumulated product sales count across the catalog</p>
        </article>
    </section>

    <section class="surface-card dashboard-workspace">
        <div class="dashboard-tabs" id="dashboardTabs">
            <button class="tab-btn active" data-tab="overview" title="Overview">
                <i class="bi bi-speedometer2"></i>
                <span>Overview</span>
            </button>
            <button class="tab-btn" data-tab="analytics" title="Analytics">
                <i class="bi bi-bar-chart"></i>
                <span>Analytics</span>
            </button>
            <button class="tab-btn" data-tab="inventory" title="Inventory">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Inventory</span>
            </button>
        </div>

        <div class="tab-content">
            <div class="tab-pane active" id="overview">
                <div class="dashboard-grid two-up">
                    <article class="chart-card">
                        <div class="card-header-row">
                            <div>
                                <h3 class="card-title">Top selling products</h3>
                                <p class="card-subtitle">Best performing items by sold quantity</p>
                            </div>
                            <span class="badge badge-primary">Sales</span>
                        </div>
                        <div id="topProductsChart" class="chart-surface"></div>
                    </article>

                    <article class="chart-card">
                        <div class="card-header-row">
                            <div>
                                <h3 class="card-title">Stock status</h3>
                                <p class="card-subtitle">Healthy stock split across product statuses</p>
                            </div>
                            <span class="badge badge-success">Live</span>
                        </div>
                        <div id="stockStatusChart" class="chart-surface"></div>
                    </article>
                </div>

                <div class="dashboard-grid two-up">
                    <article class="chart-card">
                        <div class="card-header-row">
                            <div>
                                <h3 class="card-title">Stock by warehouse</h3>
                                <p class="card-subtitle">Current stock allocation per warehouse</p>
                            </div>
                            <span class="badge badge-primary">Warehouses</span>
                        </div>
                        <div id="warehouseChart" class="chart-surface"></div>
                    </article>

                    <article class="chart-card">
                        <div class="card-header-row">
                            <div>
                                <h3 class="card-title">Products by category</h3>
                                <p class="card-subtitle">Distribution of active products by category</p>
                            </div>
                            <span class="badge badge-primary">Catalog</span>
                        </div>
                        <div id="categoryChart" class="chart-surface"></div>
                    </article>
                </div>

                <div class="dashboard-grid two-up">
                    <article class="table-card">
                        <div class="card-header-row">
                            <div>
                                <h3 class="card-title">Top products</h3>
                                <p class="card-subtitle">Fastest movers in the current catalog</p>
                            </div>
                        </div>
                        <x-table :headers="['Product', 'Sold']">
                            @forelse($topSellingProducts->take(5) as $product)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($product->name, 30) }}</strong><br>
                                        <span class="text-muted small">{{ $product->sku }}</span>
                                    </td>
                                    <td class="text-end"><span class="badge badge-success">{{ $product->sold_count ?? 0 }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">No sales data available.</td></tr>
                            @endforelse
                        </x-table>
                    </article>

                    <article class="table-card">
                        <div class="card-header-row">
                            <div>
                                <h3 class="card-title">Low stock items</h3>
                                <p class="card-subtitle">Items requiring replenishment soon</p>
                            </div>
                        </div>
                        <x-table :headers="['Product', 'Stock']">
                            @forelse($lowStockProducts->take(5) as $product)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($product->name, 30) }}</strong><br>
                                        <span class="text-muted small">{{ $product->sku }}</span>
                                    </td>
                                    <td class="text-end"><span class="badge badge-warning">{{ $product->total_stock }} units</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">All products are currently healthy.</td></tr>
                            @endforelse
                        </x-table>
                    </article>
                </div>
            </div>

            <div class="tab-pane" id="analytics">
                <div class="dashboard-grid two-up">
                    <article class="chart-card">
                        <div class="card-header-row">
                            <div>
                                <h3 class="card-title">Sales trend</h3>
                                <p class="card-subtitle">Top products ranked by sales count</p>
                            </div>
                        </div>
                        <div id="salesTrendChart" class="chart-surface chart-tall"></div>
                    </article>
                    <article class="chart-card">
                        <div class="card-header-row">
                            <div>
                                <h3 class="card-title">Warehouse stock levels</h3>
                                <p class="card-subtitle">Compare stock volume by warehouse</p>
                            </div>
                        </div>
                        <div id="warehouseDetailChart" class="chart-surface chart-tall"></div>
                    </article>
                </div>

                <article class="chart-card">
                    <div class="card-header-row">
                        <div>
                            <h3 class="card-title">Category performance</h3>
                            <p class="card-subtitle">Categories contributing the most to available products</p>
                        </div>
                    </div>
                    <div id="categoryDetailChart" class="chart-surface chart-tall"></div>
                </article>
            </div>

            <div class="tab-pane" id="inventory">
                <div class="card-header-row">
                    <div>
                        <h3 class="card-title">Recent inventory</h3>
                        <p class="card-subtitle">Latest products added to the catalog with quick filtering.</p>
                    </div>
                    <div class="page-actions">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">View all products</a>
                    </div>
                </div>

                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterInventory(event, 'all')"><i class="bi bi-grid"></i> All</button>
                    <button class="filter-tab" onclick="filterInventory(event, 'in-stock')"><i class="bi bi-check-circle"></i> In stock</button>
                    <button class="filter-tab" onclick="filterInventory(event, 'low-stock')"><i class="bi bi-exclamation-triangle"></i> Low stock</button>
                    <button class="filter-tab" onclick="filterInventory(event, 'out-stock')"><i class="bi bi-x-circle"></i> Out of stock</button>
                </div>

                <div class="inventory-grid" id="inventory-grid">
                    @forelse($recentProducts as $product)
                        @php
                            $stock = $product->total_stock ?? 0;
                            $status = $stock <= 0 ? 'out-stock' : ($stock <= 10 ? 'low-stock' : 'in-stock');
                        @endphp
                        <article class="inventory-card" data-status="{{ $status }}" onclick="location.href='{{ route('products.show', $product) }}'">
                            <div class="inventory-card-image">
                                @if($product->primaryImage && $product->primaryImage->url)
                                    <img src="{{ $product->primaryImage->url }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <i class="bi bi-image"></i>
                                @endif
                            </div>
                            <div class="inventory-card-body">
                                <h4 class="inventory-card-title">{{ $product->name }}</h4>
                                <div class="inventory-card-sku">{{ $product->sku }}</div>
                                <div class="inventory-card-info">
                                    <span class="inventory-card-price">${{ number_format($product->price, 2) }}</span>
                                    <span class="inventory-card-stock">{{ $stock }} units</span>
                                </div>
                                @if($stock <= 0)
                                    <span class="status-pill danger">Out of stock</span>
                                @elseif($stock <= 10)
                                    <span class="status-pill warning">Low stock</span>
                                @else
                                    <span class="status-pill success">In stock</span>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-panel">
                            <i class="bi bi-inbox"></i>
                            <p>No products found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
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
