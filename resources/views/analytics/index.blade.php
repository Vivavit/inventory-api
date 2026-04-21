@extends('layouts.app')

@section('title', 'Inventory Analytics')

@section('content')
@push('styles')
    @vite(['resources/css/features/analytics.css'])
@endpush

<div class="analytics-page">
    <section class="analytics-hero">
        <div class="hero-copy">
            <p class="section-title">Inventory analytics</p>
            <h1 class="page-title">Real-time inventory health</h1>
            <p class="text-muted hero-description">Track stock value, warehouse utilization, sales velocity, and low-stock alerts with a fast, responsive analytics view.</p>
        </div>

        <div class="hero-actions">
            <div class="period-filters" role="tablist" aria-label="Analytics period filters">
                @foreach(['day' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $key => $label)
                    <button type="button" class="period-chip" data-period="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>
            <p class="hero-note">Data refreshes instantly as you switch time ranges.</p>
        </div>
    </section>

    <section id="kpiSection" class="kpi-grid" aria-live="polite">
        <article class="kpi-card loading" data-kpi="average_stock_value">
            <div class="kpi-card-top">
                <span class="kpi-icon bg-teal"><i class="bi bi-currency-dollar"></i></span>
                <span class="kpi-label">Average stock value</span>
            </div>
            <div class="kpi-value">0</div>
            <p class="kpi-subtitle">Inventory replacement cost</p>
        </article>

        <article class="kpi-card loading" data-kpi="low_stock_alert_count">
            <div class="kpi-card-top">
                <span class="kpi-icon bg-warning"><i class="bi bi-exclamation-triangle-fill"></i></span>
                <span class="kpi-label">Low stock alerts</span>
            </div>
            <div class="kpi-value">0</div>
            <p class="kpi-subtitle">Items close to reorder point</p>
        </article>

        <article class="kpi-card loading" data-kpi="out_of_stock_count">
            <div class="kpi-card-top">
                <span class="kpi-icon bg-danger"><i class="bi bi-x-circle"></i></span>
                <span class="kpi-label">Out of stock</span>
            </div>
            <div class="kpi-value">0</div>
            <p class="kpi-subtitle">Products needing urgent restock</p>
        </article>

        <article class="kpi-card loading" data-kpi="total_sales_today">
            <div class="kpi-card-top">
                <span class="kpi-icon bg-primary"><i class="bi bi-bag-check"></i></span>
                <span class="kpi-label">Sales today</span>
            </div>
            <div class="kpi-value">0</div>
            <p class="kpi-subtitle">Gross order total</p>
        </article>

        <article class="kpi-card loading" data-kpi="total_sales_month">
            <div class="kpi-card-top">
                <span class="kpi-icon bg-blue"><i class="bi bi-calendar-check"></i></span>
                <span class="kpi-label">This month</span>
            </div>
            <div class="kpi-value">0</div>
            <p class="kpi-subtitle">Sales captured this month</p>
        </article>

        <article class="kpi-card loading" data-kpi="total_sales_year">
            <div class="kpi-card-top">
                <span class="kpi-icon bg-success"><i class="bi bi-graph-up"></i></span>
                <span class="kpi-label">This year</span>
            </div>
            <div class="kpi-value">0</div>
            <p class="kpi-subtitle">Year-to-date revenue</p>
        </article>
    </section>

    <section class="analytics-tabs">
        <div class="tab-actions" role="tablist" aria-label="Analytics navigation tabs">
            <button type="button" class="tab-button active" data-tab="overview" aria-selected="true">Overview</button>
            <button type="button" class="tab-button" data-tab="trends">Trends</button>
            <button type="button" class="tab-button" data-tab="warehouse">Warehouse</button>
            <button type="button" class="tab-button" data-tab="reports">Reports</button>
        </div>

        <p id="analyticsStatus" class="analytics-status hidden">Loading analytics...</p>

        <div id="overviewTab" class="analytics-tab-panel active">
            <div class="analytics-grid">
                <article class="chart-card loading">
                    <div class="chart-card-header">
                        <h3>Stock value trend</h3>
                        <span class="badge badge-success">Live</span>
                    </div>
                    <div class="chart-frame"><canvas id="stockValueChart"></canvas></div>
                </article>

                <article class="chart-card loading">
                    <div class="chart-card-header">
                        <h3>Inventory by category</h3>
                        <span class="badge badge-secondary">Distribution</span>
                    </div>
                    <div class="chart-frame"><canvas id="categoryChart"></canvas></div>
                </article>
            </div>

            <article class="chart-card loading chart-full">
                <div class="chart-card-header">
                    <h3>Sales trend</h3>
                    <span class="badge badge-primary">Period</span>
                </div>
                <div class="chart-frame"><canvas id="salesTrendChart"></canvas></div>
            </article>
        </div>

        <div id="trendsTab" class="analytics-tab-panel">
            <div class="analytics-grid">
                <article class="chart-card loading chart-full">
                    <div class="chart-card-header">
                        <h3>Monthly performance (12 months)</h3>
                        <span class="badge badge-primary">Revenue</span>
                    </div>
                    <div class="chart-frame"><canvas id="monthlyChart"></canvas></div>
                </article>

                <article class="chart-card loading chart-full">
                    <div class="chart-card-header">
                        <h3>Category momentum</h3>
                        <span class="badge badge-warning">Growth %</span>
                    </div>
                    <div class="chart-frame"><canvas id="categoryMomentumChart"></canvas></div>
                </article>
            </div>
        </div>

        <div id="warehouseTab" class="analytics-tab-panel">
            <article class="chart-card loading chart-full">
                <div class="chart-card-header">
                    <h3>Warehouse utilization</h3>
                    <span class="badge badge-primary">Capacity</span>
                </div>
                <div class="chart-frame"><canvas id="warehouseComparisonChart"></canvas></div>
            </article>
            <div class="warehouse-grid" id="warehouseGrid"></div>
        </div>

        <div id="reportsTab" class="analytics-tab-panel">
            <div class="analytics-grid">
                <article class="chart-card chart-full">
                    <div class="chart-card-header">
                        <h3>Top products</h3>
                        <span class="badge badge-primary">Sales</span>
                    </div>
                    <div class="table-responsive">
                        <table class="stat-table" id="topProductsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sales (qty)</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </article>

                <article class="chart-card chart-full">
                    <div class="chart-card-header">
                        <h3>Low stock items</h3>
                        <span class="badge badge-warning">Alert</span>
                    </div>
                    <div class="table-responsive">
                        <table class="stat-table" id="lowStockTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Current Stock</th>
                                    <th>Threshold</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </article>

                <article class="chart-card chart-full">
                    <div class="chart-card-header">
                        <h3>Category summary</h3>
                        <span class="badge badge-secondary">Health</span>
                    </div>
                    <div class="table-responsive">
                        <table class="stat-table" id="categorySummaryTable">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Products</th>
                                    <th>Total Value</th>
                                    <th>Avg Price</th>
                                    <th>Stock Health</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>

@push('scripts')
    @vite(['resources/js/features/analytics.js'])
@endpush
@endsection