@extends('layouts.app')
@section('title', $user->name)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">{{ $user->name }}</h3>
        <small class="text-muted">User Details</small>
    </div>
    <div class="btn-group">
        <a href="{{ route('users.index') }}" class="btn btn-light">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        @can('manage-users')
        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @endcan
    </div>
</div>

<div class="row">
    <!-- User Info Card -->
    <div class="col-lg-4 mb-4">
        <div class="custom-card">
            <div class="text-center mb-4">
                <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 24px;">
                    {{ $user->initials() }}
                </div>
                <h5 class="mt-3 mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-0">{{ $user->email }}</p>
                <div class="mt-2">
                    @if($user->isAdmin())
                        <span class="badge bg-danger">Administrator</span>
                    @else
                        <span class="badge bg-info">Staff Member</span>
                    @endif
                    
                    @if($user->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between">
                    <span>Phone</span>
                    <span>{{ $user->phone ?: 'N/A' }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span>Account Type</span>
                    <span class="text-capitalize">{{ $user->user_type }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span>Created</span>
                    <span>{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span>Last Login</span>
                    <span>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
        </div>
        
        <!-- Assigned Warehouses -->
        <div class="custom-card mt-4">
            <h6 class="section-title">Assigned Warehouses</h6>
            @if($user->warehouses->count() > 0)
                <div class="d-flex flex-wrap gap-2">
                    @foreach($user->warehouses as $warehouse)
                    <div class="border rounded p-2 flex-grow-1">
                        <div class="fw-semibold">{{ $warehouse->name }}</div>
                        <small class="text-muted">{{ $warehouse->code }}</small>
                        @if($warehouse->pivot->is_default)
                            <div class="badge bg-success mt-1">Default</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-building fs-4"></i>
                    <p class="mb-0">No warehouses assigned</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-lg-8">
        <div class="custom-card">
            <h6 class="section-title">Recent Activity</h6>
            
            @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction</th>
                                <th>Product</th>
                                <th>Warehouse</th>
                                <th>Quantity</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                <td>#{{ $transaction->id }}</td>
                                <td>{{ $transaction->product->name }}</td>
                                <td>{{ $transaction->warehouse->name }}</td>
                                <td>{{ $transaction->quantity }}</td>
                                <td>
                                    @if($transaction->type === 'in')
                                        <span class="badge bg-success">Stock In</span>
                                    @else
                                        <span class="badge bg-danger">Stock Out</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-3">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No recent activity</p>
                </div>
            @endif
        </div>
        
        <!-- Permissions Summary -->
        <div class="custom-card mt-4">
            <h6 class="section-title">Permissions Summary</h6>
            <div class="row">
                @if($user->isAdmin())
                <div class="col-12">
                    <div class="alert alert-success">
                        <i class="bi bi-shield-check me-2"></i>
                        <strong>Administrator Access</strong>
                        <p class="mb-0 small">This user has full access to all system features and settings.</p>
                    </div>
                </div>
                @else
                <div class="col-md-6">
                    <h6 class="small fw-bold text-muted mb-2">Allowed Actions</h6>
                    <ul class="list-unstyled">
                        <li class="mb-1"><i class="bi bi-check-circle text-success me-2"></i>View Products</li>
                        <li class="mb-1"><i class="bi bi-check-circle text-success me-2"></i>View Warehouses</li>
                        <li class="mb-1"><i class="bi bi-check-circle text-success me-2"></i>View Analytics</li>
                        <li class="mb-1"><i class="bi bi-check-circle text-success me-2"></i>Checkout Items</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="small fw-bold text-muted mb-2">Restricted Actions</h6>
                    <ul class="list-unstyled">
                        <li class="mb-1"><i class="bi bi-x-circle text-danger me-2"></i>Manage Users</li>
                        <li class="mb-1"><i class="bi bi-x-circle text-danger me-2"></i>Create/Edit Products</li>
                        <li class="mb-1"><i class="bi bi-x-circle text-danger me-2"></i>Manage Warehouses</li>
                        <li class="mb-1"><i class="bi bi-x-circle text-danger me-2"></i>Delete Records</li>
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.avatar {
    font-weight: 600;
}
</style>

@endsection