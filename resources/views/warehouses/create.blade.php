@extends('layouts.app')

@section('title', 'Add Warehouse')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Create New Warehouse</h1>
            <p class="text-muted small">Add a new storage location</p>
        </div>
        <a href="{{ route('warehouses.index') }}" class="btn btn-light btn-sm border">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form action="{{ route('warehouses.store') }}" method="POST">
        @csrf
        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="custom-card">
                    <h6 class="section-title"><i class="bi bi-building me-2"></i>Warehouse Details</h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Warehouse Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="WH-001" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <h6 class="section-title"><i class="bi bi-person-lines-fill me-2"></i>Contact Info</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Type</label>
                            <select name="type" class="form-select">
                                <option value="main">Main Warehouse</option>
                                <option value="branch">Branch</option>
                                <option value="store">Store</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="custom-card">
                    <h6 class="section-title"><i class="bi bi-sliders me-2"></i>Settings</h6>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1">
                        <label class="form-check-label small fw-medium">Default Warehouse</label>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                        <label class="form-check-label small fw-medium">Active</label>
                    </div>

                    <button type="submit"
                        class="btn btn-primary w-100 fw-bold"
                        style="background: var(--primary-color); border: none;">
                        <i class="bi bi-plus-lg me-1"></i> Create Warehouse
                    </button>
                </div>

                <div class="alert alert-light border shadow-sm">
                    <h6 class="small fw-bold"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li>Warehouse code must be unique</li>
                        <li>Only one default warehouse allowed</li>
                        <li>You can assign staff later</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
