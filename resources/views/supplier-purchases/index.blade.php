@extends('layouts.app')

@section('title', 'Supplier Purchases')

@section('content')

<style>
    .page-header { background:#03624C;color:white;padding:28px 32px;border-radius:14px;margin-bottom:24px;box-shadow:0 4px 20px rgba(3,98,76,0.18); }
    .page-header h1 { margin:0 0 4px;font-size:24px;font-weight:800;letter-spacing:-.3px; }
    .page-header p  { margin:0;opacity:.75;font-size:13px; }
    .btn-add { background:white;color:#03624C;padding:9px 18px;font-size:13px;font-weight:700;border-radius:8px;border:none;display:inline-flex;align-items:center;gap:6px;cursor:pointer;transition:box-shadow .2s,transform .2s;white-space:nowrap;text-decoration:none; }
    .btn-add:hover { box-shadow:0 4px 14px rgba(0,0,0,.15);transform:translateY(-1px);color:#03624C; }
    .purchases-card { background:white;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #eaf7f4;overflow:hidden; }
    .purchases-table { width:100%;border-collapse:collapse; }
    .purchases-table thead { background:#03624C;color:white; }
    .purchases-table th { padding:13px 16px;text-align:left;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.6px;border:none; }
    .purchases-table td { padding:13px 16px;border-bottom:1px solid #f0faf7;vertical-align:middle;font-size:13px; }
    .purchases-table tbody tr:last-child td { border-bottom:none; }
    .purchases-table tbody tr:hover { background:#fafffe; }
    .po-tag { font-family:'Courier New',monospace;background:#e9fff9;padding:3px 8px;border-radius:4px;font-size:11px;color:#03624C;font-weight:700; }
    .supplier-name { font-weight:600;color:#1a2e28;font-size:13px; }
    .status-badge { padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:4px; }
    .status-pending   { background:#fef9c3;color:#854d0e;border:1px solid #fde047; }
    .status-completed { background:#dcfce7;color:#166534;border:1px solid #86efac; }
    .status-cancelled { background:#f1f5f9;color:#475569;border:1px solid #cbd5e1; }
    .amount-val { font-weight:700;color:#03624C;font-size:14px; }
    .action-buttons { display:flex;gap:5px;justify-content:flex-end; }
    .btn-ico { width:30px;height:30px;border-radius:6px;border:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:13px;color:white;text-decoration:none;transition:opacity .15s,transform .15s; }
    .btn-ico:hover { opacity:.85;transform:translateY(-1px);color:white; }
    .ico-view    { background:#03624C; }
    .ico-confirm { background:#16a34a; }
    .ico-delete  { background:#ef4444; }
    .empty-state { text-align:center;padding:70px 20px;color:#94a3b8; }
    .empty-state i { font-size:56px;display:block;margin-bottom:12px;opacity:.4; }
    /* modals */
    .modal-backdrop { z-index:1040!important; }
    .modal          { z-index:1050!important; }
    .modal-content  { border-radius:14px;border:none;box-shadow:0 24px 60px rgba(0,0,0,.22); }
    .modal-header   { background:#03624C;color:white;border-radius:14px 14px 0 0;border:none;padding:18px 24px; }
    .modal-header .btn-close { filter:invert(1) opacity(.8); }
    .modal-title    { font-weight:700;font-size:16px; }
    .modal-body     { padding:24px;max-height:74vh;overflow-y:auto; }
    .modal-footer   { padding:14px 24px;border-top:1px solid #edf7f4;gap:8px; }
    .form-section   { border:1px solid #e8f5f0;border-radius:10px;padding:18px;margin-bottom:18px; }
    .form-section-title { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#03624C;margin-bottom:14px;display:flex;align-items:center;gap:6px; }
    .flabel { font-weight:600;color:#374151;font-size:12px;margin-bottom:5px;display:block; }
    .flabel .req { color:#ef4444; }
    .finput,.fselect,.ftextarea { width:100%;border:1.5px solid #e2e8f0;border-radius:7px;padding:9px 12px;font-size:13px;transition:border-color .2s,box-shadow .2s;background:white;color:#1e293b; }
    .finput:focus,.fselect:focus,.ftextarea:focus { border-color:#03624C;box-shadow:0 0 0 3px rgba(3,98,76,.08);outline:none; }
    .ftextarea { resize:vertical; }
    .input-prefix { display:flex;align-items:center;border:1.5px solid #e2e8f0;border-radius:7px;overflow:hidden;transition:border-color .2s,box-shadow .2s; }
    .input-prefix:focus-within { border-color:#03624C;box-shadow:0 0 0 3px rgba(3,98,76,.08); }
    .input-prefix span { background:#f8fafc;padding:9px 11px;font-size:13px;color:#64748b;border-right:1.5px solid #e2e8f0;flex-shrink:0; }
    .input-prefix input { border:none;padding:9px 12px;font-size:13px;flex:1;outline:none;color:#1e293b; }
    .items-table { width:100%;border-collapse:collapse;font-size:12px; }
    .items-table th { padding:7px 10px;background:#f8fafc;color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid #e2e8f0;text-align:left; }
    .items-table td { padding:8px 10px;border-bottom:1px solid #f0f4f8;vertical-align:middle; }
    .items-table tbody tr:last-child td { border-bottom:none; }
    .item-input { border:1.5px solid #e2e8f0;border-radius:6px;padding:6px 8px;font-size:12px;width:100%;transition:border-color .2s; }
    .item-input:focus { border-color:#03624C;outline:none; }
    .btn-add-row { padding:7px 14px;background:#f0faf7;border:1.5px dashed #9ecfc4;border-radius:7px;font-size:12px;font-weight:600;color:#03624C;cursor:pointer;width:100%;margin-top:8px;transition:background .15s; }
    .btn-add-row:hover { background:#e0f5ef; }
    .btn-remove-row { width:24px;height:24px;background:#fee2e2;border:none;border-radius:5px;color:#ef4444;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s; }
    .btn-remove-row:hover { background:#fecaca; }
    .btn-save   { background:#03624C;color:white;padding:10px 22px;border-radius:8px;font-weight:600;font-size:13px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:background .2s,transform .2s; }
    .btn-save:hover { background:#024a3a;transform:translateY(-1px); }
    .btn-cancel { background:white;color:#64748b;padding:10px 20px;border-radius:8px;font-weight:600;font-size:13px;border:1.5px solid #e2e8f0;cursor:pointer;transition:background .15s; }
    .btn-cancel:hover { background:#f8fafc; }
    /* detail */
    .detail-row { display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f0f4f8;font-size:13px; }
    .detail-row:last-child { border-bottom:none; }
    .detail-label { color:#64748b;font-weight:500; }
    .detail-value { font-weight:600;color:#1a2e28;text-align:right; }
    .detail-items-table { width:100%;border-collapse:collapse;font-size:12px; }
    .detail-items-table th { padding:8px 12px;background:#f8fafc;color:#64748b;font-weight:600;font-size:11px;text-transform:uppercase;border-bottom:1px solid #e2e8f0;text-align:left; }
    .detail-items-table td { padding:10px 12px;border-bottom:1px solid #f0f4f8;vertical-align:middle; }
    .detail-items-table tbody tr:last-child td { border-bottom:none; }
    .total-row { background:#f0faf7;border-radius:8px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;margin-top:12px; }
    .total-row span { font-size:13px;color:#64748b;font-weight:600; }
    .total-row strong { font-size:18px;color:#03624C;font-weight:800; }
</style>

{{-- Page Header --}}
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1>Supplier Purchases</h1>
        <p>Manage purchase orders and supplier transactions</p>
    </div>
    <button type="button" class="btn-add" onclick="openCreateModal()">
        <i class="bi bi-plus-lg"></i> Create Purchase
    </button>
</div>

<div class="row g-3 align-items-center mb-4">
    <div class="col-md-6">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="finput" placeholder="Search PO number or supplier" value="{{ $search ?? '' }}">
            <select name="status" class="fselect" style="max-width:180px;">
                <option value="">All statuses</option>
                <option value="pending" {{ (isset($status) && $status === 'pending') ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ (isset($status) && $status === 'completed') ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ (isset($status) && $status === 'cancelled') ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn-save" style="padding:10px 18px;">Filter</button>
        </form>
    </div>
    <div class="col-md-6 text-md-end" style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
        @if(isset($search) && $search)
            <span class="badge bg-info text-dark">Search: {{ $search }}</span>
        @endif
        @if(isset($status) && $status)
            <span class="badge bg-success text-white">Status: {{ ucfirst($status) }}</span>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center mb-4" style="border-radius:10px;">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger d-flex align-items-center mb-4" style="border-radius:10px;">
        <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
    </div>
@endif

<div class="purchases-card">
    @if($purchases->count() > 0)
        <div class="table-responsive">
            <table class="purchases-table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Order Date</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                        @php
                            $sc = match($purchase->status) { 'pending'=>'status-pending','completed'=>'status-completed',default=>'status-cancelled' };
                            $si = match($purchase->status) { 'pending'=>'bi-clock','completed'=>'bi-check-circle',default=>'bi-x-circle' };
                        @endphp
                        <tr>
                            <td><span class="po-tag">{{ $purchase->po_number }}</span></td>
                            <td><span class="supplier-name">{{ $purchase->supplier->name }}</span></td>
                            <td>
                                <span class="status-badge {{ $sc }}">
                                    <i class="bi {{ $si }}"></i> {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                            <td><span class="amount-val">${{ number_format($purchase->total_amount, 2) }}</span></td>
                            <td style="color:#4a7c6f;">{{ $purchase->order_date->format('M d, Y') }}</td>
                            <td style="text-align:right;">
                                <div class="action-buttons">
                                    <button type="button" class="btn-ico ico-view"
                                            onclick="openDetailModal({{ $purchase->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if($purchase->status === 'pending')
                                        <form method="POST" action="{{ route('supplier-purchases.confirm', $purchase) }}"
                                              class="d-inline me-2"
                                              onsubmit="return confirm('Confirm this purchase? This will increase product stock.')">
                                            @csrf
                                            <button type="submit" class="btn-ico ico-confirm" title="Confirm">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn-ico ico-edit"
                                                onclick="openEditModal({{ $purchase->id }})"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn-ico ico-delete"
                                                onclick="openDeleteModal({{ $purchase->id }}, '{{ $purchase->po_number }}')"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="display:flex;justify-content:center;padding:18px;">{{ $purchases->links() }}</div>
    @else
        <div class="empty-state">
            <i class="bi bi-receipt"></i>
            <h5 style="color:#475569;">No Purchase Orders Yet</h5>
            <p style="font-size:13px;">Create your first supplier purchase to get started.</p>
            <button type="button" class="btn-add" style="margin-top:10px;" onclick="openCreateModal()">
                <i class="bi bi-plus-lg"></i> Create Purchase
            </button>
        </div>
    @endif
</div>


{{-- PURCHASE FORM MODAL --}}
<div class="modal fade" id="purchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purchaseModalTitle"><i class="bi bi-plus-circle me-2"></i>Create New Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="purchaseForm" method="POST" action="{{ route('supplier-purchases.store') }}">
                @csrf
                <input type="hidden" name="_method" id="purchaseMethod" value="POST">
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-lg-8">

                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-info-circle"></i> Order Information</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="flabel">Supplier <span class="req">*</span></label>
                                        <select id="supplierSelect" name="supplier_id" class="fselect" required>
                                            <option value="">— Select Supplier —</option>
                                            @foreach($suppliers as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="flabel">Order Date <span class="req">*</span></label>
                                        <input type="date" name="order_date" id="cOrderDate" class="finput" required value="{{ now()->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="flabel">Expected Delivery Date</label>
                                        <input type="date" id="cDeliveryDate" name="expected_delivery_date" class="finput">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="flabel">Warehouse</label>
                                        <select id="warehouseSelect" name="warehouse_id" class="fselect">
                                            <option value="">— Default Warehouse —</option>
                                            @foreach($warehouses as $wh)
                                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-list-ul"></i> Order Items</div>
                                <div class="table-responsive">
                                    <table class="items-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th style="width:100px;">Qty</th>
                                                <th style="width:130px;">Unit Price</th>
                                                <th style="width:110px;">Subtotal</th>
                                                <th style="width:36px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody"></tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn-add-row" onclick="addItemRow()">
                                    <i class="bi bi-plus-lg me-1"></i> Add Item
                                </button>
                                <div class="total-row" style="margin-top:12px;">
                                    <span>Order Total</span>
                                    <strong id="orderTotal">$0.00</strong>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-chat-text"></i> Notes</div>
                                <textarea name="notes" class="finput ftextarea" rows="3" placeholder="Optional notes or instructions…"></textarea>
                            </div>

                        </div>
                        <div class="col-lg-4">

                            <div class="form-section">
                                <div class="form-section-title"><i class="bi bi-truck"></i> Shipping & Payment</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="flabel">Shipping Cost</label>
                                        <div class="input-prefix">
                                            <span>$</span>
                                            <input type="number" step="0.01" min="0" name="shipping_cost"
                                                   id="cShippingCost" placeholder="0.00" oninput="recalcTotal()">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="flabel">Payment Terms</label>
                                        <select name="payment_terms" class="fselect">
                                            <option value="">— Select —</option>
                                            <option value="net_30">Net 30</option>
                                            <option value="net_60">Net 60</option>
                                            <option value="net_90">Net 90</option>
                                            <option value="immediate">Immediate</option>
                                            <option value="cod">Cash on Delivery</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div style="background:#f8fffe;border:1px solid #d4ede7;border-radius:10px;padding:14px;font-size:12px;color:#4a7c6f;">
                                <strong style="display:flex;align-items:center;gap:5px;margin-bottom:8px;color:#03624C;"><i class="bi bi-lightbulb"></i> Tips</strong>
                                <ul style="margin:0;padding-left:16px;line-height:1.9;">
                                    <li>PO number is auto-generated.</li>
                                    <li>Confirming a purchase updates stock.</li>
                                    <li>Only pending orders can be deleted.</li>
                                    <li>Unit price pre-fills from product cost.</li>
                                </ul>
                            </div>

                        </div>
                    </div>
                    <div id="purchaseErrors" class="d-none" style="margin-top:16px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-save" id="purchaseSubmitBtn">
                        <i class="bi bi-check-lg"></i> Create Purchase
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- DETAIL MODAL --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel"><i class="bi bi-receipt me-2"></i>Purchase Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2 text-muted" style="font-size:13px;">Loading details…</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


{{-- HISTORY MODAL --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Product Stock History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-5" id="historyLoading">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2 text-muted" style="font-size:13px;">Loading history…</p>
                </div>
                <div id="historyContent" class="d-none">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Change</th>
                                <th>Warehouse</th>
                                <th>User</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header" style="background:#ef4444;">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Delete Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:28px 24px;text-align:center;">
                <div style="width:60px;height:60px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="bi bi-trash" style="font-size:24px;color:#ef4444;"></i>
                </div>
                <h6 style="font-weight:700;color:#1e293b;margin-bottom:8px;">Are you sure?</h6>
                <p style="font-size:13px;color:#64748b;margin:0;">
                    You are about to delete purchase order
                    <strong id="deletePONumber" style="color:#ef4444;font-family:'Courier New',monospace;"></strong>.
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer" style="justify-content:center;gap:10px;">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-save" style="background:#ef4444;">
                        <i class="bi bi-trash me-1"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const PRODUCTS = @json($products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'price'=>$p->cost_price ?? $p->price ?? 0]));

let rowIndex = 0;

function buildProductOptions() {
    return PRODUCTS.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('');
}

function buildItemRow(idx) {
    return `<tr class="item-row">
        <td>
            <select name="items[${idx}][product_id]" class="item-input" onchange="onProductChange(this)">
                <option value="">— Select Product —</option>${buildProductOptions()}
            </select>
        </td>
        <td><input type="number" name="items[${idx}][quantity]" class="item-input item-qty" min="1" value="1" oninput="recalcRow(this.closest('tr'));recalcTotal()"></td>
        <td><input type="number" name="items[${idx}][unit_price]" class="item-input item-price" step="0.01" min="0" placeholder="0.00" oninput="recalcRow(this.closest('tr'));recalcTotal()"></td>
        <td><span class="item-subtotal" style="font-weight:700;color:#03624C;font-size:13px;">$0.00</span></td>
        <td><button type="button" class="btn-remove-row" onclick="removeItemRow(this)"><i class="bi bi-x"></i></button></td>
    </tr>`;
}

function addItemRow(item = null) {
    document.getElementById('itemsTableBody').insertAdjacentHTML('beforeend', buildItemRow(rowIndex++));
    if (!item) return;
    const row = document.querySelector('#itemsTableBody .item-row:last-child');
    const select = row.querySelector('select');
    const priceInput = row.querySelector('.item-price');
    const qtyInput = row.querySelector('.item-qty');

    if (select && item.product_id) select.value = item.product_id;
    if (priceInput && item.unit_price !== undefined) priceInput.value = parseFloat(item.unit_price).toFixed(2);
    if (qtyInput && item.quantity !== undefined) qtyInput.value = item.quantity;
    recalcRow(row);
    recalcTotal();
}

function removeItemRow(btn) {
    if (document.querySelectorAll('#itemsTableBody .item-row').length <= 1) return;
    btn.closest('tr').remove();
    recalcTotal();
}

function onProductChange(sel) {
    const row  = sel.closest('tr');
    const cost = parseFloat(sel.options[sel.selectedIndex]?.dataset?.price ?? 0);
    const pi   = row.querySelector('.item-price');
    if (pi && cost > 0) pi.value = cost.toFixed(2);
    recalcRow(row);
    recalcTotal();
}

function recalcRow(row) {
    const qty   = parseFloat(row.querySelector('.item-qty')?.value)   || 0;
    const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
    row.querySelector('.item-subtotal').textContent = '$' + (qty * price).toFixed(2);
}

function recalcTotal() {
    let total = 0;
    document.querySelectorAll('#itemsTableBody .item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.item-qty')?.value)   || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        total += qty * price;
        row.querySelector('.item-subtotal').textContent = '$' + (qty * price).toFixed(2);
    });
    const shipping = parseFloat(document.getElementById('cShippingCost')?.value) || 0;
    document.getElementById('orderTotal').textContent = '$' + (total + shipping).toFixed(2);
}

function resetPurchaseForm() {
    const form = document.getElementById('purchaseForm');
    form.reset();
    document.getElementById('purchaseErrors').innerHTML = '';
    document.getElementById('purchaseErrors').classList.add('d-none');
    document.getElementById('orderTotal').textContent = '$0.00';
    document.getElementById('cOrderDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('purchaseForm').action = '{{ route('supplier-purchases.store') }}';
    document.getElementById('purchaseMethod').value = 'POST';
    document.getElementById('purchaseModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Create New Purchase Order';
    document.getElementById('purchaseSubmitBtn').innerHTML = '<i class="bi bi-check-lg"></i> Create Purchase';
    rowIndex = 0;
    document.getElementById('itemsTableBody').innerHTML = buildItemRow(rowIndex++);
}

function cleanupModals() {
    ['purchaseModal','detailModal','deleteModal','historyModal'].forEach(id => {
        const m = bootstrap.Modal.getInstance(document.getElementById(id));
        if (m) m.dispose();
    });
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

function openCreateModal() {
    cleanupModals();
    resetPurchaseForm();
    new bootstrap.Modal(document.getElementById('purchaseModal'), {backdrop:true,keyboard:true,focus:true}).show();
}

function openEditModal(purchaseId) {
    cleanupModals();
    resetPurchaseForm();
    document.getElementById('purchaseModalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Purchase Order';
    document.getElementById('purchaseSubmitBtn').innerHTML = '<i class="bi bi-save me-2"></i>Update Purchase';
    document.getElementById('purchaseForm').action = `/supplier-purchases/${purchaseId}`;
    document.getElementById('purchaseMethod').value = 'PUT';

    fetch(`/supplier-purchases/${purchaseId}/edit`, {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
    .then(r => r.json())
    .then(response => {
        const data = response.purchase;
        document.getElementById('supplierSelect').value = data.supplier_id;
        document.getElementById('warehouseSelect').value = data.warehouse_id;
        document.getElementById('cOrderDate').value = data.order_date;
        document.getElementById('cDeliveryDate').value = data.expected_delivery_date ?? '';
        document.getElementById('cShippingCost').value = data.shipping_cost ?? 0;
        document.querySelector('select[name="payment_terms"]').value = data.payment_terms ?? '';
        document.querySelector('textarea[name="notes"]').value = data.notes ?? '';
        document.getElementById('itemsTableBody').innerHTML = '';
        rowIndex = 0;
        if (Array.isArray(data.items) && data.items.length) {
            data.items.forEach(item => addItemRow(item));
        } else {
            addItemRow();
        }
        recalcTotal();
        new bootstrap.Modal(document.getElementById('purchaseModal'), {backdrop:true,keyboard:true,focus:true}).show();
    })
    .catch(() => {
        alert('Unable to load purchase for editing.');
    });
}

document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('purchaseSubmitBtn');
    const orig = btn.innerHTML;
    const errBox = document.getElementById('purchaseErrors');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
    errBox.innerHTML = '';
    errBox.classList.add('d-none');

    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
    .then(r => { if (r.redirected) { window.location.href = r.url; return null; } return r.json(); })
    .then(data => {
        if (!data) return;
        if (data.success || data.id) { window.location.reload(); return; }
        if (data.errors) {
            const list = Object.values(data.errors).flat().map(e=>`<li>${e}</li>`).join('');
            errBox.innerHTML = `<div class="alert alert-danger"><strong>Please fix:</strong><ul class="mb-0 mt-1">${list}</ul></div>`;
            errBox.classList.remove('d-none');
        } else if (data.message) {
            errBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            errBox.classList.remove('d-none');
        }
        btn.disabled = false; btn.innerHTML = orig;
    })
    .catch(() => {
        errBox.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        errBox.classList.remove('d-none');
        btn.disabled = false; btn.innerHTML = orig;
    });
});

function openDetailModal(purchaseId) {
    cleanupModals();
    document.getElementById('detailModalBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status"></div>
            <p class="mt-2 text-muted" style="font-size:13px;">Loading details…</p>
        </div>`;
    new bootstrap.Modal(document.getElementById('detailModal'), {backdrop:true,keyboard:true,focus:true}).show();

    fetch(`/supplier-purchases/${purchaseId}`, {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
    .then(r => r.json())
    .then(data => {
        const sc = {pending:'status-pending',completed:'status-completed'}[data.status] ?? 'status-cancelled';
        const si = {pending:'bi-clock',completed:'bi-check-circle'}[data.status] ?? 'bi-x-circle';
        const ptLabel = {net_30:'Net 30',net_60:'Net 60',net_90:'Net 90',immediate:'Immediate',cod:'Cash on Delivery'}[data.payment_terms] ?? data.payment_terms ?? null;

        let itemRows = data.items?.length
            ? data.items.map(item => `<tr>
                <td style="font-weight:600;color:#1a2e28;">${item.product?.name ?? '—'}</td>
                <td style="text-align:center;">${item.quantity}</td>
                <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                <td style="font-weight:700;color:#03624C;">$${(item.quantity * item.unit_price).toFixed(2)}</td>
                <td style="text-align:center;"><button class="btn-ico ico-history" type="button" onclick="openHistoryModal(${item.product_id})" title="View Product History"><i class="bi bi-clock-history"></i></button></td>
              </tr>`).join('')
            : `<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">No items found.</td></tr>`;

        document.getElementById('detailModalLabel').innerHTML = `<i class="bi bi-receipt me-2"></i>${data.po_number}`;
        document.getElementById('detailModalBody').innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-section" style="margin-bottom:0;height:100%;">
                        <div class="form-section-title"><i class="bi bi-info-circle"></i> Order Info</div>
                        <div class="detail-row"><span class="detail-label">PO Number</span><span class="detail-value"><span class="po-tag">${data.po_number}</span></span></div>
                        <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value"><span class="status-badge ${sc}"><i class="bi ${si}"></i> ${data.status.charAt(0).toUpperCase()+data.status.slice(1)}</span></span></div>
                        <div class="detail-row"><span class="detail-label">Order Date</span><span class="detail-value">${data.order_date_formatted ?? data.order_date}</span></div>
                        ${data.expected_delivery_date ? `<div class="detail-row"><span class="detail-label">Expected Delivery</span><span class="detail-value">${data.expected_delivery_date_formatted ?? data.expected_delivery_date}</span></div>` : ''}
                        ${ptLabel ? `<div class="detail-row"><span class="detail-label">Payment Terms</span><span class="detail-value">${ptLabel}</span></div>` : ''}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-section" style="margin-bottom:0;height:100%;">
                        <div class="form-section-title"><i class="bi bi-person"></i> Supplier</div>
                        <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value">${data.supplier?.name ?? '—'}</span></div>
                        ${data.supplier?.email ? `<div class="detail-row"><span class="detail-label">Email</span><span class="detail-value">${data.supplier.email}</span></div>` : ''}
                        ${data.supplier?.phone ? `<div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">${data.supplier.phone}</span></div>` : ''}
                        ${data.warehouse?.name ? `<div class="detail-row"><span class="detail-label">Warehouse</span><span class="detail-value">${data.warehouse.name}</span></div>` : ''}
                    </div>
                </div>
            </div>
            <div class="form-section" style="margin-top:16px;">
                <div class="form-section-title"><i class="bi bi-list-ul"></i> Order Items</div>
                <div class="table-responsive">
                    <table class="detail-items-table">
                        <thead><tr><th>Product</th><th style="text-align:center;">Qty</th><th>Unit Price</th><th>Subtotal</th><th>History</th></tr></thead>
                        <tbody>${itemRows}</tbody>
                    </table>
                </div>
                ${data.shipping_cost && parseFloat(data.shipping_cost) > 0 ? `<div class="detail-row" style="margin-top:8px;"><span class="detail-label">Shipping Cost</span><span class="detail-value">$${parseFloat(data.shipping_cost).toFixed(2)}</span></div>` : ''}
                <div class="total-row"><span>Total Amount</span><strong>$${parseFloat(data.total_amount).toFixed(2)}</strong></div>
            </div>
            ${data.notes ? `<div class="form-section" style="margin-top:0;"><div class="form-section-title"><i class="bi bi-chat-text"></i> Notes</div><p style="font-size:13px;color:#4a7c6f;margin:0;">${data.notes}</p></div>` : ''}
        `;
    })
    .catch(() => {
        document.getElementById('detailModalBody').innerHTML =
            '<div class="alert alert-danger m-0">Failed to load purchase details. Please try again.</div>';
    });
}

function openHistoryModal(productId) {
    cleanupModals();
    document.getElementById('historyLoading').classList.remove('d-none');
    document.getElementById('historyContent').classList.add('d-none');
    document.getElementById('historyTableBody').innerHTML = '';

    new bootstrap.Modal(document.getElementById('historyModal'), {backdrop:true,keyboard:true,focus:true}).show();

    fetch(`/supplier-purchases/product-history/${productId}`, {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    })
    .then(r => r.json())
    .then(data => {
        const rows = (data.history || []).map(tx => `
            <tr>
                <td>${tx.date ?? '—'}</td>
                <td>${tx.type ?? '—'}</td>
                <td>${tx.change ?? 0}</td>
                <td>${tx.warehouse ?? '—'}</td>
                <td>${tx.user ?? '—'}</td>
                <td>${tx.notes ?? '-'}</td>
            </tr>
        `).join('') || '<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:20px;">No history available.</td></tr>';

        document.getElementById('historyTableBody').innerHTML = rows;
        document.getElementById('historyLoading').classList.add('d-none');
        document.getElementById('historyContent').classList.remove('d-none');
    })
    .catch(() => {
        document.getElementById('historyLoading').innerHTML = '<div class="alert alert-danger m-0">Unable to load history.</div>';
    });
}

function openDeleteModal(purchaseId, poNumber) {
    cleanupModals();
    document.getElementById('deletePONumber').textContent = poNumber;
    document.getElementById('deleteForm').action = `/supplier-purchases/${purchaseId}`;
    new bootstrap.Modal(document.getElementById('deleteModal'), {backdrop:true,keyboard:true,focus:true}).show();
}

document.addEventListener('DOMContentLoaded', function() {
    ['purchaseModal','detailModal','deleteModal','historyModal'].forEach(id => {
        document.getElementById(id).addEventListener('hidden.bs.modal', cleanupModals);
    });
});
</script>
@endpush