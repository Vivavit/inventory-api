@extends('layouts.app')

@section('title', 'Orders')

@section('content')

<style>
    :root {
        --primary: #0D9488;
        --primary-dark: #0F766E;
        --primary-light: #14B8A6;
        --secondary: #6366F1;
        --success: #10B981;
        --warning: #F59E0B;
        --danger: #EF4444;
        --info: #3B82F6;
        --dark: #1F2937;
        --light: #F9FAFB;
        --border: #E5E7EB;
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 32px 36px;
        border-radius: 24px;
        margin-bottom: 32px;
        box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.2);
        transition: all 0.3s ease;
    }

    .page-header h1 {
        margin: 0 0 8px 0;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .page-header p {
        margin: 0;
        opacity: 0.95;
        font-size: 14px;
    }

    /* Buttons */
    .btn-create-order {
        background: white;
        color: var(--primary);
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: none;
        cursor: pointer;
    }

    .btn-create-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        background: white;
        color: var(--primary-dark);
    }

    /* Summary Cards */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .summary-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .summary-value {
        font-size: 36px;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 8px;
    }

    .summary-label {
        font-size: 13px;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Orders Card */
    .orders-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .card-header-custom {
        padding: 24px 28px 0 28px;
        border-bottom: 2px solid #F3F4F6;
    }

    .card-header-custom h3 {
        margin: 0 0 20px 0;
        color: var(--dark);
        font-weight: 700;
        font-size: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Filters Section */
    .filters-section {
        padding: 0 28px 24px 28px;
        border-bottom: 1px solid #F3F4F6;
    }

    .filter-label {
        font-weight: 600;
        color: var(--dark);
        font-size: 12px;
        margin-bottom: 8px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-input, .filter-select {
        width: 100%;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.2s;
        background: white;
    }

    .filter-input:focus, .filter-select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }

    /* Bulk Actions */
    .bulk-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #F3F4F6;
    }

    .btn-bulk {
        padding: 8px 18px;
        font-size: 13px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-bulk-primary {
        background: var(--primary);
        color: white;
    }

    .btn-bulk-primary:hover {
        background: var(--primary-dark);
    }

    .btn-bulk-success {
        background: var(--success);
        color: white;
    }

    .btn-bulk-success:hover {
        background: #059669;
    }

    .btn-bulk-danger {
        background: var(--danger);
        color: white;
    }

    .btn-bulk-danger:hover {
        background: #DC2626;
    }

    /* Table Styles */
    .orders-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .orders-table thead {
        background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
    }

    .orders-table th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6B7280;
        border-bottom: 2px solid var(--border);
    }

    .orders-table td {
        padding: 18px 20px;
        border-bottom: 1px solid #F3F4F6;
        vertical-align: middle;
    }

    .orders-table tbody tr {
        transition: all 0.2s ease;
    }

    .orders-table tbody tr:hover {
        background: #F9FAFB;
    }

    /* Order ID Badge */
    .order-id {
        font-family: 'Courier New', monospace;
        background: linear-gradient(135deg, #E6F7F5 0%, #D1FAF5 100%);
        color: var(--primary);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        display: inline-block;
    }

    /* Customer Info */
    .customer-name {
        font-weight: 700;
        color: var(--dark);
        font-size: 14px;
        margin-bottom: 2px;
    }

    .customer-email {
        font-size: 12px;
        color: #6B7280;
    }

    /* Warehouse Badge */
    .warehouse-badge {
        background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
        color: var(--secondary);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    /* Items Tags */
    .items-list {
        max-width: 300px;
    }

    .item-tag {
        display: inline-block;
        background: #F3F4F6;
        padding: 4px 10px;
        border-radius: 6px;
        margin: 2px;
        font-size: 11px;
        color: var(--dark);
        font-weight: 500;
    }

    .more-items {
        color: #6B7280;
        font-size: 11px;
        font-style: italic;
        margin-left: 4px;
    }

    /* Price Tag */
    .price-tag {
        font-weight: 800;
        color: var(--primary);
        font-size: 16px;
    }

    /* Status Badges */
    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-completed {
        background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
        color: #065F46;
    }

    .status-processing {
        background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
        color: #1E40AF;
    }

    .status-pending {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        color: #92400E;
    }

    .status-cancelled {
        background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
        color: #991B1B;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .btn-view {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
    }

    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
    }

    .btn-edit {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .btn-print {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: white;
    }

    .btn-print:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }


    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-state i {
        font-size: 80px;
        margin-bottom: 20px;
        opacity: 0.3;
        color: var(--primary);
    }

    .empty-state h4 {
        color: var(--dark);
        margin-bottom: 12px;
        font-size: 20px;
        font-weight: 700;
    }

    .empty-state p {
        color: #6B7280;
        margin-bottom: 24px;
        font-size: 14px;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 24px;
        border: none;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 24px 28px;
        border-bottom: none;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .modal-title {
        font-weight: 700;
        font-size: 20px;
    }

    .modal-body {
        padding: 28px;
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Form Styles */
    .form-section {
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        background: #F9FAFB;
        transition: all 0.2s;
    }

    .form-section:hover {
        border-color: var(--primary-light);
    }

    .form-section-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-label {
        font-weight: 600;
        color: var(--dark);
        font-size: 13px;
        margin-bottom: 8px;
        display: block;
    }

    .form-label .required {
        color: var(--danger);
        margin-left: 4px;
    }

    .form-control, .form-select {
        width: 100%;
        border: 1.5px solid var(--border);
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }

    /* Product Row */
    .product-row {
        background: white;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 16px;
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        align-items: flex-end;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
    }

    .product-row .form-group {
        flex: 1;
        min-width: 160px;
    }

    .btn-remove-product {
        background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
        color: var(--danger);
        border: none;
        padding: 8px 16px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-remove-product:hover {
        background: linear-gradient(135deg, #FECACA 0%, #FCA5A5 100%);
    }

    .btn-add-product {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 12px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        margin-top: 16px;
        width: 100%;
        transition: all 0.2s;
    }

    .btn-add-product:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
    }

    /* Total Amount */
    .total-amount {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 20px;
        border-radius: 14px;
        text-align: right;
        margin-top: 20px;
    }

    .total-amount label {
        font-size: 14px;
        opacity: 0.95;
        font-weight: 600;
    }

    .total-amount .total-value {
        font-size: 28px;
        font-weight: 800;
        margin-left: 16px;
    }

    /* Modal Footer Buttons */
    .btn-modal-cancel {
        background: white;
        color: #6B7280;
        padding: 10px 24px;
        border-radius: 12px;
        font-weight: 600;
        border: 1.5px solid var(--border);
        transition: all 0.2s;
    }

    .btn-modal-cancel:hover {
        background: #F9FAFB;
    }

    .btn-modal-save {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 10px 28px;
        border-radius: 12px;
        font-weight: 700;
        border: none;
        transition: all 0.2s;
    }

    .btn-modal-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
    }

    /* Animations */
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in-down {
        animation: fadeInDown 0.6s ease;
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 24px;
        }

        .page-header h1 {
            font-size: 22px;
        }

        .summary-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .orders-table thead {
            display: none;
        }

        .orders-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            background: white;
        }

        .orders-table td {
            display: block;
            padding: 10px 0;
            border: none;
            position: relative;
            padding-left: 120px;
        }

        .orders-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--dark);
            position: absolute;
            left: 0;
            top: 10px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .action-buttons {
            justify-content: flex-start;
            margin-top: 12px;
        }

        .bulk-actions {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }

        .product-row {
            flex-direction: column;
        }

        .product-row .form-group {
            width: 100%;
        }
    }
</style>

<!-- Summary Cards -->
<div class="summary-grid fade-in-up">
    <div class="summary-card">
        <div class="summary-value">{{ $orders->total() }}</div>
        <div class="summary-label">
            <i class="bi bi-cart-check"></i>
            Total Orders
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: var(--success);">
            ${{ number_format($orders->getCollection()->sum('total'), 0) }}
        </div>
        <div class="summary-label">
            <i class="bi bi-currency-dollar"></i>
            Total Revenue
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: var(--success);">
            {{ $orders->getCollection()->where('status', 'completed')->count() }}
        </div>
        <div class="summary-label">
            <i class="bi bi-check-circle"></i>
            Completed
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: var(--info);">
            {{ $orders->getCollection()->sum(function($order) { return $order->items->sum('quantity'); }) }}
        </div>
        <div class="summary-label">
            <i class="bi bi-box-seam"></i>
            Items Sold
        </div>
    </div>
</div>

<!-- Orders Table Card -->
<div class="orders-card fade-in-up">
    <div class="card-header-custom" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; padding-bottom: 16px;">
        <h3>
            <i class="bi bi-table"></i>
            Order List
        </h3>
        <button type="button" class="btn-create-order" id="createOrderBtn">
        <i class="bi bi-plus-circle"></i>
        <span>Create Order</span>
    </button>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="filter-label">Status</label>
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="filter-label">Customer</label>
                <select id="customerFilter" class="filter-select">
                    <option value="">All Customers</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="filter-label">Warehouse</label>
                <select id="warehouseFilter" class="filter-select">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses ?? [] as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="filter-label">From Date</label>
                <input type="date" id="fromDateFilter" class="filter-input" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="filter-label">To Date</label>
                <input type="date" id="toDateFilter" class="filter-input" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-1">
                <div class="d-flex gap-2">
                    <button type="button" class="btn-bulk btn-bulk-primary" id="applyFiltersBtn" style="padding: 10px 16px;">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <button type="button" class="btn-bulk" id="clearFiltersBtn" style="background: #6B7280; color: white; padding: 10px 16px;">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <div class="d-flex gap-2">
                <button type="button" class="btn-bulk btn-bulk-primary" id="selectAllBtn">
                    <i class="bi bi-check-all"></i> Select All
                </button>
                <button type="button" class="btn-bulk btn-bulk-success" id="exportExcelBtn">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </button>
                <button type="button" class="btn-bulk btn-bulk-danger" id="printInvoicesBtn">
                    <i class="bi bi-printer"></i> Print Invoices
                </button>
            </div>
            <div class="text-muted" style="font-size: 13px;">
                <i class="bi bi-check2-square"></i>
                <span id="selectedCount">0</span> orders selected
            </div>
        </div>
    </div>

    @if($orders->count() > 0)
        <div class="table-responsive">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAllCheckbox" style="transform: scale(1.2); cursor: pointer;">
                        </th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Warehouse</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr data-order-id="{{ $order->id }}">
                            <td data-label="Select" style="text-align: center;">
                                <input type="checkbox" class="order-checkbox" value="{{ $order->id }}" style="transform: scale(1.2); cursor: pointer;">
                            </td>
                            <td data-label="Order ID">
                                <span class="order-id">#{{ $order->id }}</span>
                            </td>
                            <td data-label="Customer">
                                <div>
                                    <div class="customer-name">{{ $order->user?->name ?? 'Guest Customer' }}</div>
                                    <div class="customer-email">{{ $order->user?->email ?? 'No email' }}</div>
                                </div>
                            </td>
                            <td data-label="Warehouse">
                                <span class="warehouse-badge">
                                    <i class="bi bi-building"></i> {{ $order->warehouse?->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td data-label="Items">
                                <div class="items-list">
                                    @foreach($order->items->take(3) as $item)
                                        <span class="item-tag">{{ Str::limit($item->product_name, 20) }} ({{ $item->quantity }})</span>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <span class="more-items">+{{ $order->items->count() - 3 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Total">
                                <div class="price-tag">${{ number_format($order->total, 2) }}</div>
                            </td>
                            <td data-label="Status">
                                <span class="status-badge status-{{ strtolower($order->status) }}">
                                    <i class="bi 
                                        @if($order->status == 'completed') bi-check-circle
                                        @elseif($order->status == 'processing') bi-arrow-repeat
                                        @elseif($order->status == 'pending') bi-clock-history
                                        @else bi-x-circle
                                        @endif
                                    "></i>
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td data-label="Date">
                                <div>
                                    <div style="font-weight: 600; font-size: 13px;">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div style="font-size: 11px; color: #6B7280;">{{ $order->created_at->format('h:i A') }}</div>
                                </div>
                            </td>
                            <td style="text-align: right;" data-label="Actions">
                                <div class="action-buttons">
                                    <button type="button" class="btn-action btn-view view-order-btn" data-order-id="{{ $order->id }}" title="View Order">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn-action btn-edit edit-order-btn" data-order-id="{{ $order->id }}" title="Edit Order">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn-action btn-print print-order-btn" data-order-id="{{ $order->id }}" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: center; padding: 24px;">
            {{ $orders->links() }}
        </div>

    @else
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h4>No Orders Yet</h4>
            <p>Get started by creating your first order</p>
            <button type="button" class="btn-create-order" id="emptyStateCreateBtn">
                <i class="bi bi-plus-circle"></i> Create Order
            </button>
        </div>
    @endif
</div>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Create New Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="orderForm" method="POST" action="{{ route('orders.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-person-circle"></i>
                                    Customer Information
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Customer</label>
                                        <select name="user_id" id="customer_id" class="form-select">
                                            <option value="">Select Customer (Optional)</option>
                                            @foreach($customers ?? [] as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Warehouse <span class="required">*</span></label>
                                        <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                                            <option value="">Select Warehouse</option>
                                            @foreach($warehouses ?? [] as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-cart-plus"></i>
                                    Order Items
                                </div>
                                <div id="order-items-container">
                                    <div class="product-row" data-index="0">
                                        <div class="form-group">
                                            <label class="form-label">Product <span class="required">*</span></label>
                                            <select name="items[0][product_id]" class="form-select product-select" required>
                                                <option value="">Select Product</option>
                                                @foreach($products ?? [] as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                        {{ $product->name }} - ${{ number_format($product->price, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Quantity <span class="required">*</span></label>
                                            <input type="number" name="items[0][quantity]" class="form-control quantity-input" value="1" min="1" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Unit Price</label>
                                            <input type="text" class="form-control unit-price" readonly style="background: #F3F4F6;">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Subtotal</label>
                                            <input type="text" class="form-control subtotal" readonly style="background: #F3F4F6;">
                                        </div>
                                        <button type="button" class="btn-remove-product remove-product-btn">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-product" id="addProductBtn">
                                    <i class="bi bi-plus-lg"></i> Add Another Product
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-receipt"></i>
                                    Order Summary
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" selected>Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Shipping Address</label>
                                    <textarea name="shipping_address" class="form-control" rows="3" placeholder="Enter shipping address..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Billing Address</label>
                                    <textarea name="billing_address" class="form-control" rows="3" placeholder="Enter billing address..."></textarea>
                                </div>

                                <div class="total-amount">
                                    <label>Total Amount:</label>
                                    <span class="total-value" id="totalAmount">$0.00</span>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-pencil-square"></i>
                                    Order Notes
                                </div>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="modalErrors" class="d-none" style="margin-top: 16px;"></div>
                </div>

                <div class="modal-footer" style="padding: 20px 28px; border-top: 1px solid var(--border);">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                    <button type="submit" class="btn-modal-save" id="submitBtn">
                        <i class="bi bi-check-lg"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOrderModalLabel">
                    <i class="bi bi-eye me-2"></i>View Order #<span id="viewOrderId"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="viewOrderContent">
                    <!-- Content loaded via JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn-modal-save" id="printCurrentInvoiceBtn">Print Invoice</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrderModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Order #<span id="editOrderId"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editOrderForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div id="editOrderContent">
                        <!-- Content loaded via JS -->
                    </div>
                    <div id="editModalErrors" class="d-none" style="margin-top: 16px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal-save" id="editSubmitBtn">Update Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let productIndex = 1;
    let editProductIndex = 0;
    let currentViewOrderId = null;
    
    // Initialize Bootstrap modals
    const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    const viewOrderModal = new bootstrap.Modal(document.getElementById('viewOrderModal'));
    const editOrderModal = new bootstrap.Modal(document.getElementById('editOrderModal'));
    
    // Helper Functions
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('#order-items-container .subtotal').forEach(field => {
            const value = field.value.replace('$', '');
            if (value) total += parseFloat(value);
        });
        document.getElementById('totalAmount').innerHTML = `$${total.toFixed(2)}`;
    }
    
    function updateEditTotal() {
        let total = 0;
        document.querySelectorAll('#editOrderItemsContainer .edit-subtotal').forEach(field => {
            const value = field.value.replace('$', '');
            if (value) total += parseFloat(value);
        });
        document.getElementById('editTotalAmount').innerHTML = `$${total.toFixed(2)}`;
    }
    
    function attachProductEventListeners() {
        document.querySelectorAll('#order-items-container .product-select').forEach(select => {
            select.removeEventListener('change', handleProductChange);
            select.addEventListener('change', handleProductChange);
        });
        
        document.querySelectorAll('#order-items-container .quantity-input').forEach(input => {
            input.removeEventListener('input', handleQuantityChange);
            input.addEventListener('input', handleQuantityChange);
        });
        
        document.querySelectorAll('#order-items-container .remove-product-btn').forEach(btn => {
            btn.removeEventListener('click', removeProductRow);
            btn.addEventListener('click', removeProductRow);
        });
    }
    
    function attachEditProductEventListeners() {
        document.querySelectorAll('#editOrderItemsContainer .edit-product-select').forEach(select => {
            select.removeEventListener('change', handleEditProductChange);
            select.addEventListener('change', handleEditProductChange);
        });
        
        document.querySelectorAll('#editOrderItemsContainer .edit-quantity-input').forEach(input => {
            input.removeEventListener('input', handleEditQuantityChange);
            input.addEventListener('input', handleEditQuantityChange);
        });
        
        document.querySelectorAll('#editOrderItemsContainer .remove-edit-product-btn').forEach(btn => {
            btn.removeEventListener('click', removeEditProductRow);
            btn.addEventListener('click', removeEditProductRow);
        });
    }
    
    function handleProductChange(e) {
        const select = e.target;
        const row = select.closest('.product-row');
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const unitPriceField = row.querySelector('.unit-price');
        const quantityField = row.querySelector('.quantity-input');
        const subtotalField = row.querySelector('.subtotal');
        
        unitPriceField.value = `$${parseFloat(price).toFixed(2)}`;
        const quantity = parseInt(quantityField.value) || 0;
        const subtotal = quantity * parseFloat(price);
        subtotalField.value = `$${subtotal.toFixed(2)}`;
        updateTotal();
    }
    
    function handleQuantityChange(e) {
        const input = e.target;
        const row = input.closest('.product-row');
        const select = row.querySelector('.product-select');
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const quantity = parseInt(input.value) || 0;
        const subtotalField = row.querySelector('.subtotal');
        const subtotal = quantity * parseFloat(price);
        subtotalField.value = `$${subtotal.toFixed(2)}`;
        updateTotal();
    }
    
    function handleEditProductChange(e) {
        const select = e.target;
        const row = select.closest('.product-row');
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const unitPriceField = row.querySelector('.edit-unit-price');
        const quantityField = row.querySelector('.edit-quantity-input');
        const subtotalField = row.querySelector('.edit-subtotal');
        
        unitPriceField.value = `$${parseFloat(price).toFixed(2)}`;
        const quantity = parseInt(quantityField.value) || 0;
        const subtotal = quantity * parseFloat(price);
        subtotalField.value = `$${subtotal.toFixed(2)}`;
        updateEditTotal();
    }
    
    function handleEditQuantityChange(e) {
        const input = e.target;
        const row = input.closest('.product-row');
        const select = row.querySelector('.edit-product-select');
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const quantity = parseInt(input.value) || 0;
        const subtotalField = row.querySelector('.edit-subtotal');
        const subtotal = quantity * parseFloat(price);
        subtotalField.value = `$${subtotal.toFixed(2)}`;
        updateEditTotal();
    }
    
    function removeProductRow(e) {
        const button = e.target;
        const row = button.closest('.product-row');
        if (document.querySelectorAll('#order-items-container .product-row').length > 1) {
            row.remove();
            updateTotal();
        } else {
            alert('At least one product is required.');
        }
    }
    
    function removeEditProductRow(e) {
        const button = e.target;
        const row = button.closest('.product-row');
        if (document.querySelectorAll('#editOrderItemsContainer .product-row').length > 1) {
            row.remove();
            updateEditTotal();
        } else {
            alert('At least one product is required.');
        }
    }
    
    function addProductRow() {
        const container = document.getElementById('order-items-container');
        const newRow = document.createElement('div');
        newRow.className = 'product-row';
        newRow.setAttribute('data-index', productIndex);
        newRow.innerHTML = `
            <div class="form-group">
                <label class="form-label">Product <span class="required">*</span></label>
                <select name="items[${productIndex}][product_id]" class="form-select product-select" required>
                    <option value="">Select Product</option>
                    @foreach($products ?? [] as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->name }} - ${{ number_format($product->price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity <span class="required">*</span></label>
                <input type="number" name="items[${productIndex}][quantity]" class="form-control quantity-input" value="1" min="1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Unit Price</label>
                <input type="text" class="form-control unit-price" readonly style="background: #F3F4F6;">
            </div>
            <div class="form-group">
                <label class="form-label">Subtotal</label>
                <input type="text" class="form-control subtotal" readonly style="background: #F3F4F6;">
            </div>
            <button type="button" class="btn-remove-product remove-product-btn"><i class="bi bi-trash"></i> Remove</button>
        `;
        container.appendChild(newRow);
        productIndex++;
        attachProductEventListeners();
    }
    
    function addEditProductRow() {
        const container = document.getElementById('editOrderItemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'product-row';
        newRow.setAttribute('data-index', editProductIndex);
        newRow.innerHTML = `
            <div class="form-group">
                <label class="form-label">Product <span class="required">*</span></label>
                <select name="items[${editProductIndex}][product_id]" class="form-select edit-product-select" required>
                    <option value="">Select Product</option>
                    @foreach($products ?? [] as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->name }} - ${{ number_format($product->price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity <span class="required">*</span></label>
                <input type="number" name="items[${editProductIndex}][quantity]" class="form-control edit-quantity-input" value="1" min="1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Unit Price</label>
                <input type="text" class="form-control edit-unit-price" readonly style="background: #F3F4F6;">
            </div>
            <div class="form-group">
                <label class="form-label">Subtotal</label>
                <input type="text" class="form-control edit-subtotal" readonly style="background: #F3F4F6;">
            </div>
            <button type="button" class="btn-remove-product remove-edit-product-btn"><i class="bi bi-trash"></i> Remove</button>
        `;
        container.appendChild(newRow);
        editProductIndex++;
        attachEditProductEventListeners();
    }
    
    function resetOrderForm() {
        const container = document.getElementById('order-items-container');
        container.innerHTML = `
            <div class="product-row" data-index="0">
                <div class="form-group">
                    <label class="form-label">Product <span class="required">*</span></label>
                    <select name="items[0][product_id]" class="form-select product-select" required>
                        <option value="">Select Product</option>
                        @foreach($products ?? [] as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                {{ $product->name }} - ${{ number_format($product->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity <span class="required">*</span></label>
                    <input type="number" name="items[0][quantity]" class="form-control quantity-input" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Price</label>
                    <input type="text" class="form-control unit-price" readonly style="background: #F3F4F6;">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtotal</label>
                    <input type="text" class="form-control subtotal" readonly style="background: #F3F4F6;">
                </div>
                <button type="button" class="btn-remove-product remove-product-btn"><i class="bi bi-trash"></i> Remove</button>
            </div>
        `;
        productIndex = 1;
        document.getElementById('orderForm').reset();
        document.getElementById('customer_id').value = '';
        document.getElementById('warehouse_id').value = '';
        attachProductEventListeners();
        updateTotal();
    }
    
    // CRUD Functions
    function openCreateOrderModal() {
        resetOrderForm();
        orderModal.show();
    }
    
    function viewOrder(orderId) {
        fetch(`/orders/${orderId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.order;
                currentViewOrderId = order.id;
                let html = `
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-info-circle"></i> Order Information</div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Order ID</label><div class="p-3 bg-light rounded">#${order.id}</div></div>
                                    <div class="col-md-6"><label class="form-label">Status</label><div class="status-badge status-${order.status}">${order.status}</div></div>
                                    <div class="col-md-6"><label class="form-label">Customer</label><div class="p-3 bg-light rounded">${order.user ? order.user.name : 'Guest'}</div></div>
                                    <div class="col-md-6"><label class="form-label">Warehouse</label><div class="p-3 bg-light rounded">${order.warehouse ? order.warehouse.name : 'N/A'}</div></div>
                                    <div class="col-md-6"><label class="form-label">Order Date</label><div class="p-3 bg-light rounded">${new Date(order.created_at).toLocaleDateString()}</div></div>
                                    <div class="col-md-6"><label class="form-label">Total</label><div class="p-3 bg-light rounded price-tag">$${parseFloat(order.total).toFixed(2)}</div></div>
                                </div>
                            </div>
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-cart"></i> Order Items</div>
                                <table class="table"><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>
                                ${order.items.map(item => `<tr><td>${item.product_name}</td><td>${item.quantity}</td><td>$${parseFloat(item.price).toFixed(2)}</td><td>$${parseFloat(item.subtotal).toFixed(2)}</td></tr>`).join('')}
                                </tbody></table>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-section"><div class="form-section-title"><i class="bi bi-geo-alt"></i> Addresses</div>
                                <div class="mb-3"><label class="form-label">Shipping</label><div class="p-3 bg-light rounded">${order.shipping_address || 'Not provided'}</div></div>
                                <div class="mb-3"><label class="form-label">Billing</label><div class="p-3 bg-light rounded">${order.billing_address || 'Not provided'}</div></div>
                            </div>
                            <div class="form-section"><div class="form-section-title"><i class="bi bi-pencil"></i> Notes</div>
                                <div class="p-3 bg-light rounded">${order.notes || 'No notes'}</div>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('viewOrderContent').innerHTML = html;
                viewOrderModal.show();
            }
        });
    }
    
    function editOrder(orderId) {
        fetch(`/orders/${orderId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.order;
                document.getElementById('editOrderId').textContent = order.id;
                document.getElementById('editOrderForm').action = `/orders/${order.id}`;
                
                let html = `
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-person-circle"></i> Customer Information</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Customer</label>
                                        <select name="user_id" id="editCustomerId" class="form-select">
                                            <option value="">Select Customer</option>
                                            @foreach($customers ?? [] as $customer)
                                                <option value="{{ $customer->id }}" ${order.user_id == {{ $customer->id }} ? 'selected' : ''}>{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Warehouse</label>
                                        <select name="warehouse_id" id="editWarehouseId" class="form-select" required>
                                            @foreach($warehouses ?? [] as $warehouse)
                                                <option value="{{ $warehouse->id }}" ${order.warehouse_id == {{ $warehouse->id }} ? 'selected' : ''}>{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-cart-plus"></i> Order Items</div>
                                <div id="editOrderItemsContainer"></div>
                                <button type="button" class="btn-add-product" id="addEditProductBtn">Add Another Product</button>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-receipt"></i> Order Summary</div>
                                <div class="mb-3"><label class="form-label">Status</label><select name="status" id="editOrderStatus" class="form-select">
                                    <option value="pending" ${order.status == 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="processing" ${order.status == 'processing' ? 'selected' : ''}>Processing</option>
                                    <option value="completed" ${order.status == 'completed' ? 'selected' : ''}>Completed</option>
                                    <option value="cancelled" ${order.status == 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select></div>
                                <div class="mb-3"><label class="form-label">Shipping Address</label><textarea name="shipping_address" id="editShippingAddress" class="form-control" rows="3">${order.shipping_address || ''}</textarea></div>
                                <div class="mb-3"><label class="form-label">Billing Address</label><textarea name="billing_address" id="editBillingAddress" class="form-control" rows="3">${order.billing_address || ''}</textarea></div>
                                <div class="total-amount"><label>Total Amount:</label><span class="total-value" id="editTotalAmount">$${parseFloat(order.total).toFixed(2)}</span></div>
                            </div>
                            <div class="form-section"><div class="form-section-title"><i class="bi bi-pencil-square"></i> Notes</div>
                                <textarea name="notes" id="editOrderNotes" class="form-control" rows="3">${order.notes || ''}</textarea>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('editOrderContent').innerHTML = html;
                
                // Rebuild items container
                const container = document.getElementById('editOrderItemsContainer');
                container.innerHTML = '';
                editProductIndex = 0;
                order.items.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'product-row';
                    row.innerHTML = `
                        <div class="form-group"><label class="form-label">Product</label>
                            <select name="items[${editProductIndex}][product_id]" class="form-select edit-product-select" required>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" ${item.product_id == {{ $product->id }} ? 'selected' : ''}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Quantity</label>
                            <input type="number" name="items[${editProductIndex}][quantity]" class="form-control edit-quantity-input" value="${item.quantity}" min="1" required>
                        </div>
                        <div class="form-group"><label class="form-label">Unit Price</label>
                            <input type="text" class="form-control edit-unit-price" value="$${parseFloat(item.price).toFixed(2)}" readonly>
                        </div>
                        <div class="form-group"><label class="form-label">Subtotal</label>
                            <input type="text" class="form-control edit-subtotal" value="$${parseFloat(item.subtotal).toFixed(2)}" readonly>
                        </div>
                        <button type="button" class="btn-remove-product remove-edit-product-btn">Remove</button>
                    `;
                    container.appendChild(row);
                    editProductIndex++;
                });
                
                attachEditProductEventListeners();
                document.getElementById('addEditProductBtn').onclick = addEditProductRow;
                editOrderModal.show();
            }
        });
    }
    
    function deleteOrder(orderId) {
        if (confirm('Are you sure you want to delete this order?')) {
            fetch(`/orders/${orderId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) window.location.reload();
                else alert(data.message);
            });
        }
    }
    
    function printInvoice(orderId) {
        window.open(`/orders/${orderId}?print=1`, '_blank');
    }
    
    function updateSelectedCount() {
        const count = document.querySelectorAll('.order-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = count;
    }
    
    function selectAllOrders() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const selectAll = document.getElementById('selectAllCheckbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSelectedCount();
    }
    
    function exportSelectedToExcel() {
        const selected = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return alert('Please select orders to export');
        window.location.href = `/orders/export/excel?orders[]=${selected.join('&orders[]=')}`;
    }
    
    function printSelectedInvoices() {
        const selected = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return alert('Please select orders to print');
        selected.forEach((id, i) => setTimeout(() => printInvoice(id), i * 500));
    }
    
    function applyFilters() {
        const params = new URLSearchParams();
        if (document.getElementById('statusFilter').value) params.set('status', document.getElementById('statusFilter').value);
        if (document.getElementById('customerFilter').value) params.set('customer', document.getElementById('customerFilter').value);
        if (document.getElementById('warehouseFilter').value) params.set('warehouse', document.getElementById('warehouseFilter').value);
        if (document.getElementById('fromDateFilter').value) params.set('from_date', document.getElementById('fromDateFilter').value);
        if (document.getElementById('toDateFilter').value) params.set('to_date', document.getElementById('toDateFilter').value);
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
    
    function clearFilters() {
        window.location.href = window.location.pathname;
    }
    
    // Form Submissions
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                orderModal.hide();
                window.location.reload();
            } else {
                alert(data.message || 'Error creating order');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    });
    
    document.getElementById('editOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('editSubmitBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
        
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                editOrderModal.hide();
                window.location.reload();
            } else {
                alert(data.message || 'Error updating order');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    });
    
    // Event Listeners
    document.getElementById('createOrderBtn').addEventListener('click', openCreateOrderModal);
    document.getElementById('emptyStateCreateBtn')?.addEventListener('click', openCreateOrderModal);
    document.getElementById('addProductBtn').addEventListener('click', addProductRow);
    document.getElementById('selectAllBtn').addEventListener('click', selectAllOrders);
    document.getElementById('exportExcelBtn').addEventListener('click', exportSelectedToExcel);
    document.getElementById('printInvoicesBtn').addEventListener('click', printSelectedInvoices);
    document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
    document.getElementById('clearFiltersBtn').addEventListener('click', clearFilters);
    document.getElementById('selectAllCheckbox').addEventListener('change', selectAllOrders);
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.addEventListener('change', updateSelectedCount));
    document.querySelectorAll('.view-order-btn').forEach(btn => btn.addEventListener('click', () => viewOrder(btn.dataset.orderId)));
    document.querySelectorAll('.edit-order-btn').forEach(btn => btn.addEventListener('click', () => editOrder(btn.dataset.orderId)));
    document.querySelectorAll('.print-order-btn').forEach(btn => btn.addEventListener('click', () => printInvoice(btn.dataset.orderId)));
    document.querySelectorAll('.delete-order-btn').forEach(btn => btn.addEventListener('click', () => deleteOrder(btn.dataset.orderId)));
    
    // Initialize
    attachProductEventListeners();
    updateSelectedCount();
});
</script>
@endpush