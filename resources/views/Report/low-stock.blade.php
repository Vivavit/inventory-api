@extends('layouts.app')

@section('title', 'Low Stock Report')

@section('content')

<style>
    .page-header {
        background: linear-gradient(135deg, #03624C, #0fb9b1);
        color: white;
        padding: 28px 32px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(3, 98, 76, 0.2);
        animation: fadeInDown 0.6s ease;
    }

    .page-header h1 {
        margin: 0 0 6px 0;
        font-size: 26px;
        font-weight: 800;
    }

    .page-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 13px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        background: white;
        color: #03624C;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .report-card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #E9FFFA;
        margin-bottom: 24px;
        animation: fadeInUp 0.6s ease 0.2s backwards;
        opacity: 0;
    }

    .report-card h3 {
        color: #03624C;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E9FFFA;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
    }

    .report-table thead {
        background: #03624C;
        color: white;
    }

    .report-table th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .report-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #E9FFFA;
        vertical-align: middle;
    }

    .report-table tbody tr:hover {
        background: #E9FFFA;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-low {
        background: rgba(255, 204, 0, 0.2);
        color: #FFCC00;
    }

    .status-out {
        background: rgba(255, 59, 48, 0.15);
        color: #FF3B31;
    }

    .difference-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .difference-negative {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }

    .action-link {
        color: #03624C;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 16px;
        border: 2px solid #03624C;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .action-link:hover {
        background: #03624C;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #64748b;
    }

    .empty-state i {
        font-size: 56px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .report-table thead {
            display: none;
        }

        .report-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid #E9FFFA;
            border-radius: 12px;
            padding: 16px;
        }

        .report-table td {
            display: block;
            padding: 8px 0;
            border: none;
            position: relative;
            padding-left: 40%;
        }

        .report-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
            position: absolute;
            left: 0;
            top: 8px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .action-buttons {
            flex-direction: column;
        }

        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 20px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1>Low Stock Report</h1>
        <p>Products needing replenishment</p>
    </div>
    <div class="action-buttons">
        <button onclick="window.print()" class="btn-action">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ route('reports.stock-movement') }}" class="btn-action">
            <i class="bi bi-arrow-left-right"></i> Stock Movements
        </a>
    </div>
</div>

<div class="report-card">
    <h3><i class="bi bi-exclamation-triangle" style="color: #FFCC00;"></i> Low Stock Items</h3>

    @if($lowStockProducts->count() > 0)
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align: right;">Current Stock</th>
                        <th style="text-align: right;">Reorder Level</th>
                        <th style="text-align: right;">Difference</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $product)
                        @php
                            $totalStock = $product->warehouseProducts->sum('quantity');
                            $difference = $product->default_low_stock_threshold - $totalStock;
                        @endphp
                        <tr>
                            <td data-label="Product">
                                <strong>{{ $product->name }}</strong><br>
                                <small style="color: #64748b;">SKU: {{ $product->sku }}</small>
                            </td>
                            <td data-label="Current Stock" style="text-align: right;">
                                <strong>{{ $totalStock }}</strong>
                            </td>
                            <td data-label="Reorder Level" style="text-align: right;">
                                {{ $product->default_low_stock_threshold }}
                            </td>
                            <td data-label="Difference" style="text-align: right;">
                                <span class="difference-badge {{ $difference > 0 ? 'difference-negative' : '' }}">
                                    {{ $difference > 0 ? '+' . $difference : $difference }}
                                </span>
                            </td>
                            <td data-label="Status">
                                @if($totalStock <= 0)
                                    <span class="status-badge status-out">Out of Stock</span>
                                @else
                                    <span class="status-badge status-low">Low Stock</span>
                                @endif
                            </td>
                            <td data-label="Action">
                                <a href="{{ route('products.show', $product) }}" class="action-link">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-check-circle" style="color: #34C759;"></i>
            <h4>All Stock Levels Good</h4>
            <p>No products are currently below their reorder threshold.</p>
        </div>
    @endif
</div>

@endsection
