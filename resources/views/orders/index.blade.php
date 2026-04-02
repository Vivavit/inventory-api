@extends('layouts.app')

@section('title','Orders')

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

    .btn-create-order {
        background: white;
        color: #03624C;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: none;
        cursor: pointer;
    }

    .btn-create-order:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
        animation: fadeInUp 0.6s ease 0.2s backwards;
    }

    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #E9FFFA;
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(3, 98, 76, 0.12);
        border-color: #03624C;
    }

    .summary-value {
        font-size: 32px;
        font-weight: 800;
        color: #03624C;
        margin-bottom: 4px;
    }

    .summary-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .orders-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #E9FFFA;
        overflow: hidden;
        animation: fadeInUp 0.6s ease 0.3s backwards;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table thead {
        background: #03624C;
        color: white;
    }

    .orders-table th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .orders-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #E9FFFA;
        vertical-align: middle;
    }

    .orders-table tbody tr {
        transition: all 0.2s ease;
    }

    .orders-table tbody tr:hover {
        background: #E9FFFA;
    }

    .order-id {
        font-family: Monaco, Consolas, monospace;
        background: #E9FFFA;
        color: #03624C;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }

    .customer-info {
        display: flex;
        flex-direction: column;
    }

    .customer-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
    }

    .customer-email {
        font-size: 12px;
        color: #64748b;
    }

    .warehouse-badge {
        background: #E9FFFA;
        color: #03624C;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .items-list {
        max-width: 350px;
    }

    .item-tag {
        display: inline-block;
        background: #f1f5f9;
        padding: 3px 8px;
        border-radius: 4px;
        margin: 2px;
        font-size: 11px;
        color: #475569;
    }

    .more-items {
        color: #64748b;
        font-size: 11px;
        font-style: italic;
    }

    .price-tag {
        font-weight: 700;
        color: #03624C;
        font-size: 16px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-completed {
        background: rgba(52, 199, 89, 0.15);
        color: #34C759;
    }

    .status-processing {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }

    .status-cancelled {
        background: rgba(239, 68, 68, 0.15);
        color: #FF3B31;
    }

    .order-date {
        display: flex;
        flex-direction: column;
    }

    .order-date-main {
        font-size: 13px;
        color: #374151;
        font-weight: 600;
    }

    .order-time {
        font-size: 11px;
        color: #64748b;
    }

    .action-buttons {
        display: flex;
        gap: 6px;
        justify-content: flex-end;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-view {
        background: #03624C;
        color: white;
    }

    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(3, 98, 76, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state h4 {
        color: #1e293b;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #64748b;
        margin-bottom: 20px;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }

    .modal-header {
        background: #03624C;
        color: white;
        padding: 20px 24px;
        border-bottom: none;
    }

    .modal-header .btn-close {
        filter: invert(1) opacity(0.8);
    }

    .modal-title {
        font-weight: 700;
        font-size: 18px;
    }

    .modal-body {
        padding: 24px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .form-section {
        border: 1px solid #E9FFFA;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .form-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #03624C;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .flabel {
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        margin-bottom: 6px;
        display: block;
    }

    .flabel .req {
        color: #ef4444;
    }

    .finput, .fselect, .ftextarea {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .finput:focus, .fselect:focus, .ftextarea:focus {
        border-color: #03624C;
        outline: none;
        box-shadow: 0 0 0 3px rgba(3, 98, 76, 0.1);
    }

    .product-row {
        background: #f8fafc;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .product-row .form-group {
        flex: 1;
        min-width: 150px;
    }

    .btn-remove-product {
        background: #ef4444;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s;
    }

    .btn-remove-product:hover {
        background: #dc2626;
    }

    .btn-add-product {
        background: #03624C;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        margin-top: 12px;
        width: 100%;
    }

    .btn-add-product:hover {
        background: #024a3a;
    }

    .total-amount {
        background: #03624C;
        color: white;
        padding: 16px;
        border-radius: 10px;
        text-align: right;
        margin-top: 16px;
    }

    .total-amount label {
        font-size: 14px;
        opacity: 0.9;
    }

    .total-amount .total-value {
        font-size: 24px;
        font-weight: 800;
        margin-left: 16px;
    }

    .btn-save {
        background: #03624C;
        color: white;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-save:hover {
        background: #024a3a;
        transform: translateY(-1px);
    }

    .btn-cancel {
        background: white;
        color: #64748b;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        border: 1.5px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #f8fafc;
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
        .orders-table thead {
            display: none;
        }

        .orders-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid #E9FFFA;
            border-radius: 12px;
            padding: 16px;
        }

        .orders-table td {
            display: block;
            padding: 8px 0;
            border: none;
            position: relative;
            padding-left: 40%;
        }

        .orders-table td::before {
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
            justify-content: center;
            margin-top: 12px;
        }

        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 20px;
        }

        .btn-create-order {
            width: 100%;
            justify-content: center;
            margin-top: 12px;
        }

        .summary-card {
            padding: 20px;
        }

        .summary-value {
            font-size: 24px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1>Orders</h1>
        <p>Manage and track customer orders</p>
    </div>
    <button type="button" class="btn-create-order" onclick="openCreateOrderModal()">
        <i class="bi bi-plus-lg"></i> Create Order
    </button>
</div>

<!-- Summary Cards -->
<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-value">{{ $orders->total() }}</div>
        <div class="summary-label">Total Orders</div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: #0fb9b1;">${{ number_format($orders->getCollection()->sum('total'), 0) }}</div>
        <div class="summary-label">Total Revenue</div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: #34C759;">{{ $orders->getCollection()->where('status', 'completed')->count() }}</div>
        <div class="summary-label">Completed</div>
    </div>
    <div class="summary-card">
        <div class="summary-value" style="color: #3b82f6;">{{ $orders->getCollection()->sum(function($order) { return $order->items->sum('quantity'); }) }}</div>
        <div class="summary-label">Items Sold</div>
    </div>
</div>

<!-- Orders Table -->
<div class="orders-card">
    <h3 style="margin: 0 0 20px; color: #03624C; font-weight: 700; font-size: 18px; padding: 20px 24px 0;">
        <i class="bi bi-receipt"></i> Order List
    </h3>

    @if($orders->count() > 0)
        <div class="table-responsive">
            <table class="orders-table">
                <thead>
                    <tr>
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
                        <tr>
                            <td data-label="Order ID">
                                <span class="order-id">#{{ $order->id }}</span>
                            </td>
                            <td data-label="Customer">
                                <div class="customer-info">
                                    <span class="customer-name">{{ $order->user?->name ?? 'N/A' }}</span>
                                    <span class="customer-email">{{ $order->user?->email ?? '' }}</span>
                                </div>
                            </td>
                            <td data-label="Warehouse">
                                <span class="warehouse-badge">{{ $order->warehouse?->name ?? 'N/A' }}</span>
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
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td data-label="Date">
                                <div class="order-date">
                                    <span class="order-date-main">{{ $order->created_at->format('M d, Y') }}</span>
                                    <span class="order-time">{{ $order->created_at->format('h:i A') }}</span>
                                </div>
                            </td>
                            <td style="text-align: right;" data-label="Actions">
                                <div class="action-buttons">
                                    <a href="{{ route('orders.show', $order) }}" class="btn-action btn-view" title="View Order">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: center; padding: 20px;">
            {{ $orders->links() }}
        </div>

    @else
        <div class="empty-state">
            <i class="bi bi-cart"></i>
            <h4>No Orders Yet</h4>
            <p>Create your first order to start tracking sales.</p>
            <button type="button" class="btn-create-order" style="display: inline-flex; margin-top: 12px;" onclick="openCreateOrderModal()">
                <i class="bi bi-plus"></i> Create Order
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
                        <!-- Left Column -->
                        <div class="col-lg-7">
                            <!-- Customer Information -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-person"></i> Customer Information
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="flabel">Customer <span class="req">*</span></label>
                                        <select name="user_id" id="customer_id" class="fselect" required>
                                            <option value="">Select Customer</option>
                                            @foreach($customers ?? [] as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="flabel">Warehouse <span class="req">*</span></label>
                                        <select name="warehouse_id" id="warehouse_id" class="fselect" required>
                                            <option value="">Select Warehouse</option>
                                            @foreach($warehouses ?? [] as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-cart"></i> Order Items
                                </div>
                                <div id="order-items-container">
                                    <div class="product-row" data-index="0">
                                        <div class="form-group">
                                            <label class="flabel">Product <span class="req">*</span></label>
                                            <select name="items[0][product_id]" class="fselect product-select" required>
                                                <option value="">Select Product</option>
                                                @foreach($products ?? [] as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                        {{ $product->name }} - ${{ number_format($product->price, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="flabel">Quantity <span class="req">*</span></label>
                                            <input type="number" name="items[0][quantity]" class="finput quantity-input" value="1" min="1" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="flabel">Unit Price</label>
                                            <input type="text" class="finput unit-price" readonly style="background: #f1f5f9;">
                                        </div>
                                        <div class="form-group">
                                            <label class="flabel">Subtotal</label>
                                            <input type="text" class="finput subtotal" readonly style="background: #f1f5f9;">
                                        </div>
                                        <button type="button" class="btn-remove-product" onclick="removeProductRow(this)">Remove</button>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-product" onclick="addProductRow()">
                                    <i class="bi bi-plus-lg"></i> Add Product
                                </button>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-5">
                            <!-- Order Summary -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-receipt"></i> Order Summary
                                </div>
                                
                                <div class="mb-3">
                                    <label class="flabel">Status</label>
                                    <select name="status" class="fselect">
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="flabel">Shipping Address</label>
                                    <textarea name="shipping_address" class="ftextarea" rows="3" placeholder="Enter shipping address..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="flabel">Billing Address</label>
                                    <textarea name="billing_address" class="ftextarea" rows="3" placeholder="Enter billing address..."></textarea>
                                </div>

                                <div class="total-amount">
                                    <label>Total Amount:</label>
                                    <span class="total-value" id="totalAmount">$0.00</span>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="bi bi-pencil"></i> Order Notes
                                </div>
                                <textarea name="notes" class="ftextarea" rows="3" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="modalErrors" class="d-none" style="margin-top: 16px;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-save" id="submitBtn">
                        <i class="bi bi-check-lg"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let productIndex = 1;

function openCreateOrderModal() {
    const modalEl = document.getElementById('orderModal');
    
    // Destroy stale instance if exists
    const existing = bootstrap.Modal.getInstance(modalEl);
    if (existing) existing.dispose();
    
    // Clean up backdrops
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    
    // Reset form
    resetOrderForm();
    
    // Show modal
    const modal = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true, focus: true });
    modal.show();
}

function resetOrderForm() {
    const form = document.getElementById('orderForm');
    form.reset();
    
    // Reset items container to just one row
    const container = document.getElementById('order-items-container');
    container.innerHTML = `
        <div class="product-row" data-index="0">
            <div class="form-group">
                <label class="flabel">Product <span class="req">*</span></label>
                <select name="items[0][product_id]" class="fselect product-select" required>
                    <option value="">Select Product</option>
                    @foreach($products ?? [] as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->name }} - ${{ number_format($product->price, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="flabel">Quantity <span class="req">*</span></label>
                <input type="number" name="items[0][quantity]" class="finput quantity-input" value="1" min="1" required>
            </div>
            <div class="form-group">
                <label class="flabel">Unit Price</label>
                <input type="text" class="finput unit-price" readonly style="background: #f1f5f9;">
            </div>
            <div class="form-group">
                <label class="flabel">Subtotal</label>
                <input type="text" class="finput subtotal" readonly style="background: #f1f5f9;">
            </div>
            <button type="button" class="btn-remove-product" onclick="removeProductRow(this)">Remove</button>
        </div>
    `;
    productIndex = 1;
    
    // Re-attach event listeners
    attachProductEventListeners();
    
    // Clear errors
    const errBox = document.getElementById('modalErrors');
    errBox.innerHTML = '';
    errBox.classList.add('d-none');
    
    // Reset total
    updateTotal();
}

function addProductRow() {
    const container = document.getElementById('order-items-container');
    const newRow = document.createElement('div');
    newRow.className = 'product-row';
    newRow.setAttribute('data-index', productIndex);
    newRow.innerHTML = `
        <div class="form-group">
            <label class="flabel">Product <span class="req">*</span></label>
            <select name="items[${productIndex}][product_id]" class="fselect product-select" required>
                <option value="">Select Product</option>
                @foreach($products ?? [] as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                        {{ $product->name }} - ${{ number_format($product->price, 2) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="flabel">Quantity <span class="req">*</span></label>
            <input type="number" name="items[${productIndex}][quantity]" class="finput quantity-input" value="1" min="1" required>
        </div>
        <div class="form-group">
            <label class="flabel">Unit Price</label>
            <input type="text" class="finput unit-price" readonly style="background: #f1f5f9;">
        </div>
        <div class="form-group">
            <label class="flabel">Subtotal</label>
            <input type="text" class="finput subtotal" readonly style="background: #f1f5f9;">
        </div>
        <button type="button" class="btn-remove-product" onclick="removeProductRow(this)">Remove</button>
    `;
    container.appendChild(newRow);
    productIndex++;
    
    attachProductEventListeners();
}

function removeProductRow(button) {
    const row = button.closest('.product-row');
    if (document.querySelectorAll('.product-row').length > 1) {
        row.remove();
        updateTotal();
    } else {
        alert('At least one product is required.');
    }
}

function attachProductEventListeners() {
    document.querySelectorAll('.product-select').forEach(select => {
        select.removeEventListener('change', handleProductChange);
        select.addEventListener('change', handleProductChange);
    });
    
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.removeEventListener('input', handleQuantityChange);
        input.addEventListener('input', handleQuantityChange);
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

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal').forEach(field => {
        const value = field.value.replace('$', '');
        if (value) {
            total += parseFloat(value);
        }
    });
    document.getElementById('totalAmount').innerHTML = `$${total.toFixed(2)}`;
}

// Form submission
document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const btn = document.getElementById('submitBtn');
    const originalHTML = btn.innerHTML;
    const errBox = document.getElementById('modalErrors');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
    errBox.innerHTML = '';
    errBox.classList.add('d-none');
    
    // Validate at least one product is selected
    let hasProduct = false;
    document.querySelectorAll('.product-select').forEach(select => {
        if (select.value) hasProduct = true;
    });
    
    if (!hasProduct) {
        errBox.innerHTML = '<div class="alert alert-danger">Please select at least one product.</div>';
        errBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        return;
    }
    
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (!data) return;
        
        if (data.success || data.id) {
            window.location.reload();
            return;
        }
        
        if (data.errors) {
            const errors = Object.values(data.errors).flat();
            const errorHtml = errors.map(err => `<li>${err}</li>`).join('');
            errBox.innerHTML = `<div class="alert alert-danger"><strong>Please fix:</strong><ul class="mb-0 mt-1">${errorHtml}</ul></div>`;
            errBox.classList.remove('d-none');
        } else if (data.message) {
            errBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            errBox.classList.remove('d-none');
        }
        
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    })
    .catch(error => {
        console.error('Error:', error);
        errBox.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        errBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
});

// Clean up on modal close
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('orderModal');
    modal.addEventListener('hidden.bs.modal', function() {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        resetOrderForm();
    });
    
    // Initialize event listeners for the first row
    attachProductEventListeners();
});
</script>
@endpush