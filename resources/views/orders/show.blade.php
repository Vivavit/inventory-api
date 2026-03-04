@extends('layouts.app')
@section('title','Order Details')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Order #{{ $order->id }}</h3>
        <small class="text-muted">Order details and products</small>
    </div>
    <a href="{{ route('orders.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="custom-card">
            <div class="section-title">Order Information</div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>User:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                    <p><strong>Warehouse:</strong> {{ $order->warehouse->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $order->status === 'completed' ? 'success' : 'danger' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                    <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="custom-card">
            <div class="section-title">Products Ordered</div>
            <table class="table">
                <thead class="text-muted small">
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($order->items as $item)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $item->product_name }}</div>
                        </td>
                        <td>{{ $item->product_sku }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No items in this order</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="custom-card">
            <div class="section-title">Order Summary</div>
            <div class="d-flex justify-content-between mb-2">
                <span>Total Items:</span>
                <strong>{{ $order->items->sum('quantity') }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Total Products:</span>
                <strong>{{ $order->items->count() }}</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
                <span><strong>Total Amount:</strong></span>
                <strong class="text-primary">${{ number_format($order->total, 2) }}</strong>
            </div>
        </div>
    </div>
</div>

@endsection