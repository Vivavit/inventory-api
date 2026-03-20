@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')

<style>
    .warehouse-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding: 24px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,.04);
    }

    .warehouse-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .warehouse-card {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 24px;
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
    }

    .warehouse-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0,0,0,.12);
    }

    .warehouse-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .warehouse-card-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--green);
        margin: 0;
    }

    .warehouse-card-code {
        font-size: 12px;
        color: #999;
        margin: 4px 0 0;
    }

    .warehouse-card-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin: 16px 0;
        flex-grow: 1;
    }

    .warehouse-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .warehouse-info-row:last-child {
        border-bottom: none;
    }

    .warehouse-info-label {
        color: #999;
    }

    .warehouse-info-value {
        font-weight: 600;
        color: #333;
    }

    .warehouse-card-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f0f0f0;
    }

    .warehouse-card-actions .btn {
        flex: 1;
    }

    .btn-outline-primary {
        color: var(--green);
        border-color: var(--green);
    }

    .btn-outline-primary:hover {
        background-color: var(--green);
        border-color: var(--green);
    }

    .btn-outline-danger {
        color: #ff6b6b;
        border-color: #ff6b6b;
    }

    .btn-outline-danger:hover {
        background-color: #ff6b6b;
        color: white;
        border-color: #ff6b6b;
    }
</style>

<!-- Page Header -->
<div class="warehouse-header">
    <div>
        <h1 class="page-title">Warehouses</h1>
        <p style="color: #999; margin: 0; font-size: 13px;">Manage and organize your storage locations</p>
    </div>
    @can('manage-warehouses')
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-lg"></i> Add Warehouse
    </a>
    @endcan
</div>

@if($warehouses->count() > 0)
<!-- Warehouses Grid -->
<div class="warehouse-grid">
    @foreach($warehouses as $warehouse)
    <div class="warehouse-card">
        <div class="warehouse-card-header">
            <div>
                <h3 class="warehouse-card-title">{{ $warehouse->name }}</h3>
                <p class="warehouse-card-code">{{ $warehouse->code }}</p>
            </div>
            <span class="badge {{ $warehouse->is_active ? 'badge-success' : 'badge-danger' }}">
                {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="warehouse-card-info">
            <div class="warehouse-info-row">
                <span class="warehouse-info-label">
                    <i class="bi bi-tags"></i> Type
                </span>
                <span class="warehouse-info-value">{{ ucfirst($warehouse->type) }}</span>
            </div>

            <div class="warehouse-info-row">
                <span class="warehouse-info-label">
                    <i class="bi bi-people"></i> Staff
                </span>
                <span class="warehouse-info-value">{{ $warehouse->users->count() }} member{{ $warehouse->users->count() !== 1 ? 's' : '' }}</span>
            </div>

            <div class="warehouse-info-row">
                <span class="warehouse-info-label">
                    <i class="bi bi-box"></i> Stock Items
                </span>
                <span class="warehouse-info-value">{{ $warehouse->inventoryLocations->count() }}</span>
            </div>
        </div>

        <div class="warehouse-card-actions">
            <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-eye"></i> View
            </a>
            @can('manage-warehouses')
                <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" 
                      onsubmit="return confirm('Delete this warehouse?');" style="display: inline-block; flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger w-100" type="submit">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endcan
        </div>
    </div>
    @endforeach
</div>
@else
<!-- Empty State -->
<div class="custom-card" style="text-align: center; padding: 48px 24px;">
    <i class="bi bi-building" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 12px;"></i>
    <h4 style="color: #333; margin: 0 0 8px;">No Warehouses Found</h4>
    <p style="color: #999; margin: 0 0 16px;">Create your first warehouse to start managing inventory locations.</p>
    @can('manage-warehouses')
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Create Warehouse
    </a>
    @endcan
</div>
@endif

@endsection