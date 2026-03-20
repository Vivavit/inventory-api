@extends('layouts.app')
@section('title','Orders')

@section('content')

<style>
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding: 24px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,.04);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .summary-card {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,.04);
    }

    .summary-card h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: var(--green);
    }

    .summary-card p {
        margin: 8px 0 0;
        font-size: 12px;
        color: #999;
    }
</style>

<!-- Page Header -->
<div class="order-header">
    <div>
        <h1 class="page-title">Orders</h1>
        <p style="color: #999; margin: 0; font-size: 13px;">Manage and track customer orders</p>
    </div>
    <a href="{{ route('orders.create') }}" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-lg"></i> Add Order
    </a>
</div>

<!-- Summary Cards -->
<div class="summary-grid">
    <div class="summary-card">
        <h3>{{ $orders->total() }}</h3>
        <p>Total Orders</p>
    </div>
    <div class="summary-card">
        <h3 style="color: var(--teal);">${{ number_format($orders->sum('total'), 0) }}</h3>
        <p>Total Revenue</p>
    </div>
    <div class="summary-card">
        <h3 style="color: #2ecc71;">{{ $orders->where('status', 'completed')->count() }}</h3>
        <p>Completed Orders</p>
    </div>
    <div class="summary-card">
        <h3 style="color: var(--blue);">{{ $orders->sum(function($order) { return $order->items->sum('quantity'); }) }}</h3>
        <p>Items Ordered</p>
    </div>
</div>

<!-- Orders Table -->
<div class="custom-card">
    <h2 style="margin: 0 0 24px; color: var(--green); font-weight: 700; font-size: 18px;">
        <i class="bi bi-receipt"></i> Order List
    </h2>

    @if($orders->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Warehouse</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width: 80px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>
                            <strong style="color: var(--green);">#{{ $order->id }}</strong>
                        </td>
                        <td>
                            <div style="font-weight: 500;">{{ $order->user->name ?? 'N/A' }}</div>
                            <small style="color: #999;">{{ $order->user->email ?? '' }}</small>
                        </td>
                        <td>
                            <span class="badge badge-success">{{ $order->warehouse->name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($order->items->count() > 0)
                                <small style="color: #666;">
                                    {{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}
                                </small>
                            @else
                                <small style="color: #999;">No items</small>
                            @endif
                        </td>
                        <td>
                            <strong style="color: var(--green); font-size: 16px;">${{ number_format($order->total, 2) }}</strong>
                        </td>
                        <td>
                            @if($order->status === 'completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($order->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-danger">{{ ucfirst($order->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <small style="color: #999;">{{ $order->created_at->format('M d, Y') }}</small>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-secondary" title="View Order">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: center; margin-top: 24px;">
            {{ $orders->links() }}
        </div>
    @else
        <div style="text-align: center; padding: 48px 24px;">
            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 12px;"></i>
            <p style="color: #999; margin: 0;">No orders found. Create one to get started!</p>
        </div>
    @endif
</div>

@endsection