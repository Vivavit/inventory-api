@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')

<style>
    /* ── Reset & base ──────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }

    /* ── CSS tokens ────────────────────────────────── */
    :root {
        --green:        #0F6E56;
        --green-mid:    #1D9E75;
        --green-light:  #E1F5EE;
        --green-text:   #085041;
        --green-border: #9FE1CB;
        --radius-sm:    6px;
        --radius-md:    8px;
        --radius-lg:    12px;
        --radius-xl:    16px;
        --shadow-sm:    0 1px 3px rgba(0,0,0,.07);
        --shadow-md:    0 4px 12px rgba(0,0,0,.08);
        --transition:   150ms ease;
    }

    /* ── Page layout ───────────────────────────────── */
    .po-page { padding: 28px 0 60px; }

    /* ── Top bar ───────────────────────────────────── */
    .po-topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .po-title { font-size: 22px; font-weight: 700; color: #111827; line-height: 1.2; margin: 0 0 4px; }
    .po-subtitle { font-size: 13px; color: #6b7280; margin: 0; }

    /* ── Primary button ────────────────────────────── */
    .btn-po-primary {
        background: var(--green-mid);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: var(--radius-md);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
        transition: background var(--transition);
        white-space: nowrap;
    }
    .btn-po-primary:hover { background: var(--green); color: #fff; }
    .btn-po-primary:active { transform: scale(.98); }

    /* ── Ghost / outline button ────────────────────── */
    .btn-po-ghost {
        background: transparent;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 7px 13px;
        border-radius: var(--radius-md);
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all var(--transition);
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-po-ghost:hover { background: #f9fafb; border-color: #9ca3af; color: #111827; }
    .btn-po-ghost.danger { color: #dc2626; border-color: #fca5a5; }
    .btn-po-ghost.danger:hover { background: #fef2f2; }

    /* ── Receive button ────────────────────────────── */
    .btn-receive {
        background: var(--green-light);
        color: var(--green-text);
        border: 1px solid var(--green-border);
        padding: 6px 12px;
        border-radius: var(--radius-md);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all var(--transition);
    }
    .btn-receive:hover { background: #9FE1CB; }

    /* ── Alert ─────────────────────────────────────── */
    .po-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: var(--radius-md);
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 20px;
        border: 1px solid transparent;
        position: relative;
    }
    .po-alert.success { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .po-alert.error   { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
    .po-alert-close {
        margin-left: auto;
        background: none;
        border: none;
        cursor: pointer;
        color: inherit;
        opacity: .6;
        font-size: 16px;
        line-height: 1;
        padding: 0 2px;
    }
    .po-alert-close:hover { opacity: 1; }

    /* ── Stats row ─────────────────────────────────── */
    .po-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }
    @media (max-width: 640px) { .po-stats { grid-template-columns: repeat(2, 1fr); } }
    .po-stat {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: var(--radius-lg);
        padding: 16px;
    }
    .po-stat-label { font-size: 12px; color: #9ca3af; font-weight: 500; margin-bottom: 6px; }
    .po-stat-value { font-size: 24px; font-weight: 700; color: #111827; line-height: 1; }
    .po-stat-value.green { color: var(--green-mid); }

    /* ── Tab bar ───────────────────────────────────── */
    .po-tabs {
        display: flex;
        gap: 0;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 16px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .po-tab {
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        border: none;
        background: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        white-space: nowrap;
        transition: color var(--transition), border-color var(--transition);
        text-decoration: none;
        display: inline-block;
    }
    .po-tab:hover { color: #111827; }
    .po-tab.active { color: var(--green-mid); border-bottom-color: var(--green-mid); }

    /* ── Filter row ────────────────────────────────── */
    .po-filters {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 20px;
    }
    .po-filter-label { font-size: 12px; color: #9ca3af; }
    .po-search {
        margin-left: auto;
        position: relative;
        min-width: 200px;
    }
    .po-search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }
    .po-search input {
        width: 100%;
        padding: 7px 12px 7px 32px;
        border: 1px solid #e5e7eb;
        border-radius: var(--radius-md);
        font-size: 13px;
        outline: none;
        background: #fff;
        transition: border-color var(--transition);
    }
    .po-search input:focus { border-color: var(--green-mid); box-shadow: 0 0 0 3px rgba(29,158,117,.1); }

    /* ── Orders grid ───────────────────────────────── */
    .po-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
    }
    @media (max-width: 480px) { .po-grid { grid-template-columns: 1fr; } }

    /* ── Order card ────────────────────────────────── */
    .po-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: var(--radius-xl);
        overflow: hidden;
        transition: border-color var(--transition), box-shadow var(--transition);
        display: flex;
        flex-direction: column;
    }
    .po-card:hover { border-color: var(--green-border); box-shadow: var(--shadow-md); }

    .po-card-head {
        padding: 16px 18px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }
    .po-number { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 2px; }
    .po-supplier { font-size: 12px; color: #6b7280; }

    /* status badges */
    .po-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .badge-draft            { background: #eff6ff; color: #1d4ed8; }
    .badge-pending          { background: #fffbeb; color: #b45309; }
    .badge-ordered          { background: #f0fdf4; color: #15803d; }
    .badge-partially_received { background: #faf5ff; color: #7e22ce; }
    .badge-received         { background: var(--green-light); color: var(--green-text); }
    .badge-cancelled        { background: #fef2f2; color: #b91c1c; }

    .po-card-body { padding: 16px 18px; flex: 1; }
    .po-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 10px; }
    .po-meta-label { font-size: 11px; color: #9ca3af; margin-bottom: 2px; }
    .po-meta-value { font-size: 13px; font-weight: 600; color: #111827; }
    .po-meta-value.amount { color: var(--green-mid); font-size: 15px; }
    .po-meta-value.muted  { color: #9ca3af; text-decoration: line-through; }

    .po-card-foot {
        padding: 12px 18px;
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .po-creator { font-size: 11px; color: #9ca3af; }
    .po-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }

    /* status select */
    .po-status-select {
        font-size: 11px;
        padding: 5px 8px;
        border: 1px solid #e5e7eb;
        border-radius: var(--radius-md);
        background: #fff;
        color: #374151;
        cursor: pointer;
        outline: none;
        transition: border-color var(--transition);
    }
    .po-status-select:focus { border-color: var(--green-mid); }

    /* ── Empty state ───────────────────────────────── */
    .po-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
    }
    .po-empty-icon {
        width: 64px; height: 64px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
        font-size: 28px;
    }
    .po-empty h4 { font-size: 16px; font-weight: 700; color: #374151; margin-bottom: 8px; }
    .po-empty p  { font-size: 13px; color: #9ca3af; margin-bottom: 20px; }

    /* ── Pagination ────────────────────────────────── */
    .po-pagination { display: flex; justify-content: center; margin-top: 28px; }
    .po-pagination .pagination { display: flex; gap: 4px; list-style: none; padding: 0; margin: 0; }
    .po-pagination .page-item .page-link {
        width: 34px; height: 34px;
        display: flex; align-items: center; justify-content: center;
        border-radius: var(--radius-md);
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        font-size: 13px;
        text-decoration: none;
        transition: all var(--transition);
    }
    .po-pagination .page-item.active .page-link { background: var(--green-mid); color: #fff; border-color: var(--green-mid); }
    .po-pagination .page-item .page-link:hover:not(.active) { background: #f3f4f6; border-color: #9ca3af; }
    .po-pagination .page-item.disabled .page-link { opacity: .4; pointer-events: none; }

    /* ── Modal ─────────────────────────────────────── */
    .po-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.45);
        z-index: 1040;
        align-items: flex-start;
        justify-content: center;
        padding: 48px 16px 32px;
        overflow-y: auto;
    }
    .po-modal-backdrop.open { display: flex; }
    .po-modal {
        background: #fff;
        border-radius: var(--radius-xl);
        width: 100%;
        max-width: 680px;
        box-shadow: 0 20px 60px rgba(0,0,0,.18);
        animation: modalIn .18s ease;
        flex-shrink: 0;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(-12px) scale(.98); }
        to   { opacity: 1; transform: none; }
    }
    .po-modal-hd {
        padding: 20px 24px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .po-modal-title { font-size: 16px; font-weight: 700; color: #111827; margin: 0; }
    .po-modal-close {
        background: none; border: none; cursor: pointer;
        color: #9ca3af; font-size: 20px; line-height: 1;
        padding: 4px 6px; border-radius: var(--radius-sm);
        transition: color var(--transition), background var(--transition);
    }
    .po-modal-close:hover { color: #374151; background: #f3f4f6; }

    .po-modal-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

    .po-modal-ft {
        padding: 16px 24px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* ── Form elements ─────────────────────────────── */
    .po-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 520px) { .po-form-row { grid-template-columns: 1fr; } }

    .po-field { display: flex; flex-direction: column; gap: 5px; }
    .po-field label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }
    .po-field label .req { color: #ef4444; }

    .po-field input,
    .po-field select,
    .po-field textarea {
        padding: 9px 12px;
        border: 1px solid #e5e7eb;
        border-radius: var(--radius-md);
        font-size: 13px;
        color: #111827;
        background: #fff;
        outline: none;
        transition: border-color var(--transition), box-shadow var(--transition);
        font-family: inherit;
        width: 100%;
    }
    .po-field input:focus,
    .po-field select:focus,
    .po-field textarea:focus {
        border-color: var(--green-mid);
        box-shadow: 0 0 0 3px rgba(29,158,117,.12);
    }
    .po-field input.is-invalid,
    .po-field select.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239,68,68,.1);
    }
    .po-field .po-error { font-size: 11px; color: #ef4444; margin-top: 2px; }

    @if($errors->any())
    /* show errors on load */
    @endif

    /* ── Items section ─────────────────────────────── */
    .po-items-label {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 10px;
        border-bottom: 1px solid #f3f4f6;
    }
    .po-item-row {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: var(--radius-md);
        padding: 12px;
        display: grid;
        grid-template-columns: 2fr 80px 100px 90px 36px;
        gap: 10px;
        align-items: end;
        margin-bottom: 8px;
    }
    @media (max-width: 560px) {
        .po-item-row { grid-template-columns: 1fr 1fr; }
        .po-item-row .po-field:last-of-type,
        .po-item-row .po-item-remove { grid-column: span 2; }
    }
    .po-item-remove {
        height: 36px;
        width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #fecaca;
        border-radius: var(--radius-md);
        color: #dc2626;
        cursor: pointer;
        transition: all var(--transition);
        flex-shrink: 0;
    }
    .po-item-remove:hover { background: #fef2f2; }
    .po-item-total input { background: #f3f4f6 !important; color: #6b7280 !important; }

    .po-add-item {
        font-size: 12px;
        font-weight: 600;
        color: var(--green-mid);
        background: var(--green-light);
        border: 1px dashed var(--green-border);
        border-radius: var(--radius-md);
        padding: 9px;
        width: 100%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background var(--transition);
    }
    .po-add-item:hover { background: #9FE1CB; }

    /* ── Confirm delete modal ──────────────────────── */
    .po-confirm-body { padding: 24px; text-align: center; }
    .po-confirm-icon {
        width: 52px; height: 52px;
        border-radius: 50%;
        background: #fef2f2;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
        font-size: 22px;
    }
    .po-confirm-body h5 { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 8px; }
    .po-confirm-body p  { font-size: 13px; color: #6b7280; margin-bottom: 0; }
</style>

<div class="container-fluid po-page">

    {{-- ── Top bar ── --}}
    <div class="po-topbar">
        <div>
            <h1 class="po-title">Purchase orders</h1>
            <p class="po-subtitle">Manage supplier orders and track inventory receipts</p>
        </div>
        <button class="btn-po-primary" onclick="openCreateModal()">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            New purchase order
        </button>
    </div>

    {{-- ── Flash messages ── --}}
    @if(session('success'))
        <div class="po-alert success" id="poAlertSuccess">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ session('success') }}
            <button class="po-alert-close" onclick="this.closest('.po-alert').remove()">×</button>
        </div>
    @endif
    @if(session('error'))
        <div class="po-alert error" id="poAlertError">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3M8 11v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('error') }}
            <button class="po-alert-close" onclick="this.closest('.po-alert').remove()">×</button>
        </div>
    @endif

    {{-- ── Stats ── --}}
    <div class="po-stats">
        <div class="po-stat">
            <div class="po-stat-label">Total orders</div>
            <div class="po-stat-value">{{ $stats['total'] ?? $orders->total() }}</div>
        </div>
        <div class="po-stat">
            <div class="po-stat-label">Pending</div>
            <div class="po-stat-value">{{ $stats['pending'] ?? 0 }}</div>
        </div>
        <div class="po-stat">
            <div class="po-stat-label">In transit</div>
            <div class="po-stat-value">{{ $stats['ordered'] ?? 0 }}</div>
        </div>
        <div class="po-stat">
            <div class="po-stat-label">Total value</div>
            <div class="po-stat-value green">${{ number_format($stats['total_value'] ?? 0, 0) }}</div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <div class="po-tabs">
        @php
            $tabs = ['all' => 'All orders', 'draft' => 'Draft', 'pending' => 'Pending', 'ordered' => 'Ordered', 'partially_received' => 'Partial', 'received' => 'Received', 'cancelled' => 'Cancelled'];
            $activeTab = request('status', 'all');
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['status' => $key === 'all' ? null : $key])) }}"
               class="po-tab {{ $activeTab === $key || ($key === 'all' && !request('status')) ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ── Filters & search ── --}}
    <div class="po-filters">
        <span class="po-filter-label">Supplier:</span>
        <a href="{{ route('purchase-orders.index', array_merge(request()->except('supplier_id'), ['supplier_id' => null])) }}"
           class="btn-po-ghost {{ !request('supplier_id') ? 'active' : '' }}" style="{{ !request('supplier_id') ? 'background:var(--green-light);color:var(--green-text);border-color:var(--green-border)' : '' }}">
           All
        </a>
        @foreach($suppliers ?? [] as $supplier)
            <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['supplier_id' => $supplier->id])) }}"
               class="btn-po-ghost {{ request('supplier_id') == $supplier->id ? 'active' : '' }}"
               style="{{ request('supplier_id') == $supplier->id ? 'background:var(--green-light);color:var(--green-text);border-color:var(--green-border)' : '' }}">
                {{ $supplier->name }}
            </a>
        @endforeach
        <div class="po-search">
            <svg class="po-search-icon" width="14" height="14" viewBox="0 0 14 14" fill="none">
                <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.3"/>
                <path d="M9.5 9.5L12 12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
            </svg>
            <form method="GET" action="{{ route('purchase-orders.index') }}" id="searchForm">
                @foreach(request()->except('search','page') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders…"
                       onchange="document.getElementById('searchForm').submit()">
            </form>
        </div>
    </div>

    {{-- ── Orders grid ── --}}
    <div class="po-grid">
        @forelse($orders as $order)
        <div class="po-card">
            {{-- head --}}
            <div class="po-card-head">
                <div>
                    <div class="po-number">{{ $order->po_number }}</div>
                    <div class="po-supplier">{{ $order->supplier->name }}</div>
                </div>
                <span class="po-badge badge-{{ $order->status }}">
                    {{ ucwords(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>

            {{-- body --}}
            <div class="po-card-body">
                <div class="po-meta-grid">
                    <div>
                        <div class="po-meta-label">Order date</div>
                        <div class="po-meta-value">{{ $order->order_date->format('M d, Y') }}</div>
                    </div>
                    <div>
                        <div class="po-meta-label">Warehouse</div>
                        <div class="po-meta-value">{{ $order->warehouse->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="po-meta-label">Items</div>
                        <div class="po-meta-value">{{ $order->items->count() }}</div>
                    </div>
                    <div>
                        <div class="po-meta-label">Total</div>
                        <div class="po-meta-value amount">${{ number_format($order->total_amount, 2) }}</div>
                    </div>
                    @if($order->expected_delivery_date)
                    <div style="grid-column: span 2">
                        <div class="po-meta-label">Expected delivery</div>
                        <div class="po-meta-value">{{ $order->expected_delivery_date->format('M d, Y') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- footer --}}
            <div class="po-card-foot">
                <div class="po-creator">{{ $order->creator->name ?? 'Unknown' }}</div>
                <div class="po-actions">
                    {{-- status change for draft/pending --}}
                    @if(in_array($order->status, ['draft', 'pending']))
                        <select class="po-status-select"
                                onchange="changeStatus({{ $order->id }}, this.value, this)">
                            <option value="">Change status</option>
                            @if($order->status === 'draft')
                                <option value="pending">Mark as pending</option>
                                <option value="ordered">Mark as ordered</option>
                            @elseif($order->status === 'pending')
                                <option value="ordered">Mark as ordered</option>
                                <option value="cancelled">Cancel order</option>
                            @endif
                        </select>
                    @endif

                    {{-- receive button --}}
                    @if(in_array($order->status, ['ordered', 'partially_received']))
                        @php
                            $itemsData = $order->items->map(function($item) {
                                return [
                                    'id' => $item->id,
                                    'product_name' => $item->product->name . ($item->product->sku ? ' (' . $item->product->sku . ')' : ''),
                                    'quantity' => $item->quantity,
                                    'received_quantity' => $item->received_quantity,
                                    'remaining' => $item->quantity - $item->received_quantity,
                                ];
                            });
                        @endphp
                        <button class="btn-receive"
                                data-order-id="{{ $order->id }}"
                                data-warehouse-id="{{ $order->warehouse_id }}"
                                data-po-number="{{ $order->po_number }}"
                                data-items='@json($itemsData)'
                                onclick="openReceiveModal(this)">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $order->status === 'partially_received' ? 'Receive rest' : 'Receive' }}
                        </button>
                    @endif

                    {{-- view --}}
                    <a href="{{ route('purchase-orders.show', $order->id) }}" class="btn-po-ghost">View</a>

                    {{-- edit (draft/pending only) --}}
                    @if(in_array($order->status, ['draft', 'pending']))
                        <a href="{{ route('purchase-orders.edit', $order->id) }}" class="btn-po-ghost">Edit</a>
                    @endif

                    {{-- delete (draft only) --}}
                    @if($order->status === 'draft')
                        <button class="btn-po-ghost danger"
                                onclick="confirmDelete({{ $order->id }}, '{{ $order->po_number }}')"
                                title="Delete order">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 3h8M5 3V2h2v1M4 3v7h4V3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="po-empty">
            <div class="po-empty-icon">📋</div>
            <h4>No purchase orders yet</h4>
            <p>Start by creating your first purchase order from a supplier.</p>
            <button class="btn-po-primary" onclick="openCreateModal()">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Create first order
            </button>
        </div>
        @endforelse
    </div>

    {{-- ── Pagination ── --}}
    @if($orders->hasPages())
    <div class="po-pagination">
        {{ $orders->appends(request()->query())->links() }}
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════
     CREATE PURCHASE ORDER MODAL
     ════════════════════════════════════════════════════ --}}
<div class="po-modal-backdrop" id="poModalBackdrop" onclick="handleBackdropClick(event)">
    <div class="po-modal" id="poModal">
        <div class="po-modal-hd">
            <h5 class="po-modal-title" id="poModalTitle">New purchase order</h5>
            <button class="po-modal-close" onclick="closeModal()" aria-label="Close">×</button>
        </div>

        <form id="poForm" method="POST" action="{{ route('purchase-orders.store') }}">
            @csrf
            <input type="hidden" name="_method" id="poFormMethod" value="POST">
            <input type="hidden" name="status" id="poFormStatus" value="draft">

            <div class="po-modal-body">

                {{-- Row 1: supplier + warehouse --}}
                <div class="po-form-row">
                    <div class="po-field">
                        <label for="supplier_id">Supplier <span class="req">*</span></label>
                        <select name="supplier_id" id="supplier_id" required>
                            <option value="">Select supplier</option>
                            @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="po-field">
                        <label for="warehouse_id">Warehouse <span class="req">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" required>
                            <option value="">Select warehouse</option>
                            @foreach($warehouses ?? [] as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 2: dates --}}
                <div class="po-form-row">
                    <div class="po-field">
                        <label for="order_date">Order date <span class="req">*</span></label>
                        <input type="date" name="order_date" id="order_date"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="po-field">
                        <label for="expected_delivery_date">Expected delivery</label>
                        <input type="date" name="expected_delivery_date" id="expected_delivery_date">
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <div class="po-items-label">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align:-2px;margin-right:5px"><rect x="1" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M4 7h6M4 4.5h6M4 9.5h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                            Order items
                        </span>
                        <span id="itemsTotalBadge" style="font-size:12px;color:var(--green-mid);font-weight:600">Total: $0.00</span>
                    </div>
                    <div id="itemsContainer" style="margin-top:12px"></div>
                    <button type="button" class="po-add-item" onclick="addItem()">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><path d="M6.5 1v11M1 6.5h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        Add item
                    </button>
                </div>

                {{-- Notes --}}
                <div class="po-field">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Additional notes about this order…"></textarea>
                </div>

            </div>

            <div class="po-modal-ft">
                <button type="button" class="btn-po-ghost" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn-po-ghost"
                        onclick="submitForm('draft')">Save as draft</button>
                <button type="button" class="btn-po-primary"
                        onclick="submitForm('pending')">
                    Create order
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════
     DELETE CONFIRM MODAL
     ════════════════════════════════════════════════════ --}}
<div class="po-modal-backdrop" id="poDeleteBackdrop" onclick="handleDeleteBackdropClick(event)">
    <div class="po-modal" style="max-width:420px">
        <div class="po-modal-hd">
            <h5 class="po-modal-title">Delete order</h5>
            <button class="po-modal-close" onclick="closeDeleteModal()">×</button>
        </div>
        <div class="po-confirm-body">
            <div class="po-confirm-icon">🗑️</div>
            <h5>Are you sure?</h5>
            <p id="deleteConfirmText">This will permanently delete the purchase order.</p>
        </div>
        <div class="po-modal-ft">
            <button class="btn-po-ghost" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-po-ghost danger" style="font-weight:600">Yes, delete</button>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════
     RECEIVE STOCK MODAL
     ════════════════════════════════════════════════════ --}}
<div class="po-modal-backdrop" id="receiveModalBackdrop" onclick="handleReceiveBackdropClick(event)">
    <div class="po-modal" id="receiveModal" style="max-width:620px">
        <div class="po-modal-hd">
            <h5 class="po-modal-title" id="receiveModalTitle">Receive Stock</h5>
            <button class="po-modal-close" onclick="closeReceiveModal()" aria-label="Close">×</button>
        </div>
        <form id="receiveForm" method="POST">
            @csrf
            <input type="hidden" name="warehouse_id" id="receiveWarehouseId">
            <div class="po-modal-body">
                <div class="po-field">
                    <label>Items to Receive</label>
                    <div id="receiveItemsContainer" style="margin-top:8px;"></div>
                </div>
            </div>
            <div class="po-modal-ft">
                <button type="button" class="btn-po-ghost" onclick="closeReceiveModal()">Cancel</button>
                <button type="submit" class="btn-po-primary">Confirm Receipt</button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════
     JAVASCRIPT
     ════════════════════════════════════════════════════ --}}
<script>
/* ── Product data (passed from controller) ── */
const PRODUCTS = @json($products ?? []);
const BASE_URL = '{{ url("purchase-orders") }}';
const CSRF     = '{{ csrf_token() }}';

let itemIndex = 0;

/* ─────────────────────────────────────────────────────
   Modal open / close
───────────────────────────────────────────────────── */
function openCreateModal() {
    document.getElementById('poModalTitle').textContent = 'New purchase order';
    document.getElementById('poForm').action = BASE_URL;
    document.getElementById('poFormMethod').value = 'POST';
    document.getElementById('poForm').reset();
    document.getElementById('order_date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('itemsContainer').innerHTML = '';
    itemIndex = 0;
    addItem();
    updateTotalBadge();
    document.getElementById('poModalBackdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('poModalBackdrop').classList.remove('open');
    document.body.style.overflow = '';
}

function handleBackdropClick(e) {
    if (e.target === document.getElementById('poModalBackdrop')) closeModal();
}

/* ─────────────────────────────────────────────────────
   Form submit with status
───────────────────────────────────────────────────── */
function submitForm(status) {
    const container = document.getElementById('itemsContainer');
    if (container.children.length === 0) {
        alert('Please add at least one item to the order.');
        return;
    }
    document.getElementById('poFormStatus').value = status;
    document.getElementById('poForm').submit();
}

/* ─────────────────────────────────────────────────────
   Order items
───────────────────────────────────────────────────── */
function addItem(productId = '', quantity = 1, unitPrice = '') {
    itemIndex++;
    const idx = itemIndex;

    const opts = PRODUCTS.map(p =>
        `<option value="${p.id}" data-price="${p.cost_price || 0}" ${productId == p.id ? 'selected' : ''}>
            ${escHtml(p.name)} (${escHtml(p.sku)})
        </option>`
    ).join('');

    const row = document.createElement('div');
    row.className = 'po-item-row';
    row.id = `item-row-${idx}`;
    row.innerHTML = `
        <div class="po-field">
            <label>Product <span class="req">*</span></label>
            <select name="items[${idx}][product_id]" required onchange="onProductChange(${idx})">
                <option value="">Select product</option>${opts}
            </select>
        </div>
        <div class="po-field">
            <label>Qty <span class="req">*</span></label>
            <input type="number" name="items[${idx}][quantity]" value="${quantity}"
                   min="1" required onchange="calcItemTotal(${idx})">
        </div>
        <div class="po-field">
            <label>Unit price <span class="req">*</span></label>
            <input type="number" name="items[${idx}][unit_price]" id="price-${idx}"
                   value="${unitPrice}" min="0" step="0.01" required onchange="calcItemTotal(${idx})">
        </div>
        <div class="po-field po-item-total">
            <label>Total</label>
            <input type="number" id="total-${idx}" readonly
                   value="${(parseFloat(quantity||0) * parseFloat(unitPrice||0)).toFixed(2)}">
        </div>
        <button type="button" class="po-item-remove" onclick="removeItem(${idx})" title="Remove item">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M2 3.5h10M6 3.5V2.5h2v1M5 3.5v9h4v-9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>`;

    document.getElementById('itemsContainer').appendChild(row);
}

function removeItem(idx) {
    const row = document.getElementById(`item-row-${idx}`);
    if (row) row.remove();
    updateTotalBadge();
}

function onProductChange(idx) {
    const row    = document.getElementById(`item-row-${idx}`);
    const select = row.querySelector('select');
    const opt    = select.options[select.selectedIndex];
    const price  = parseFloat(opt.dataset.price || 0);
    const priceInput = document.getElementById(`price-${idx}`);
    if (priceInput && (!priceInput.value || parseFloat(priceInput.value) === 0)) {
        priceInput.value = price.toFixed(2);
    }
    calcItemTotal(idx);
}

function calcItemTotal(idx) {
    const row   = document.getElementById(`item-row-${idx}`);
    if (!row) return;
    const qty   = parseFloat(row.querySelector(`input[name*="[quantity]"]`)?.value || 0);
    const price = parseFloat(document.getElementById(`price-${idx}`)?.value || 0);
    const total = document.getElementById(`total-${idx}`);
    if (total) total.value = (qty * price).toFixed(2);
    updateTotalBadge();
}

function updateTotalBadge() {
    let sum = 0;
    document.querySelectorAll('[id^="total-"]').forEach(el => {
        sum += parseFloat(el.value || 0);
    });
    document.getElementById('itemsTotalBadge').textContent = `Total: $${sum.toFixed(2)}`;
}

/* ─────────────────────────────────────────────────────
   Status change (AJAX PATCH)
───────────────────────────────────────────────────── */
function changeStatus(orderId, newStatus, selectEl) {
    if (!newStatus) return;
    if (!confirm(`Change status to "${newStatus.replace('_', ' ')}"?`)) {
        selectEl.value = '';
        return;
    }

    fetch(`${BASE_URL}/${orderId}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ status: newStatus })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Could not update status.'));
            selectEl.value = '';
        }
    })
    .catch(() => {
        alert('Network error. Please try again.');
        selectEl.value = '';
    });
}

/* ─────────────────────────────────────────────────────
   Receive order - legacy for show page
───────────────────────────────────────────────────── */
function receiveOrder(orderId) {
    if (!confirm('Mark this order as received? Inventory stock will be updated.')) return;
    const form = document.getElementById('receiveForm');
    form.action = `${BASE_URL}/${orderId}/receive`;
    form.submit();
}

/* ─────────────────────────────────────────────────────
   Receive modal (index page)
───────────────────────────────────────────────────── */
function openReceiveModal(btn) {
    const orderId = btn.dataset.orderId;
    const warehouseId = btn.dataset.warehouseId;
    const items = JSON.parse(btn.dataset.items);
    const poNumber = btn.dataset.poNumber || '';

    // Set modal title
    document.getElementById('receiveModalTitle').textContent = poNumber ? `Receive Stock - ${poNumber}` : 'Receive Stock';

    // Set form action and warehouse
    const form = document.getElementById('receiveForm');
    form.action = `${BASE_URL}/${orderId}/receive`;
    document.getElementById('receiveWarehouseId').value = warehouseId;

    // Build items list
    const container = document.getElementById('receiveItemsContainer');
    container.innerHTML = '';

    items.forEach(item => {
        const row = document.createElement('div');
        row.className = 'receive-form';
        row.innerHTML = `
            <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-medium">${escHtml(item.product_name)}</span>
                <small class="text-muted">Ordered: ${item.quantity} | Received: ${item.received_quantity}</small>
            </div>
            <div class="row g-2">
                <div class="col-8">
                    <input type="number" name="items[${item.id}][received]" class="form-control" min="0" max="${item.remaining}" placeholder="Quantity to receive" value="${item.remaining}" required>
                </div>
                <div class="col-4">
                    <small class="text-muted">Remaining: ${item.remaining}</small>
                </div>
            </div>
        `;
        container.appendChild(row);
    });

    document.getElementById('receiveModalBackdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeReceiveModal() {
    document.getElementById('receiveModalBackdrop').classList.remove('open');
    document.body.style.overflow = '';
}

function handleReceiveBackdropClick(e) {
    if (e.target === document.getElementById('receiveModalBackdrop')) closeReceiveModal();
}

/* ─────────────────────────────────────────────────────
   Delete order
───────────────────────────────────────────────────── */
function confirmDelete(orderId, poNumber) {
    document.getElementById('deleteConfirmText').textContent =
        `This will permanently delete order "${poNumber}". This action cannot be undone.`;
    document.getElementById('deleteForm').action = `${BASE_URL}/${orderId}`;
    document.getElementById('poDeleteBackdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('poDeleteBackdrop').classList.remove('open');
    document.body.style.overflow = '';
}

function handleDeleteBackdropClick(e) {
    if (e.target === document.getElementById('poDeleteBackdrop')) closeDeleteModal();
}

/* ─────────────────────────────────────────────────────
   Utility
───────────────────────────────────────────────────── */
function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Keyboard: ESC closes modals ── */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal(); closeDeleteModal(); closeReceiveModal(); }
});

/* ── Auto-dismiss alerts after 5 s ── */
['poAlertSuccess','poAlertError'].forEach(id => {
    const el = document.getElementById(id);
    if (el) setTimeout(() => el.remove(), 5000);
});
</script>

@endsection