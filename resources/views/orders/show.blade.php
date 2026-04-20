@extends('layouts.app')

@section('title','Order Invoice - #' . $order->id)

@section('content')
<style>
    .invoice-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        font-family: Arial, sans-serif;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #03624C;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }

    .company-info h1 {
        color: #03624C;
        margin: 0;
        font-size: 28px;
    }

    .invoice-details {
        text-align: right;
    }

    .invoice-details h2 {
        color: #03624C;
        margin: 0 0 10px 0;
        font-size: 24px;
    }

    .invoice-details p {
        margin: 5px 0;
        color: #666;
    }

    .customer-info, .order-info {
        margin-bottom: 30px;
    }

    .info-section h3 {
        color: #03624C;
        margin: 0 0 10px 0;
        font-size: 16px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .info-item {
        margin-bottom: 8px;
    }

    .info-label {
        font-weight: bold;
        color: #333;
    }

    .info-value {
        color: #666;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .items-table th {
        background: #03624C;
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    .items-table tbody tr:hover {
        background: #f9f9f9;
    }

    .text-right {
        text-align: right;
    }

    .total-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 16px;
    }

    .total-row.final {
        font-size: 20px;
        font-weight: bold;
        color: #03624C;
        border-top: 2px solid #03624C;
        padding-top: 15px;
        margin-top: 15px;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-pending { background: #fef3c7; color: #f59e0b; }
    .status-processing { background: #dbeafe; color: #3b82f6; }
    .status-completed { background: #d1fae5; color: #10b981; }
    .status-cancelled { background: #fee2e2; color: #ef4444; }

    @media print {
        body { background: white; }
        .invoice-container { box-shadow: none; }
        .no-print { display: none; }
    }
</style>

<div class="invoice-container">
    <!-- Header -->
    <div class="invoice-header">
        <div class="company-info">
            <h1>INVENTORY SYSTEM</h1>
            <p>Order Management Invoice</p>
        </div>
        <div class="invoice-details">
            <h2>INVOICE</h2>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
            <p><strong>Status:</strong> <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></p>
        </div>
    </div>

    <!-- Customer & Order Info -->
    <div class="info-grid">
        <div class="customer-info info-section">
            <h3>Bill To:</h3>
            <div class="info-item">
                <span class="info-label">Customer:</span>
                <span class="info-value">{{ $order->user ? $order->user->name : 'Walk-in Customer' }}</span>
            </div>
            @if($order->user)
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $order->user->email }}</span>
            </div>
            @endif
            @if($order->billing_address)
            <div class="info-item">
                <span class="info-label">Billing Address:</span>
                <span class="info-value">{{ $order->billing_address }}</span>
            </div>
            @endif
        </div>

        <div class="order-info info-section">
            <h3>Order Details:</h3>
            <div class="info-item">
                <span class="info-label">Warehouse:</span>
                <span class="info-value">{{ $order->warehouse ? $order->warehouse->name : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Order Date:</span>
                <span class="info-value">{{ $order->created_at->format('M d, Y H:i') }}</span>
            </div>
            @if($order->shipping_address)
            <div class="info-item">
                <span class="info-label">Shipping Address:</span>
                <span class="info-value">{{ $order->shipping_address }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->product_sku ?: 'N/A' }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="total-section">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>${{ number_format($order->total, 2) }}</span>
        </div>
        <div class="total-row final">
            <span>Total Amount:</span>
            <span>${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <!-- Notes -->
    @if($order->notes)
    <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
        <h4 style="margin: 0 0 10px 0; color: #03624C;">Order Notes:</h4>
        <p style="margin: 0; color: #666;">{{ $order->notes }}</p>
    </div>
    @endif

    <!-- Print Button -->
    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer me-2"></i>Print Invoice
        </button>
        <button onclick="window.close()" class="btn btn-secondary ms-2">
            <i class="bi bi-x-circle me-2"></i>Close
        </button>
    </div>
</div>

<script>
    // Auto-print when loaded for printing
    if (window.location.search.includes('print=1')) {
        window.onload = function() {
            window.print();
        }
    }
</script>
@endsection