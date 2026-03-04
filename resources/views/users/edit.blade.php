@extends('layouts.app')
@section('title', 'Edit User')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Edit User</h3>
        <small class="text-muted">Update user information</small>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="custom-card">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Enter your full name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" placeholder="Leave blank to keep current">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Minimum 8 characters</small>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" 
                           id="password_confirmation" name="password_confirmation">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="user_type" class="form-label">User Role *</label>
                    <select class="form-select @error('user_type') is-invalid @enderror" 
                            id="user_type" name="user_type" required>
                        <option value="">Select Role</option>
                        <option value="admin" {{ old('user_type', $user->user_type) == 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="staff" {{ old('user_type', $user->user_type) == 'staff' ? 'selected' : '' }}>Staff Member</option>
                    </select>
                    @error('user_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">Assign Warehouses</label>
                    <div class="border rounded p-3">
                        @if($warehouses->count() > 0)
                            <div class="row">
                                @foreach($warehouses as $warehouse)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="warehouses[]" value="{{ $warehouse->id }}" 
                                               id="warehouse_{{ $warehouse->id }}"
                                               {{ in_array($warehouse->id, old('warehouses', $userWarehouses)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="warehouse_{{ $warehouse->id }}">
                                            {{ $warehouse->name }}
                                            @if($warehouse->is_default)
                                                <span class="badge bg-success ms-1">Default</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3 text-muted">
                                <i class="bi bi-building fs-4"></i>
                                <p class="mb-0">No warehouses available</p>
                            </div>
                        @endif
                    </div>
                    <small class="text-muted">Select warehouses this user can access</small>
                </div>
            </div>
            
            <div class="col-12">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" 
                           id="is_active" name="is_active" value="1" 
                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active Account
                    </label>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Update User
            </button>
        </div>
    </form>
</div>

@endsection