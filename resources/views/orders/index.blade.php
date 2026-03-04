@extends('layouts.app')
@section('title','Orders')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Orders</h3>
        <small class="text-muted">Order management</small>
    </div>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Order
    </a>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="custom-card text-center">
            <h4 class="fw-bold text-primary">{{ $orders->total() }}</h4>
            <small class="text-muted">Total Orders</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="custom-card text-center">
            <h4 class="fw-bold text-success">${{ number_format($orders->sum('total'), 2) }}</h4>
            <small class="text-muted">Total Value</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="custom-card text-center">
            <h4 class="fw-bold text-info">{{ $orders->where('status', 'completed')->count() }}</h4>
            <small class="text-muted">Completed Orders</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="custom-card text-center">
            <h4 class="fw-bold text-warning">{{ $orders->sum(function($order) { return $order->items->sum('quantity'); }) }}</h4>
            <small class="text-muted">Total Products Ordered</small>
        </div>
    </div>
</div>

<div class="custom-card">
    <div class="section-title">Order List</div>
    <table class="table align-middle">
        <thead class="text-muted small">
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Warehouse</th>
                <th>Products</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td>
                    <div class="fw-semibold">#{{ $order->id }}</div>
                </td>
                <td>{{ $order->user->name ?? 'N/A' }}</td>
                <td>{{ $order->warehouse->name ?? 'N/A' }}</td>
                <td>
                    @if($order->items->count() > 0)
                        <small>
                            @foreach($order->items as $item)
                                {{ $item->product_name }} ({{ $item->quantity }})
                                @if(!$loop->last), @endif
                            @endforeach
                        </small>
                    @else
                        <small class="text-muted">No items</small>
                    @endif
                </td>
                <td>${{ number_format($order->total, 2) }}</td>
                <td>
                    <span class="badge bg-{{ $order->status === 'completed' ? 'success' : 'danger' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td>
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-4">
        {{ $orders->links() }}
    </div>
</div>

@endsection