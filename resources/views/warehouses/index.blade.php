@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Warehouses</h3>
        <small class="text-muted">Manage storage locations</small>
    </div>
    @can('manage-warehouses')
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Warehouse
    </a>
    @endcan
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
    @foreach($warehouses as $warehouse) {{-- ✅ Changed from $warehouse to $warehouses --}}
    <div class="col">
        <div class="custom-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="fw-bold mb-1">{{ $warehouse->name }}</h6>
                    <small class="text-muted">{{ $warehouse->code }}</small>
                </div>
                <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'danger' }}">
                    {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            
            <div class="mt-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span>Type:</span>
                    <span class="fw-medium">{{ ucfirst($warehouse->type) }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Assigned Staff:</span>
                    <span class="fw-medium">{{ $warehouse->users->count() }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span>Stock Items:</span>
                    <span class="fw-medium">{{ $warehouse->inventoryLocations->count() }}</span>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex gap-2">
                    <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-sm btn-light flex-fill">
                        <i class="bi bi-eye"></i> View
                    </a>
                    @can('manage-warehouses')
                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('Delete this warehouse?');" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" type="submit">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($warehouses->isEmpty())
<div class="text-center py-5">
    <i class="bi bi-building display-1 text-muted"></i>
    <h5 class="mt-3 text-muted">No warehouses found</h5>
    <p class="text-muted">Create your first warehouse to get started</p>
</div>
@endif
@endsection