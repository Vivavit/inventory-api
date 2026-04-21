@extends('layouts.app')

@section('title', 'Orders')

@section('content')

@push('styles')
    @vite(['resources/css/features/orders.css'])
@endpush

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
    @vite(['resources/js/features/orders.js'])
@endpush