@extends('layouts.app')
@section('title', 'Stock Movement Report')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Stock Movement Report</h3>
        <small class="text-muted">Track inventory changes over time</small>
    </div>
    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-light">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ route('reports.low-stock') }}" class="btn btn-light">
            <i class="bi bi-exclamation-triangle"></i> Low Stock
        </a>
    </div>
</div>

<div class="custom-card">
    <div class="section-title">Recent Stock Movements</div>
    
    @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="text-muted small">
                    <tr>
                        <th>Date & Time</th>
                        <th>Transaction ID</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>User</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>
                            <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                        </td>
                        <td>#{{ $transaction->id }}</td>
                        <td>{{ $transaction->product->name }}</td>
                        <td>{{ $transaction->warehouse->name }}</td>
                        <td>
                            @if($transaction->type === 'in')
                                <span class="badge bg-success">Stock In</span>
                            @else
                                <span class="badge bg-danger">Stock Out</span>
                            @endif
                        </td>
                        <td>{{ $transaction->quantity }}</td>
                        <td>{{ $transaction->user->name }}</td>
                        <td>
                            <small class="text-muted">{{ $transaction->notes ?? 'No notes' }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-3">
            {{ $transactions->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-clock-history fs-1 text-muted"></i>
            <p class="mt-3">No stock movements found</p>
            <small class="text-muted">No inventory transactions have been recorded yet.</small>
        </div>
    @endif
</div>

@endsection