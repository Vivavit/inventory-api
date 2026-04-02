@extends('layouts.app')

@section('title', 'Stock Movement Report')

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

    .type-badge {
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 600;
    }

    .type-in {
        background: rgba(52, 199, 89, 0.15);
        color: #34C759;
    }

    .type-out {
        background: rgba(255, 59, 48, 0.15);
        color: #FF3B31;
    }

    .quantity-positive {
        color: #34C759;
        font-weight: 700;
    }

    .quantity-negative {
        color: #FF3B31;
        font-weight: 700;
    }

    .user-name {
        font-weight: 600;
        color: #1e293b;
    }

    .notes-text {
        font-size: 13px;
        color: #64748b;
        max-width: 250px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.4;
    }

    .empty-state h4 {
        color: #1e293b;
        margin-bottom: 8px;
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

        .notes-text {
            max-width: 100%;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1>Stock Movement Report</h1>
        <p>Track inventory changes over time</p>
    </div>
    <div class="action-buttons">
        <button onclick="window.print()" class="btn-action">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ route('reports.low-stock') }}" class="btn-action">
            <i class="bi bi-exclamation-triangle"></i> Low Stock
        </a>
    </div>
</div>

<div class="report-card">
    <h3><i class="bi bi-clock-history" style="color: #03624C;"></i> Recent Stock Movements</h3>

    @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Transaction ID</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Type</th>
                        <th style="text-align: right;">Quantity</th>
                        <th>User</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td data-label="Date & Time">
                                <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                                <small style="color: #64748b;">{{ $transaction->created_at->format('H:i') }}</small>
                            </td>
                            <td data-label="Transaction ID">
                                <span class="order-id">#{{ $transaction->id }}</span>
                            </td>
                            <td data-label="Product">
                                <strong>{{ $transaction->product->name ?? 'N/A' }}</strong>
                            </td>
                            <td data-label="Warehouse">
                                {{ $transaction->warehouse->name ?? 'N/A' }}
                            </td>
                            <td data-label="Type">
                                @if($transaction->type === 'in')
                                    <span class="type-badge type-in">Stock In</span>
                                @else
                                    <span class="type-badge type-out">Stock Out</span>
                                @endif
                            </td>
                            <td data-label="Quantity" style="text-align: right;">
                                <span class="{{ $transaction->quantity > 0 ? 'quantity-positive' : 'quantity-negative' }}">
                                    {{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}
                                </span>
                            </td>
                            <td data-label="User">
                                <span class="user-name">{{ $transaction->user->name ?? 'System' }}</span>
                            </td>
                            <td data-label="Notes">
                                <small class="notes-text">{{ $transaction->notes ?? 'No notes' }}</small>
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
        <div class="empty-state">
            <i class="bi bi-clock-history"></i>
            <h4>No Stock Movements</h4>
            <p>No inventory transactions have been recorded yet.</p>
        </div>
    @endif
</div>

@endsection
