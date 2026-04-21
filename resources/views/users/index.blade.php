@extends('layouts.app')

@section('title', 'Users')

@push('styles')
    @vite(['resources/css/features/users.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1>User Management</h1>
        <p>Manage system users and permissions</p>
    </div>
    @can('manage-users')
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add User
    </a>
    @endcan
</div>

<div class="users-card">
    @if($users->count() > 0)
        <div class="table-responsive">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Warehouses</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td data-label="User">
                                <div class="user-info">
                                    <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                    <div>
                                        <p class="user-name">{{ $user->name }}</p>
                                        <small class="user-phone">{{ $user->phone ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Email">
                                <small>{{ $user->email }}</small>
                            </td>
                            <td data-label="Role">
                                @if($user->isAdmin())
                                    <span class="badge-role-admin">Admin</span>
                                @else
                                    <span class="badge-role-staff">Staff</span>
                                @endif
                            </td>
                            <td data-label="Warehouses">
                                @if($user->warehouses->count() > 0)
                                    <div class="d-flex flex-wrap">
                                        @foreach($user->warehouses->take(2) as $warehouse)
                                            <span class="warehouse-tag">{{ $warehouse->name }}</span>
                                        @endforeach
                                        @if($user->warehouses->count() > 2)
                                            <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary); border: 1px solid var(--border-color); font-size: 10px;">+{{ $user->warehouses->count() - 2 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <small style="color: var(--text-secondary);">None assigned</small>
                                @endif
                            </td>
                            <td data-label="Status">
                                @if($user->is_active)
                                    <span class="badge-status-active">Active</span>
                                @else
                                    <span class="badge-status-inactive">Inactive</span>
                                @endif
                            </td>
                            <td data-label="Last Login">
                                <span class="last-login">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                            </td>
                            <td style="text-align: right;" data-label="Actions">
                                <div class="action-buttons">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-primary btn-sm btn-icon" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('manage-users')
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline btn-sm btn-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.toggle-status', $user) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline btn-sm btn-icon" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi bi-{{ $user->is_active ? 'person-x' : 'person-check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;" data-user-name="{{ $user->name }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
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

        <div class="pagination-custom m-4">
            {{ $users->links() }}
        </div>

    @else
        <div class="empty-state">
            <i class="bi bi-people"></i>
            <h4>No Users Found</h4>
            <p>Add users to your system and assign them to warehouses.</p>
            @can('manage-users')
            <div class="mt-4">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add User
                </a>
            </div>
            @endcan
        </div>
    @endif
</div>

@endsection

@push('scripts')
    @vite(['resources/js/features/users.js'])
@endpush
