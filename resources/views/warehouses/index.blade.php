@extends('layouts.app')

@section('title', 'Warehouses')

@push('styles')
    @vite(['resources/css/features/warehouses.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1>Warehouses</h1>
        <p>Manage and organize your storage locations</p>
    </div>
    @can('manage-warehouses')
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Warehouse
    </a>
    @endcan
</div>

@if($warehouses->count() > 0)
    <div class="warehouse-grid">
        @foreach($warehouses as $warehouse)
            <div class="warehouse-card">
                <div class="warehouse-card-header">
                    <div>
                        <h3 class="warehouse-card-title">{{ $warehouse->name }}</h3>
                        <span class="warehouse-card-code">{{ $warehouse->code }}</span>
                    </div>
                    <span class="status-badge {{ $warehouse->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="warehouse-card-body">
                    <div class="warehouse-stat">
                        <span class="warehouse-stat-label">
                            <i class="bi bi-box-seam"></i> Products
                        </span>
                        <span class="warehouse-stat-value">{{ $warehouse->inventoryLocations->count() }}</span>
                    </div>

                    <div class="warehouse-stat">
                        <span class="warehouse-stat-label">
                            <i class="bi bi-people"></i> Staff
                        </span>
                        <span class="warehouse-stat-value">{{ $warehouse->users->count() }} member{{ $warehouse->users->count() !== 1 ? 's' : '' }}</span>
                    </div>

                    <div class="warehouse-stat">
                        <span class="warehouse-stat-label">
                            <i class="bi bi-box-arrow-up"></i> Total Stock
                        </span>
                        <span class="warehouse-stat-value">{{ $warehouse->inventoryLocations->sum('quantity') }}</span>
                    </div>

                    <div class="warehouse-stat">
                        <span class="warehouse-stat-label">
                            <i class="bi bi-cash"></i> Capacity
                        </span>
                        <span class="warehouse-stat-value">{{ $warehouse->capacity ? number_format($warehouse->capacity) . ' sq.ft.' : 'N/A' }}</span>
                    </div>
                </div>

                <div class="warehouse-card-footer">
                    <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-eye"></i> View
                    </a>
                    @can('manage-warehouses')
                        <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-outline btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" data-warehouse-name="{{ $warehouse->name }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <i class="bi bi-building"></i>
        <h4>No Warehouses Found</h4>
        <p>Create your first warehouse to start managing inventory locations.</p>
        @can('manage-warehouses')
        <div class="mt-4">
            <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Create Warehouse
            </a>
        </div>
        @endcan
    </div>
@endif

@endsection

@push('scripts')
    @vite(['resources/js/features/warehouses.js'])
@endpush
