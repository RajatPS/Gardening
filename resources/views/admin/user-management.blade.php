@extends('admin.layouts.app')

@section('title', 'User Management')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">User Management</h1>
    <p class="text-muted">Manage all users in the system</p>
</div>

@php
    $users = $users ?? collect();
@endphp

@if(isset($showMode) || isset($editMode))
    <div id="admin-detail-content">
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0" id="admin-detail-title">
                        @if(isset($editMode)) Edit User @else User Details @endif
                    </h5>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Back to users
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(isset($editMode))
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PIN Code</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $user->pincode ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Select branch</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $user->address ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $user->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ old('status', $user->status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </form>
            @else
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Name</strong>
                        <div>{{ $user->name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email</strong>
                        <div>{{ $user->email }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone</strong>
                        <div>{{ $user->phone }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Branch</strong>
                        <div>{{ $user->branch?->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <strong>Address</strong>
                        <div>{{ $user->address ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status</strong>
                        <div>{{ ucfirst($user->status) }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Users List</h5>
            </div>
            <div class="col-auto">
                <a href="#" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i> Add User
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, email, or phone"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <select class="form-select" name="user_type">
                        <option value="">All Types</option>
                        <option value="free" {{ request('user_type') === 'free' ? 'selected' : '' }}>Free</option>
                        <option value="premium" {{ request('user_type') === 'premium' ? 'selected' : '' }}>Premium</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </div>
        </form>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>User ID</th>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Branch</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>#{{ $user->id }}</td>
                            <td>
                                <img src="https://ui-avatars.com/api/?name={{ $user->name }}" 
                                    alt="{{ $user->name }}" class="rounded-circle" width="30">
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->branch?->name ?? 'N/A' }}</td>
                            <td>{{ optional($user->created_at)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                @if($user->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->status === 'active')
                                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger" 
                                                title="Deactivate User"
                                                data-confirm="Deactivate this user?"
                                                data-confirm-title="Deactivate user"
                                                data-confirm-button-text="Deactivate">
                                                <i class="fas fa-power-off me-1"></i> Deactivate
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Activate User">
                                                <i class="fas fa-power-off me-1"></i> Activate
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger admin-action-delete" 
                                            data-confirm="Delete this user permanently?"
                                            data-confirm-title="Delete user"
                                            data-confirm-button-text="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
            <p class="text-muted mb-0">Showing {{ $users->count() }} of {{ $users->total() }} users</p>
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
