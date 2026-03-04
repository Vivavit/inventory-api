@extends('layouts.app')

@section('title', 'Inventory Analytics')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Inventory Analytics</h1>
            <p class="text-muted mb-0">Data-driven inventory management</p>
        </div>
        
        <!-- Period Filter -->
        <div class="d-flex align-items-center">
            <span class="me-2 text-muted">Period:</span>
            <div class="btn-group btn-group-sm" role="group">
                @foreach(['day' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $key => $label)
                    <a href="?period={{ $key }}" 
                       class="btn {{ $period == $key ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>


    <div class="row g-3 mb-4">
        <!-- Avg Stock Value -->
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Avg. Stock Value</h6>
                            <h3 class="fw-bold mb-0">
                                ${{ number_format($metrics['average_stock_value'], 2) }}
                            </h3>
                        </div>
                        <div >
                            <i class="bi bi-cash fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Low Stock Items</h6>
                            <h3 class="fw-bold mb-0">
                                {{ $metrics['low_stock_alert_count'] }}
                            </h3>
                        </div>
                        <div >
                            <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Out of Stock Items -->
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Out of Stock</h6>
                            <h3 class="fw-bold mb-0">
                                {{ $metrics['out_of_stock_count'] ?? 0 }}
                            </h3>
                        </div>
                        <div>
                            <i class="bi bi-x-circle fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Sales Today</h6>
                            <h3 class="fw-bold mb-0">{{ $metrics['total_sales_today'] }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-cart-check fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Sales This Month</h6>
                            <h3 class="fw-bold mb-0">{{ $metrics['total_sales_month'] }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-calendar-check fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Sales This Year</h6>
                            <h3 class="fw-bold mb-0">{{ $metrics['total_sales_year'] }}</h3>
                        </div>
                        <div>
                            <i class="bi bi-graph-up fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Stock Value Trend Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="fw-bold mb-0">Stock Value Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="stockValueChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Category Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="fw-bold mb-0">Inventory by Category</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Performance -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3">
            <h5 class="fw-bold mb-0">Warehouse Performance</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($charts['warehouse_utilization'] as $warehouse)
                <div class="col-lg-4 col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">{{ $warehouse->name }}</h6>
                            <span class="badge bg-{{ $warehouse->utilization > 80 ? 'danger' : ($warehouse->utilization > 60 ? 'warning' : 'success') }}">
                                {{ $warehouse->utilization }}%
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Capacity</span>
                                <span>{{ $warehouse->used_capacity }} / {{ $warehouse->total_capacity }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $warehouse->utilization > 80 ? 'danger' : ($warehouse->utilization > 60 ? 'warning' : 'success') }}" 
                                     style="width: {{ $warehouse->utilization }}%"></div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between small text-muted">
                            <span><i class="bi bi-box me-1"></i> {{ $warehouse->item_count }} items</span>
                            <span>{{ $warehouse->utilization > 80 ? 'Overutilized' : ($warehouse->utilization > 60 ? 'Moderate' : 'Optimal') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-warehouse fs-1 mb-3"></i>
                        <p>No warehouse data available</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sales Trend Chart -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3">
            <h5 class="fw-bold mb-0">Sales Trend (Last 7 Days)</h5>
        </div>
        <div class="card-body">
            <canvas id="salesTrendChart" height="250"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Stock Value Trend Chart
    const stockValueData = @json($charts['stock_value_trend']);
    const stockValueCtx = document.getElementById('stockValueChart').getContext('2d');
    new Chart(stockValueCtx, {
        type: 'line',
        data: {
            labels: stockValueData.labels,
            datasets: [{
                label: 'Stock Value ($)',
                data: stockValueData.values,
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
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return `$${context.parsed.y.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Category Distribution Chart
    const categoryData = @json($charts['category_distribution']);
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.labels,
            datasets: [{
                data: categoryData.values,
                backgroundColor: [
                    '#03624C', '#0fb9b1', '#4facfe', '#ff6b6b', '#ffd93d',
                    '#6b5b95', '#88d8b0', '#ffaaa5', '#a8e6cf', '#dcedc1'
                ],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Sales Trend Chart
    const salesTrendData = @json($charts['sales_trend']);
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesTrendCtx, {
        type: 'bar',
        data: {
            labels: salesTrendData.labels,
            datasets: [{
                label: 'Sales Quantity',
                data: salesTrendData.values,
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: '#28a745',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .badge {
        font-weight: 500;
    }
</style>
@endpush
@endsection