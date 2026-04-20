@extends('layouts.app')

@section('title', 'Inventory Analytics')

@section('content')
<style>
    .analytics-page {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .analytics-hero {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        align-items: flex-start;
        padding: 24px;
        background: var(--surface);
        border: 1px solid rgba(15, 185, 177, 0.16);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .hero-copy {
        max-width: 640px;
    }

    .hero-description {
        margin-top: 12px;
        color: var(--muted);
        line-height: 1.7;
    }

    .hero-actions {
        display: flex;
        flex-direction: column;
        gap: 14px;
        align-items: flex-end;
    }

    .period-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .period-chip {
        border: 1px solid rgba(3, 98, 76, 0.16);
        background: var(--surface-soft);
        color: var(--text);
        padding: 10px 18px;
        border-radius: var(--radius-full);
        font-weight: var(--fw-semibold);
        transition: var(--transition-fast);
        cursor: pointer;
    }

    .period-chip.active,
    .period-chip:hover {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    .hero-note {
        color: var(--muted);
        font-size: 0.95rem;
        text-align: right;
    }

    .kpi-grid,
    .analytics-grid {
        display: grid;
        gap: 20px;
    }

    .kpi-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .kpi-card,
    .chart-card {
        position: relative;
        background: var(--surface);
        border-radius: var(--radius-lg);
        border: 1px solid rgba(115, 134, 141, 0.12);
        box-shadow: var(--shadow-sm);
        padding: 24px;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .kpi-card:hover,
    .chart-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .kpi-card.loading::after,
    .chart-card.loading::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.4), rgba(255,255,255,0));
        animation: shimmer 1.25s infinite;
        pointer-events: none;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(100%);
        }
    }

    .kpi-card-top,
    .chart-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: white;
        font-size: 1.1rem;
    }

    .bg-teal { background: #0fb9b1; }
    .bg-warning { background: #ff9800; }
    .bg-danger { background: #ff6b6b; }
    .bg-primary { background: #03624c; }
    .bg-blue { background: #4facfe; }
    .bg-success { background: #2ecc71; }

    .kpi-label {
        font-size: 0.95rem;
        font-weight: var(--fw-semibold);
        color: var(--text);
    }

    .kpi-value {
        margin-top: 10px;
        font-size: 2.35rem;
        font-weight: var(--fw-bold);
        color: var(--text);
    }

    .kpi-subtitle {
        margin-top: 10px;
        color: var(--muted);
        font-size: 0.95rem;
    }

    .analytics-tabs {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .tab-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tab-button {
        border: none;
        background: var(--surface-soft);
        color: var(--text);
        padding: 12px 20px;
        border-radius: var(--radius-full);
        cursor: pointer;
        font-weight: var(--fw-semibold);
        transition: var(--transition-fast);
    }

    .tab-button.active,
    .tab-button:hover {
        background: var(--accent);
        color: white;
        transform: translateY(-1px);
    }

    .analytics-status {
        margin: 0;
        color: var(--text);
        font-size: 0.95rem;
    }

    .analytics-tab-panel {
        display: none;
        opacity: 0;
        transform: translateY(12px);
        transition: opacity 0.25s ease, transform 0.25s ease;
    }

    .analytics-tab-panel.active {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    .chart-frame {
        min-height: 320px;
        position: relative;
    }

    .chart-frame canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .chart-full {
        grid-column: 1 / -1;
    }

    .warehouse-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .warehouse-card {
        background: var(--surface-soft);
        border-radius: var(--radius-md);
        padding: 20px;
        min-height: 180px;
    }

    .warehouse-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .warehouse-label {
        margin: 0;
        font-weight: var(--fw-bold);
        color: var(--accent);
    }

    .warehouse-subtitle {
        margin: 0;
        font-size: 0.9rem;
        color: var(--muted);
    }

    .warehouse-bar {
        background: rgba(3, 98, 76, 0.08);
        border-radius: 999px;
        height: 10px;
        overflow: hidden;
        margin-bottom: 14px;
    }

    .warehouse-fill {
        background: linear-gradient(90deg, #0fb9b1, #03624c);
        height: 100%;
    }

    .warehouse-details {
        display: flex;
        justify-content: space-between;
        color: var(--muted);
        font-size: 0.95rem;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .stat-table {
        width: 100%;
        border-collapse: collapse;
    }

    .stat-table thead th {
        background: var(--surface-soft);
        color: var(--text);
        padding: 16px;
        font-weight: var(--fw-semibold);
        text-align: left;
        border-bottom: 1px solid rgba(115, 134, 141, 0.12);
        font-size: 0.95rem;
    }

    .stat-table tbody td {
        padding: 16px;
        border-bottom: 1px solid rgba(115, 134, 141, 0.09);
        color: var(--text);
        font-size: 0.95rem;
    }

    .stat-table tbody tr:hover {
        background: rgba(15, 185, 177, 0.08);
    }

    .empty-row {
        text-align: center;
        color: var(--muted);
        padding: 24px;
    }

    .badge {
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.78rem;
        font-weight: var(--fw-semibold);
    }

    .badge-success { background: rgba(2, 110, 76, 0.12); color: #03624c; }
    .badge-primary { background: rgba(3, 98, 76, 0.14); color: #03624c; }
    .badge-secondary { background: rgba(62, 79, 85, 0.1); color: #3e4f55; }
    .badge-warning { background: rgba(255, 152, 0, 0.16); color: #b66f00; }
    .badge-danger { background: rgba(255, 107, 107, 0.16); color: #c92a2a; }

    .hidden { display: none !important; }

    @media (max-width: 1080px) {
        .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .analytics-grid { grid-template-columns: 1fr; }
        .warehouse-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 720px) {
        .analytics-hero { flex-direction: column; }
        .period-filters { justify-content: flex-start; }
        .kpi-grid { grid-template-columns: 1fr; }
        .tab-actions { justify-content: flex-start; overflow-x: auto; }
        .tab-button { white-space: nowrap; }
    }

    @media (max-width: 520px) {
        .analytics-hero { padding: 18px; }
        .kpi-card, .chart-card { padding: 18px; }
        .chart-frame { min-height: 260px; }
    }
</style>

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

@vite(['resources/js/analytics.js'])
@endsection