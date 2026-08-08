@extends('admin.layouts.app')

@section('title', 'User Management')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">User Management</h1>
    <p class="text-muted">Manage all users in the system</p>
</div>

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
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
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
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $users->count() }} of {{ $users->total() }} users</p>
            {{ $users->links() }}
        </div>
    </div>
</div>

@endsection
