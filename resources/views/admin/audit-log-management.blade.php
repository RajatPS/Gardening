@extends('admin.layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Audit Logs</h1>
    <p class="text-muted">Track all admin and staff activities</p>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Activity Log</h5>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <select class="form-select" name="action_type">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('action_type') === 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action_type') === 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action_type') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <select class="form-select" name="module">
                        <option value="">All Modules</option>
                        <option value="users" {{ request('module') === 'users' ? 'selected' : '' }}>Users</option>
                        <option value="products" {{ request('module') === 'products' ? 'selected' : '' }}>Products</option>
                        <option value="orders" {{ request('module') === 'orders' ? 'selected' : '' }}>Orders</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <select class="form-select" name="user_role">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('user_role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ request('user_role') === 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>
                <div class="col-md-1 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Audit Logs Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Record ID</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td><small>{{ optional($log->created_at)->format('M d, Y H:i') ?? 'N/A' }}</small></td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $log->action_type }}</span>
                            </td>
                            <td>{{ ucfirst($log->module) }}</td>
                            <td>#{{ $log->record_id }}</td>
                            <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                            <td>
                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No audit logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $auditLogs->count() }} of {{ $auditLogs->total() }} logs</p>
            {{ $auditLogs->links() }}
        </div>
    </div>
</div>

@endsection
