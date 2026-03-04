@extends('layouts.app')

@section('title', 'Edit Warehouse: ' . $warehouse->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Edit Warehouse</h1>
            <p class="text-muted small">{{ $warehouse->code }}</p>
        </div>
        <a href="{{ route('warehouses.index') }}" class="btn btn-light btn-sm border">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    {{-- ✅ FIXED: Changed to warehouses.update --}}
    <form action="{{ route('warehouses.update', $warehouse) }}" method="POST">
        @csrf
        @method('PUT') {{-- ✅ ADDED THIS --}}

        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="custom-card">
                    <h6 class="section-title">
                        <i class="bi bi-building me-2"></i>Warehouse Details
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Warehouse Name *</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $warehouse->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Code *</label>
                            <input type="text" name="code" class="form-control"
                                value="{{ old('code', $warehouse->code) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $warehouse->address) }}</textarea>
                        </div>
                        
                        {{-- ✅ ADDED Capacity field --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Capacity (sq. ft.)</label>
                            <input type="number" name="capacity" class="form-control" 
                                value="{{ old('capacity', $warehouse->capacity) }}" min="0" step="0.01">
                        </div>
                    </div>

                    <h6 class="section-title">
                        <i class="bi bi-person-lines-fill me-2"></i>Contact Info
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Type</label>
                            <select name="type" class="form-select">
                                <option value="main" {{ old('type', $warehouse->type) == 'main' ? 'selected' : '' }}>Main Warehouse</option>
                                <option value="branch" {{ old('type', $warehouse->type) == 'branch' ? 'selected' : '' }}>Branch</option>
                                <option value="store" {{ old('type', $warehouse->type) == 'store' ? 'selected' : '' }}>Store</option>
                                <option value="virtual" {{ old('type', $warehouse->type) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control"
                                value="{{ old('contact_person', $warehouse->contact_person) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $warehouse->phone) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $warehouse->email) }}">
                        </div>
                    </div>
                </div>

                {{-- Staff Assignment --}}
                <div class="custom-card">
                    <h6 class="section-title">
                        <i class="bi bi-people me-2"></i>Assigned Staff
                    </h6>

                    <div class="row g-2">
                        @foreach($users as $user)
                            <div class="col-md-6">
                                <label class="staff-card d-flex align-items-center gap-3 p-3 border rounded-3 w-100">
                                    <input
                                        type="checkbox"
                                        name="assigned_users[]"
                                        value="{{ $user->id }}"
                                        class="form-check-input mt-0"
                                        {{ in_array($user->id, $assignedUserIds) ? 'checked' : '' }}
                                    >

                                    <div>
                                        <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->email }}</div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <small class="text-muted d-block mt-2">
                        Select staff members who can access this warehouse
                    </small>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="custom-card">
                    <h6 class="section-title">
                        <i class="bi bi-sliders me-2"></i>Settings
                    </h6>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1"
                            {{ $warehouse->is_default ? 'checked' : '' }}>
                        <label class="form-check-label small fw-medium">Default Warehouse</label>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                            {{ $warehouse->is_active ? 'checked' : '' }}>
                        <label class="form-check-label small fw-medium">Active</label>
                    </div>

                    <button type="submit"
                        class="btn btn-primary w-100 fw-bold"
                        style="border: none;">
                        <i class="bi bi-check-lg me-1"></i> Update Warehouse
                    </button>
                </div>
                
                {{-- Quick Actions --}}
                <div class="custom-card mt-4">
                    <h6 class="section-title">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                    
                    <div class="d-grid gap-2">
                        @if(!$warehouse->is_default)
                        <form action="{{ route('warehouses.set-default', $warehouse) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-star me-1"></i> Set as Default
                            </button>
                        </form>
                        @endif
                        
                        <form action="{{ route('warehouses.toggle-status', $warehouse) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-{{ $warehouse->is_active ? 'warning' : 'success' }} w-100">
                                <i class="bi bi-power me-1"></i> {{ $warehouse->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        
                        <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-outline-info w-100">
                            <i class="bi bi-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
                
                {{-- Warehouse Stats --}}
                <div class="custom-card mt-4">
                    <h6 class="section-title">
                        <i class="bi bi-graph-up me-2"></i>Quick Stats
                    </h6>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center p-2 border rounded">
                                <div class="h5 mb-0">{{ $warehouse->inventoryLocations->count() }}</div>
                                <small class="text-muted">Products</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 border rounded">
                                <div class="h5 mb-0">{{ $warehouse->users->count() }}</div>
                                <small class="text-muted">Staff</small>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="text-center p-2 border rounded">
                                @php
                                    $totalStock = $warehouse->inventoryLocations->sum('quantity');
                                @endphp
                                <div class="h5 mb-0">{{ $totalStock }}</div>
                                <small class="text-muted">Total Stock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .staff-card {
        cursor: pointer;
        transition: 0.2s ease;
    }
    .staff-card:hover {
        border-color: var(--primary-color);
        background: #f8fffd;
    }
    .staff-card:has(input:checked) {
        border-color: var(--primary-color);
        background: #eefaf6;
    }
    .section-title {
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #03624C;
        border-bottom: 2px solid #eef2f1;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
</style>

@endsection