@extends('layouts.app')

@section('title','Order Details')

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

    .btn-back {
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

    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #E9FFFA;
        margin-bottom: 24px;
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }

    .card:nth-child(2) { animation-delay: 0.1s; }
    .card:nth-child(3) { animation-delay: 0.2s; }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        color: #03624C;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E9FFFA;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .info-item {
        padding: 12px;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 3px solid #03624C;
    }

    .info-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
    }

    .order-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .order-table thead {
        background: #03624C;
        color: white;
    }

    .order-table th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .order-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #E9FFFA;
        vertical-align: middle;
    }

    .order-table tbody tr:hover {
        background: #E9FFFA;
    }

    .product-name {
        font-weight: 600;
        color: #1e293b;
    }

    .sku-badge {
        font-family: Monaco, Consolas, monospace;
        background: #E9FFFA;
        color: #03624C;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
    }

    .price-value {
        font-weight: 600;
        color: #03624C;
    }

    .summary-box {
        background: linear-gradient(135deg, #E9FFFA, #d6f5ed);
        border-radius: 12px;
        padding: 24px;
        border: 2px solid #03624C;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .summary-row:last-of-type {
        margin-bottom: 0;
        padding-top: 12px;
        border-top: 2px solid #03624C;
        margin-top: 12px;
    }

    .summary-label {
        color: #64748b;
    }

    .summary-value {
        font-weight: 700;
        color: #1e293b;
    }

    .total-row {
        font-size: 18px;
        color: #03624C;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-completed {
        background: rgba(52, 199, 89, 0.15);
        color: #34C759;
    }

    .status-processing {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }

    .status-cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: #FF3B31;
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
        .order-table thead {
            display: none;
        }

        .order-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid #E9FFFA;
            border-radius: 12px;
            padding: 16px;
        }

        .order-table td {
            display: block;
            padding: 8px 0;
            border: none;
            position: relative;
            padding-left: 40%;
        }

        .order-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
            position: absolute;
            left: 0;
            top: 8px;
            font-size: 12px;
            text-transform: uppercase;
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
        <h1>Order #{{ $order->id }}</h1>
        <p>Order details and products</p>
    </div>
    <a href="{{ route('orders.index') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>
</div>

<!-- Order Information -->
<div class="card">
    <h3 class="card-title"><i class="bi bi-info-circle"></i> Order Information</h3>
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Customer</div>
            <div class="info-value">{{ $order->user?->name ?? 'N/A' }}</div>
            <small style="color: #64748b; font-size: 12px;">{{ $order->user?->email ?? '' }}</small>
        </div>
        <div class="info-item">
            <div class="info-label">Warehouse</div>
            <div class="info-value">{{ $order->warehouse?->name ?? 'N/A' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Status</div>
            <div class="info-value">
                <span class="status-badge status-{{ strtolower($order->status) }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">Order Date</div>
            <div class="info-value">{{ $order->created_at->format('M d, Y') }}</div>
            <small style="color: #64748b; font-size: 12px;">{{ $order->created_at->format('h:i A') }}</small>
        </div>
    </div>
</div>

<!-- Products Ordered -->
<div class="card">
    <h3 class="card-title"><i class="bi bi-cart"></i> Products Ordered</h3>
    <div class="table-responsive">
        <table class="order-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th style="text-align: center;">Quantity</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->items as $item)
                    <tr>
                        <td data-label="Product">
                            <div class="product-name">{{ $item->product_name }}</div>
                        </td>
                        <td data-label="SKU">
                            <span class="sku-badge">{{ $item->product_sku }}</span>
                        </td>
                        <td data-label="Quantity" style="text-align: center;">
                            <strong>{{ $item->quantity }}</strong>
                        </td>
                        <td data-label="Price" style="text-align: right;">
                            <div class="price-value">${{ number_format($item->price, 2) }}</div>
                        </td>
                        <td data-label="Subtotal" style="text-align: right;">
                            <div class="price-value">${{ number_format($item->subtotal, 2) }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 32px;">
                            <i class="bi bi-inbox" style="font-size: 32px; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                            No items in this order
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Order Summary -->
<div class="col-md-4" style="animation-delay: 0.3s;">
    <div class="card">
        <h3 class="card-title"><i class="bi bi-calculator"></i> Order Summary</h3>
        <div class="summary-box">
            <div class="summary-row">
                <span class="summary-label">Total Items</span>
                <span class="summary-value">{{ $order->items->sum('quantity') }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Total Products</span>
                <span class="summary-value">{{ $order->items->count() }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Subtotal</span>
                <span class="summary-value">${{ number_format($order->total, 2) }}</span>
            </div>
            <div class="summary-row total-row">
                <span class="summary-label">Total Amount</span>
                <span class="summary-value">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>
</div>

@endsection
