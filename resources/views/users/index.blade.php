@extends('layouts.app')

@section('title', 'Users')

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

    .btn-add-user {
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

    .btn-add-user:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .users-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #E9FFFA;
        overflow: hidden;
        animation: fadeInUp 0.6s ease 0.2s backwards;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table thead {
        background: #03624C;
        color: white;
    }

    .users-table th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .users-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #E9FFFA;
        vertical-align: middle;
    }

    .users-table tbody tr {
        transition: all 0.2s ease;
    }

    .users-table tbody tr:hover {
        background: #f8fafc;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #03624C, #0fb9b1);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-name {
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        font-size: 15px;
    }

    .user-phone {
        color: #64748b;
        font-size: 12px;
        margin: 0;
    }

    .badge-role-admin {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-role-staff {
        background: rgba(52, 199, 89, 0.15);
        color: #34C759;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-status-active {
        background: rgba(52, 199, 89, 0.15);
        color: #34C759;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-status-inactive {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .warehouse-tag {
        padding: 4px 8px;
        border-radius: 8px;
        background: #E9FFFA;
        color: #03624C;
        font-size: 10px;
        font-weight: 600;
        margin-right: 4px;
        margin-bottom: 4px;
        display: inline-block;
    }

    .last-login {
        font-size: 12px;
        color: #64748b;
    }

    .action-buttons {
        display: flex;
        gap: 6px;
        justify-content: flex-end;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-size: 16px;
    }

    .btn-view {
        background: #03624C;
        color: white;
    }

    .btn-edit {
        background: #0fb9b1;
        color: white;
    }

    .btn-toggle {
        background: white;
        color: #64748b;
        border: 2px solid #e5e7eb;
    }

    .btn-toggle:hover {
        border-color: #03624C;
        color: #03624C;
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
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.5;
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
        .users-table thead {
            display: none;
        }

        .users-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid #E9FFFA;
            border-radius: 12px;
            padding: 16px;
        }

        .users-table td {
            display: block;
            padding: 8px 0;
            border: none;
            position: relative;
            padding-left: 40%;
        }

        .users-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
            position: absolute;
            left: 0;
            top: 8px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .action-buttons {
            justify-content: center;
            margin-top: 12px;
        }

        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 20px;
        }

        .btn-add-user {
            width: 100%;
            justify-content: center;
            margin-top: 12px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1>User Management</h1>
        <p>Manage system users and permissions</p>
    </div>
    @can('manage-users')
    <a href="{{ route('users.create') }}" class="btn-add-user">
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
                                            <span class="badge" style="background: #f1f5f9; color: #999; font-size: 10px;">+{{ $user->warehouses->count() - 2 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <small style="color: #999;">None assigned</small>
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
                                    <a href="{{ route('users.show', $user) }}" class="btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('manage-users')
                                        <a href="{{ route('users.edit', $user) }}" class="btn-action btn-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.toggle-status', $user) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-action btn-toggle" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi bi-{{ $user->is_active ? 'person-x' : 'person-check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete user {{ addslashes($user->name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete" title="Delete">
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

        <div class="pagination-custom">
            {{ $users->links() }}
        </div>

    @else
        <div class="empty-state">
            <i class="bi bi-people"></i>
            <h4>No Users Found</h4>
            <p>Add users to your system and assign them to warehouses.</p>
            @can('manage-users')
            <a href="{{ route('users.create') }}" class="btn-add-user" style="display: inline-flex; margin-top: 12px;">
                <i class="bi bi-plus"></i> Add User
            </a>
            @endcan
        </div>
    @endif
</div>

@endsection
