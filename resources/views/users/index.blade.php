@extends('layouts.app')
@section('title', 'Users')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">User Management</h3>
        <small class="text-muted">Manage system users and permissions</small>
    </div>
    
    @can('manage-users')
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add New User
    </a>
    @endcan
</div>

<div class="custom-card">
    <div class="section-title">All Users</div>
    
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Warehouses</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                {{ $user->initials() }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->phone }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->isAdmin())
                            <span class="badge bg-danger">Admin</span>
                        @else
                            <span class="badge bg-info">Staff</span>
                        @endif
                    </td>
                    <td>
                        @if($user->warehouses->count() > 0)
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($user->warehouses as $warehouse)
                                    <span class="badge bg-light text-dark small">{{ $warehouse->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">No warehouses</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                        </small>
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-light">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            @can('manage-users')
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-light">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light">
                                    @if($user->is_active)
                                        <i class="bi bi-person-x text-danger"></i>
                                    @else
                                        <i class="bi bi-person-check text-success"></i>
                                    @endif
                                </button>
                            </form>
                            
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="d-flex justify-content-center">
        {{ $users->links() }}
    </div>
</div>

<style>
.avatar {
    font-weight: 600;
    font-size: 14px;
}
</style>

@endsection