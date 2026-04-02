@php
    $totalValue = 0;
    foreach($warehouse->inventoryLocations as $location) {
        if($location->product) {
            $totalValue += $location->quantity * ($location->product->cost_price ?? 0);
        }
    }
@endphp

@extends('layouts.app')

@section('title', $warehouse->name)

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

    .warehouse-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 12px;
    }

    .warehouse-badge {
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-active {
        background: rgba(52, 199, 89, 0.2);
        color: #34C759;
    }

    .badge-inactive {
        background: rgba(239, 68, 68, 0.2);
        color: #FF3B31;
    }

    .badge-default {
        background: rgba(59, 130, 246, 0.2);
        color: #3b82f6;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #E9FFFA;
        transition: all 0.3s ease;
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 24px rgba(3, 98, 76, 0.12);
        border-color: #03624C;
    }

    .stat-icon {
        font-size: 36px;
        color: #03624C;
        margin-bottom: 12px;
        display: inline-block;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #03624C;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 13px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .info-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #E9FFFA;
        margin-bottom: 24px;
        animation: fadeInUp 0.6s ease 0.5s forwards;
        opacity: 0;
    }

    .info-card h3 {
        color: #03624C;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E9FFFA;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-value {
        color: #1e293b;
        font-weight: 600;
        font-size: 14px;
        text-align: right;
    }

    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #E9FFFA;
        overflow: hidden;
        animation: fadeInUp 0.6s ease 0.6s forwards;
        opacity: 0;
    }

    .table-card-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, #f8fafc, #E9FFFA);
        border-bottom: 1px solid #E9FFFA;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #03624C;
        margin: 0;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .custom-table thead {
        background: #03624C;
        color: white;
    }

    .custom-table th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .custom-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #E9FFFA;
        vertical-align: middle;
    }

    .custom-table tbody tr:hover {
        background: #E9FFFA;
    }

    .badge-custom {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-success { background: rgba(52, 199, 89, 0.15); color: #34C759; }
    .badge-warning { background: rgba(255, 204, 0, 0.2); color: #FFCC00; }
    .badge-danger { background: rgba(255, 59, 48, 0.15); color: #FF3B31; }
    .badge-neutral { background: #f1f5f9; color: #64748b; }

    .stock-high { color: #34C759; font-weight: 700; }
    .stock-medium { color: #FFCC00; font-weight: 700; }
    .stock-low { color: #FF3B31; font-weight: 700; }

    .action-btn {
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .action-btn-primary {
        background: #03624C;
        color: white;
    }

    .action-btn-primary:hover {
        background: #024538;
        transform: translateY(-2px);
    }

    .action-btn-secondary {
        background: white;
        color: #03624C;
        border: 2px solid #03624C;
    }

    .action-btn-secondary:hover {
        background: #03624C;
        color: white;
    }

    .quick-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .empty-table {
        text-align: center;
        padding: 48px 20px;
        color: #64748b;
    }

    .empty-table i {
        font-size: 48px;
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
        .stat-card {
            padding: 20px;
        }

        .stat-value {
            font-size: 24px;
        }

        .info-row {
            flex-direction: column;
            gap: 4px;
        }

        .info-value {
            text-align: left;
        }

        .table-card-header {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .custom-table thead {
            display: none;
        }

        .custom-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid #E9FFFA;
            border-radius: 12px;
            padding: 16px;
        }

        .custom-table td {
            display: block;
            padding: 8px 0;
            border: none;
            position: relative;
            padding-left: 40%;
        }

        .custom-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
            position: absolute;
            left: 0;
            top: 8px;
            font-size: 12px;
        }

        .quick-actions {
            flex-direction: column;
        }

        .action-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1>{{ $warehouse->name }}</h1>
        <div class="warehouse-meta">
            <span class="warehouse-badge badge-{{ $warehouse->is_active ? 'active' : 'inactive' }}">
                <i class="bi bi-circle-fill"></i> {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
            </span>
            @if($warehouse->is_default)
                <span class="warehouse-badge badge-default">
                    <i class="bi bi-star-fill"></i> Default
                </span>
            @endif
            <span class="warehouse-badge badge-neutral" style="background: #f1f5f9; color: #64748b;">
                {{ $warehouse->code }}
            </span>
        </div>
    </div>
    <div class="d-flex gap-2" style="margin-top: 12px;">
        <a href="{{ route('warehouses.index') }}" class="action-btn action-secondary" style="background: white; color: #03624C; border: 2px solid #03624C; padding: 10px 20px; text-decoration: none;">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        @can('manage-warehouses')
            <a href="{{ route('warehouses.edit', $warehouse) }}" class="action-btn action-btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
        @endcan
    </div>
</div>

<!-- Stats Grid -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-value">{{ $warehouse->inventoryLocations->count() }}</div>
            <div class="stat-label">Products</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="color: #3b82f6;"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ $warehouse->users->count() }}</div>
            <div class="stat-label">Staff Members</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="color: #34C759;"><i class="bi bi-box-arrow-up"></i></div>
            <div class="stat-value">{{ $warehouse->inventoryLocations->sum('quantity') }}</div>
            <div class="stat-label">Total Stock Units</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="color: #9b59b6;"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">${{ number_format($totalValue, 0) }}</div>
            <div class="stat-label">Inventory Value</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-4">
        <!-- Warehouse Info -->
        <div class="info-card">
            <h3><i class="bi bi-info-circle"></i> Warehouse Information</h3>
            <div class="info-row">
                <span class="info-label"><i class="bi bi-tags"></i> Type</span>
                <span class="info-value">{{ ucfirst($warehouse->type) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="bi bi-arrows-angle-expand"></i> Capacity</span>
                <span class="info-value">{{ $warehouse->capacity ? number_format($warehouse->capacity) . ' sq. ft.' : 'Not specified' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="bi bi-geo-alt"></i> Address</span>
                <span class="info-value">{{ $warehouse->address ?? 'Not specified' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="bi bi-person"></i> Contact Person</span>
                <span class="info-value">{{ $warehouse->contact_person ?? 'Not specified' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="bi bi-telephone"></i> Phone</span>
                <span class="info-value">{{ $warehouse->phone ?? 'Not specified' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="bi bi-envelope"></i> Email</span>
                <span class="info-value">{{ $warehouse->email ?? 'Not specified' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="bi bi-clock"></i> Created</span>
                <span class="info-value">{{ $warehouse->created_at->format('M d, Y') }}</span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="info-card">
            <h3><i class="bi bi-lightning"></i> Quick Actions</h3>
            <div class="quick-actions">
                <a href="{{ route('warehouses.edit', $warehouse) }}#stock" class="action-btn action-btn-secondary">
                    <i class="bi bi-plus-circle"></i> Manage Stock
                </a>
                <a href="{{ route('warehouses.edit', $warehouse) }}#users" class="action-btn action-btn-secondary">
                    <i class="bi bi-people"></i> Assign Staff
                </a>
                <a href="{{ route('products.create') }}" class="action-btn action-btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Product
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column - Stock Table -->
    <div class="col-lg-8">
        <div class="table-card">
            <div class="table-card-header">
                <h3 class="table-card-title"><i class="bi bi-boxes"></i> Products in Warehouse</h3>
                <div class="d-flex gap-2">
                    <button type="button" class="action-btn action-btn-primary" data-bs-toggle="modal" data-bs-target="#addStockModal">
                        <i class="bi bi-plus-lg"></i> Add Stock
                    </button>
                    <a href="{{ route('warehouses.edit', $warehouse) }}#stock" class="action-btn action-btn-secondary">
                        <i class="bi bi-pencil"></i> Edit All
                    </a>
                </div>
            </div>

            @if($warehouse->inventoryLocations->count() > 0)
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th style="text-align: right;">Quantity</th>
                                <th>Location</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: right;">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($warehouse->inventoryLocations as $location)
                                @php
                                    $product = $location->product;
                                    $value = $product ? $location->quantity * ($product->cost_price ?? 0) : 0;
                                @endphp
                                <tr>
                                    <td data-label="Product">
                                        <strong>{{ $product->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td data-label="SKU">
                                        <code class="badge-custom badge-neutral">{{ $product->sku ?? 'N/A' }}</code>
                                    </td>
                                    <td data-label="Category">
                                        {{ $product->category->name ?? 'N/A' }}
                                    </td>
                                    <td data-label="Quantity" style="text-align: right;">
                                        <strong>{{ $location->quantity }}</strong>
                                    </td>
                                    <td data-label="Location">
                                        <span class="badge-custom badge-neutral">{{ $location->location_code ?? 'Default' }}</span>
                                    </td>
                                    <td data-label="Status" style="text-align: center;">
                                        @if($location->quantity <= 0)
                                            <span class="badge-custom badge-danger">Out</span>
                                        @elseif($location->quantity <= 10)
                                            <span class="badge-custom badge-warning">Low</span>
                                        @else
                                            <span class="badge-custom badge-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td data-label="Value" style="text-align: right;" class="stock-high">
                                        ${{ number_format($value, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc;">
                                <td colspan="6" class="fw-bold">Total Inventory Value</td>
                                <td class="text-end fw-bold" style="color: #03624C; font-size: 16px;">
                                    ${{ number_format($totalValue, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="empty-table">
                    <i class="bi bi-inbox"></i>
                    <p class="mb-0">No products in this warehouse yet</p>
                    <a href="{{ route('products.create') }}" class="action-btn action-btn-primary" style="margin-top: 12px; display: inline-flex;">
                        <i class="bi bi-plus"></i> Add Product
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
