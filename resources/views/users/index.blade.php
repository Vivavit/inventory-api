@extends('layouts.app')
@section('title', 'Users')

@section('content')

<style>
    .user-header {
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        padding: 24px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,.04);
    }

    .user-avatar-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--green), var(--teal));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
    }

    .btn-group .btn {
        border-radius: 6px;
        margin-right: 4px;
    }

    .btn-outline-primary {
        color: var(--green);
        border-color: var(--green);
    }

    .btn-outline-primary:hover {
        background-color: var(--green);
        border-color: var(--green);
    }
</style>

<!-- Page Header -->
<div>
<div class="user-header">
    <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 24px;">
    <div>
        <h1 class="page-title">User Management</h1>
        <p style="color: #999; margin: 0; font-size: 13px;">Manage system users and permissions</p>
    </div>
    
    @can('manage-users')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-lg">
        <i class="bi bi-plus-lg"></i> Add User
    </a>
    @endcan
</div>

<!-- Users Table -->
<div >
    
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Warehouses</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th style="width: 140px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="user-avatar-badge">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #333;">{{ $user->name }}</div>
                                <small style="color: #999;">{{ $user->phone ?? '—' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <small style="color: #666;">{{ $user->email }}</small>
                    </td>
                    <td>
                        @if($user->isAdmin())
                            <span class="badge badge-danger">Admin</span>
                        @else
                            <span class="badge badge-success">Staff</span>
                        @endif
                    </td>
                    <td>
                        @if($user->warehouses->count() > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @foreach($user->warehouses->take(2) as $warehouse)
                                    <span class="badge badge-success" style="font-size: 11px;">{{ $warehouse->name }}</span>
                                @endforeach
                                @if($user->warehouses->count() > 2)
                                    <span class="badge" style="background: #f0f0f0; color: #999; font-size: 11px;">+{{ $user->warehouses->count() - 2 }} more</span>
                                @endif
                            </div>
                        @else
                            <small style="color: #999;">No warehouses</small>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <small style="color: #999;">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                        </small>
                    </td>
                    <td style="text-align: right;">
                        <div class="btn-group" role="group">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-secondary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            @can('manage-users')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.toggle-status', $user) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-secondary" 
                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                        @if($user->is_active)
                                            <i class="bi bi-person-x" style="color: #ff6b6b;"></i>
                                        @else
                                            <i class="bi bi-person-check" style="color: #2ecc71;"></i>
                                        @endif
                                    </button>
                                </form>
                                
                                <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                      onsubmit="return confirm('Delete this user?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Delete">
                                        <i class="bi bi-trash" style="color: #ff6b6b;"></i>
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
    
    <div style="display: flex; justify-content: center; margin-top: 24px;">

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