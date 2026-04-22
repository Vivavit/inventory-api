@extends('layouts.app')

@section('title','Purchase Order Details')

@section('content')

<style>
    .page-header {
        background: linear-gradient(135deg, #03624C, #0fb9b1);
        color: white;
        padding: 28px 32px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(3, 98, 76, 0.2);
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

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-draft { background: #e3f2fd; color: #1976d2; }
    .status-pending { background: #fff3e0; color: #f57c00; }
    .status-ordered { background: #e8f5e8; color: #388e3c; }
    .status-partially_received { background: #f3e5f5; color: #7b1fa2; }
    .status-received { background: #e8f5e8; color: #2e7d32; }
    .status-cancelled { background: #ffebee; color: #d32f2f; }

    .detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
        margin-bottom: 24px;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 20px;
        border-bottom: 1px solid #e0e0e0;
        border-radius: 12px 12px 0 0;
    }

    .card-body-custom {
        padding: 24px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #495057;
    }

    .info-value {
        font-weight: 500;
        color: #212529;
    }

    .btn-receive {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .btn-receive:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
    }

    .btn-back {
        background: #6c757d;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .item-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .item-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
        padding: 12px;
        text-align: left;
    }

    .item-table td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
    }

    .item-table tbody tr:hover {
        background: #f8f9fa;
    }

    .progress-bar {
        height: 8px;
        border-radius: 4px;
    }

    .receive-form {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 16px;
    }

    html.dark .detail-card,
    html.dark .item-table,
    html.dark .receive-form {
        background: #0f172a;
        border-color: #334155;
        box-shadow: none;
        color: #e5e7eb;
    }
    html.dark .card-header-custom {
        background: linear-gradient(135deg, #111827, #0f172a);
        border-color: #334155;
    }
    html.dark .card-header-custom h5,
    html.dark .info-label,
    html.dark .info-value,
    html.dark .item-table th,
    html.dark .item-table td {
        color: #f8fafc;
    }
    html.dark .info-row,
    html.dark .item-table td {
        border-color: #1f2937;
    }
    html.dark .item-table th {
        background: #111827;
    }
    html.dark .item-table tbody tr:hover {
        background: #111827;
    }
    html.dark .btn-back {
        background: #334155;
    }
    html.dark .btn-back:hover {
        background: #475569;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-receipt me-2"></i>{{ $purchaseOrder->po_number }}</h1>
                <p>Purchase Order Details</p>
            </div>
            <div class="d-flex gap-2">
                @if(in_array($purchaseOrder->status, ['ordered', 'partially_received']))
                    <button class="btn-receive" onclick="toggleReceiveForm()">
                        <i class="bi bi-box-arrow-in-down me-2"></i>Receive Stock
                    </button>
                @endif
                <a href="{{ route('purchase-orders.index') }}" class="btn-back">
                    <i class="bi bi-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Order Information -->
        <div class="col-lg-8">
            <div class="detail-card">
                <div class="card-header-custom">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle me-2"></i>Order Information
                    </h4>
                </div>
                <div class="card-body-custom">
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-{{ $purchaseOrder->status }}">
                            {{ ucwords(str_replace('_', ' ', $purchaseOrder->status)) }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Supplier</span>
                        <span class="info-value">{{ $purchaseOrder->supplier->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Warehouse</span>
                        <span class="info-value">{{ $purchaseOrder->warehouse->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Date</span>
                        <span class="info-value">{{ $purchaseOrder->order_date->format('M d, Y') }}</span>
                    </div>
                    @if($purchaseOrder->expected_delivery_date)
                    <div class="info-row">
                        <span class="info-label">Expected Delivery</span>
                        <span class="info-value">{{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}</span>
                    </div>
                    @endif
                    @if($purchaseOrder->actual_delivery_date)
                    <div class="info-row">
                        <span class="info-label">Actual Delivery</span>
                        <span class="info-value">{{ $purchaseOrder->actual_delivery_date->format('M d, Y') }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Created By</span>
                        <span class="info-value">{{ $purchaseOrder->creator->name ?? 'Unknown' }}</span>
                    </div>
                    @if($purchaseOrder->notes)
                    <div class="info-row">
                        <span class="info-label">Notes</span>
                        <span class="info-value">{{ $purchaseOrder->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Items -->
            <div class="detail-card">
                <div class="card-header-custom">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam me-2"></i>Order Items
                    </h4>
                </div>
                <div class="card-body-custom">
                    <div class="item-table">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Received</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->product->sku)
                                            <br><small class="text-muted">{{ $item->product->sku }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>${{ number_format($item->total_price, 2) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span>{{ $item->received_quantity }} / {{ $item->quantity }}</span>
                                            @if($item->quantity > 0)
                                            <div class="progress ms-2 flex-fill" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                     style="width: {{ ($item->received_quantity / $item->quantity) * 100 }}%"
                                                     aria-valuenow="{{ $item->received_quantity }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="{{ $item->quantity }}"></div>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->received_quantity == 0)
                                            <span class="badge bg-warning">Not Received</span>
                                        @elseif($item->received_quantity < $item->quantity)
                                            <span class="badge bg-info">Partial</span>
                                        @else
                                            <span class="badge bg-success">Received</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="detail-card">
                <div class="card-header-custom">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-calculator me-2"></i>Order Summary
                    </h4>
                </div>
                <div class="card-body-custom">
                    <div class="info-row">
                        <span class="info-label">Subtotal</span>
                        <span class="info-value">${{ number_format($purchaseOrder->subtotal, 2) }}</span>
                    </div>
                    @if($purchaseOrder->tax_amount > 0)
                    <div class="info-row">
                        <span class="info-label">Tax</span>
                        <span class="info-value">${{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($purchaseOrder->shipping_cost > 0)
                    <div class="info-row">
                        <span class="info-label">Shipping</span>
                        <span class="info-value">${{ number_format($purchaseOrder->shipping_cost, 2) }}</span>
                    </div>
                    @endif
                    <div class="info-row border-top pt-3">
                        <span class="info-label fw-bold">Total Amount</span>
                        <span class="info-value fw-bold text-primary fs-5">${{ number_format($purchaseOrder->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Receive Stock Form -->
            @if(in_array($purchaseOrder->status, ['ordered', 'partially_received']))
            <div class="detail-card" id="receiveForm" style="display: none;">
                <div class="card-header-custom">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-box-arrow-in-down me-2"></i>Receive Stock
                    </h4>
                </div>
                <div class="card-body-custom">
                    <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST">
                        @csrf
                        <input type="hidden" name="warehouse_id" value="{{ $purchaseOrder->warehouse_id }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Receiving Items</label>
                            @foreach($purchaseOrder->items as $item)
                            <div class="receive-form">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-medium">{{ $item->product->name }}</span>
                                    <small class="text-muted">Ordered: {{ $item->quantity }} | Received: {{ $item->received_quantity }}</small>
                                </div>
                                <div class="row g-2">
                                    <div class="col-8">
                                        <input type="number" name="items[{{ $item->id }}][received]"
                                               class="form-control" min="0"
                                               max="{{ $item->quantity - $item->received_quantity }}"
                                               placeholder="Quantity to receive">
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Remaining: {{ $item->quantity - $item->received_quantity }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-check-circle me-2"></i>Receive Stock
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleReceiveForm()">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleReceiveForm() {
    const form = document.getElementById('receiveForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

@endsection
