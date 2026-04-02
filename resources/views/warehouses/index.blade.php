@extends('layouts.app')

@section('title', 'Warehouses')

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

    .btn-add-warehouse {
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
    }

    .btn-add-warehouse:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .warehouse-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .warehouse-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #E9FFFA;
        transition: all 0.3s ease;
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }

    .warehouse-card:nth-child(1) { animation-delay: 0.1s; }
    .warehouse-card:nth-child(2) { animation-delay: 0.2s; }
    .warehouse-card:nth-child(3) { animation-delay: 0.3s; }
    .warehouse-card:nth-child(4) { animation-delay: 0.4s; }
    .warehouse-card:nth-child(5) { animation-delay: 0.5s; }

    .warehouse-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 32px rgba(3, 98, 76, 0.15);
        border-color: #03624C;
    }

    .warehouse-card-header {
        padding: 24px;
        background: linear-gradient(135deg, #f8fafc, #E9FFFA);
        border-bottom: 1px solid #E9FFFA;
    }

    .warehouse-card-title {
        font-size: 18px;
        font-weight: 800;
        color: #03624C;
        margin: 0 0 4px;
    }

    .warehouse-card-code {
        font-family: Monaco, Consolas, monospace;
        background: #03624C;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        display: inline-block;
    }

    .warehouse-card-body {
        padding: 24px;
        flex-grow: 1;
    }

    .warehouse-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #E9FFFA;
    }

    .warehouse-stat:last-child {
        border-bottom: none;
    }

    .warehouse-stat-label {
        color: #64748b;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .warehouse-stat-value {
        font-weight: 700;
        color: #1e293b;
        font-size: 15px;
    }

    .warehouse-card-footer {
        padding: 16px 24px;
        background: #f8fafc;
        border-top: 1px solid #E9FFFA;
        display: flex;
        gap: 8px;
    }

    .btn-warehouse {
        flex: 1;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-view {
        background: #03624C;
        color: white;
    }

    .btn-view:hover {
        background: #024538;
        transform: translateY(-2px);
    }

    .btn-edit {
        background: white;
        color: #03624C;
        border: 2px solid #03624C;
    }

    .btn-edit:hover {
        background: #03624C;
        color: white;
    }

    .btn-delete {
        background: white;
        color: #ef4444;
        border: 2px solid #ef4444;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        border: 1px solid #E9FFFA;
    }

    .empty-state i {
        font-size: 64px;
        color: #cbd5e1;
        margin-bottom: 16px;
    }

    .empty-state h4 {
        color: #1e293b;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #64748b;
        margin-bottom: 20px;
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
        .warehouse-grid {
            grid-template-columns: 1fr;
        }

        .warehouse-card-header {
            padding: 20px;
        }

        .warehouse-card-body {
            padding: 20px;
        }

        .warehouse-card-footer {
            flex-wrap: wrap;
        }

        .btn-warehouse {
            flex: 1 1 auto;
        }

        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 20px;
        }

        .btn-add-warehouse {
            width: 100%;
            justify-content: center;
            margin-top: 12px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1>Warehouses</h1>
        <p>Manage and organize your storage locations</p>
    </div>
    @can('manage-warehouses')
    <a href="{{ route('warehouses.create') }}" class="btn-add-warehouse">
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
                    <span class="badge {{ $warehouse->is_active ? 'badge-success' : 'badge-danger' }}" style="background: {{ $warehouse->is_active ? 'rgba(52, 199, 89, 0.15); color: #34C759;' : 'rgba(239, 68, 68, 0.15); color: #FF3B31;' }}; padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
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
                    <a href="{{ route('warehouses.show', $warehouse) }}" class="btn-warehouse btn-view">
                        <i class="bi bi-eye"></i> View
                    </a>
                    @can('manage-warehouses')
                        <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn-warehouse btn-edit">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Delete warehouse {{ addslashes($warehouse->name) }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-warehouse btn-delete">
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
        <a href="{{ route('warehouses.create') }}" class="btn-add-warehouse" style="display: inline-flex; margin-top: 12px;">
            <i class="bi bi-plus"></i> Create Warehouse
        </a>
        @endcan
    </div>
@endif

@endsection
