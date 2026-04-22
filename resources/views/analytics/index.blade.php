@extends('layouts.app')

@section('title', 'Inventory Analytics')

@push('styles')
    @vite(['resources/css/features/analytics.css'])
@endpush

@section('content')
<div class="analytics-page page-shell" x-data="analyticsApp()" x-init="init()">
    <section class="page-hero analytics-hero">
        <div class="hero-copy">
            <p class="page-eyebrow">Analytics</p>
            <h1 class="page-title">Operational inventory analytics</h1>
            <p class="page-subtitle">Track stock value, sales trends, category balance, and warehouse utilization through a single consistent reporting surface.</p>
        </div>

        <div class="hero-actions">
            <div class="period-filters" role="tablist" aria-label="Analytics period filters">
                <template x-for="period in periods" :key="period.value">
                    <button type="button" class="period-chip" :class="{ active: activePeriod === period.value }" @click="setPeriod(period.value)" x-text="period.label"></button>
                </template>
            </div>
            <p class="hero-note" x-text="lastUpdatedText"></p>
        </div>
    </section>

    <section class="kpi-grid" aria-live="polite">
        <template x-for="kpi in kpiCards" :key="kpi.key">
            <article class="kpi-card" :class="{ loading: isLoading }">
                <div class="kpi-card-top">
                    <span class="kpi-icon" :class="`bg-${kpi.colorClass}`"><i class="bi" :class="kpi.icon"></i></span>
                    <span class="kpi-label" x-text="kpi.label"></span>
                </div>
                <div class="kpi-value" x-text="formatKPIValue(kpi.key, kpi.value)"></div>
                <p class="kpi-subtitle" x-text="kpi.subtitle"></p>
            </article>
        </template>
    </section>

    <section class="analytics-tabs">
        <div class="tab-actions" role="tablist" aria-label="Analytics navigation tabs">
            <template x-for="tab in tabs" :key="tab.id">
                <button type="button" class="tab-button" :class="{ active: activeTab === tab.id }" :data-tab="tab.id" @click="switchTab(tab.id)" x-text="tab.label"></button>
            </template>
        </div>

        <p class="analytics-status" :class="{ hidden: !isLoading }">Loading analytics...</p>

        <div class="analytics-tab-panel" :class="{ active: activeTab === 'overview' }">
            <div class="analytics-grid">
                <article class="chart-card" :class="{ loading: isLoading }">
                    <div class="chart-card-header">
                        <div>
                            <h3>Stock value trend</h3>
                            <p class="card-subtitle">Average replacement cost over time</p>
                        </div>
                        <span class="badge badge-primary">Trend</span>
                    </div>
                    <div class="chart-frame"><canvas id="stockValueChart"></canvas></div>
                </article>

                <article class="chart-card" :class="{ loading: isLoading }">
                    <div class="chart-card-header">
                        <div>
                            <h3>Inventory by category</h3>
                            <p class="card-subtitle">How products are distributed across major groups</p>
                        </div>
                        <span class="badge badge-success">Distribution</span>
                    </div>
                    <div class="chart-frame"><canvas id="categoryDistributionChart"></canvas></div>
                </article>
            </div>

            <article class="chart-card chart-full" :class="{ loading: isLoading }">
                <div class="chart-card-header">
                    <div>
                        <h3>Sales trend</h3>
                        <p class="card-subtitle">Revenue trend for the selected time period</p>
                    </div>
                    <span class="badge badge-primary">Revenue</span>
                </div>
                <div class="chart-frame"><canvas id="salesTrendChart"></canvas></div>
            </article>
        </div>

        <div class="analytics-tab-panel" :class="{ active: activeTab === 'trends' }">
            <div class="analytics-grid">
                <article class="chart-card chart-full" :class="{ loading: isLoading }">
                    <div class="chart-card-header">
                        <div>
                            <h3>Monthly performance</h3>
                            <p class="card-subtitle">Rolling 12-month revenue view</p>
                        </div>
                        <span class="badge badge-primary">12 months</span>
                    </div>
                    <div class="chart-frame"><canvas id="monthlyPerformanceChart"></canvas></div>
                </article>

                <article class="chart-card chart-full" :class="{ loading: isLoading }">
                    <div class="chart-card-header">
                        <div>
                            <h3>Category momentum</h3>
                            <p class="card-subtitle">Month-over-month growth by category</p>
                        </div>
                        <span class="badge badge-warning">Growth</span>
                    </div>
                    <div class="chart-frame"><canvas id="categoryMomentumChart"></canvas></div>
                </article>
            </div>
        </div>

        <div class="analytics-tab-panel" :class="{ active: activeTab === 'warehouse' }">
            <article class="chart-card chart-full" :class="{ loading: isLoading }">
                <div class="chart-card-header">
                    <div>
                        <h3>Warehouse utilization</h3>
                        <p class="card-subtitle">Capacity versus stock in each active warehouse</p>
                    </div>
                    <span class="badge badge-primary">Utilization</span>
                </div>
                <div class="chart-frame"><canvas id="warehouseComparisonChart"></canvas></div>
            </article>

            <div class="warehouse-grid">
                <template x-for="warehouse in warehouseData" :key="warehouse.id">
                    <article class="warehouse-card">
                        <div class="warehouse-header">
                            <div class="warehouse-info">
                                <p class="warehouse-label" x-text="warehouse.name"></p>
                                <p class="warehouse-subtitle">Current stock versus estimated capacity</p>
                            </div>
                            <span class="warehouse-badge" x-text="`${warehouse.utilization}% used`"></span>
                        </div>
                        <div class="warehouse-bar">
                            <div class="warehouse-fill" :style="`width: ${warehouse.utilization}%`"></div>
                        </div>
                        <div class="warehouse-details">
                            <div class="warehouse-detail-item">
                                <div class="warehouse-detail-label">Items</div>
                                <div class="warehouse-detail-value" x-text="formatNumber(warehouse.items)"></div>
                            </div>
                            <div class="warehouse-detail-item">
                                <div class="warehouse-detail-label">Capacity</div>
                                <div class="warehouse-detail-value" x-text="formatNumber(warehouse.capacity)"></div>
                            </div>
                            <div class="warehouse-detail-item">
                                <div class="warehouse-detail-label">Status</div>
                                <div class="warehouse-detail-value" x-text="getUtilizationLabel(warehouse.utilization)"></div>
                            </div>
                        </div>
                    </article>
                </template>
            </div>
        </div>

        <div class="analytics-tab-panel" :class="{ active: activeTab === 'reports' }">
            <div class="analytics-grid">
                <article class="chart-card chart-full">
                    <div class="chart-card-header">
                        <div>
                            <h3>Top products</h3>
                            <p class="card-subtitle">Best performing products in the current period</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="stat-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sales</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="product in topProducts" :key="product.name">
                                    <tr>
                                        <td x-text="product.name"></td>
                                        <td x-text="formatNumber(product.sales)"></td>
                                        <td x-text="formatCurrency(product.value)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="chart-card chart-full">
                    <div class="chart-card-header">
                        <div>
                            <h3>Low stock items</h3>
                            <p class="card-subtitle">Products close to or below threshold</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="stat-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Current stock</th>
                                    <th>Threshold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in lowStockItems" :key="item.name">
                                    <tr>
                                        <td x-text="item.name"></td>
                                        <td x-text="formatNumber(item.current)"></td>
                                        <td x-text="formatNumber(item.threshold)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="chart-card chart-full">
                    <div class="chart-card-header">
                        <div>
                            <h3>Category summary</h3>
                            <p class="card-subtitle">Category value, price level, and stock health overview</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="stat-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Products</th>
                                    <th>Total value</th>
                                    <th>Avg price</th>
                                    <th>Stock health</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="category in categorySummary" :key="category.name">
                                    <tr>
                                        <td x-text="category.name"></td>
                                        <td x-text="formatNumber(category.products)"></td>
                                        <td x-text="formatCurrency(category.value)"></td>
                                        <td x-text="formatCurrency(category.avg_price)"></td>
                                        <td x-text="`${category.stock_health}%`"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @vite(['resources/js/features/analytics.js'])
@endpush
