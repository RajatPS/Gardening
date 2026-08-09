@extends('admin.layouts.app')

@section('title', 'Staff Management')

@section('content')
@php
    $staffMembers = $staffMembers ?? $staff ?? collect();
@endphp
<div class="page-header mb-4">
    <h1 class="page-title">Staff Management</h1>
    <p class="text-muted">Manage your staff members</p>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Staff List</h5>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i> Add Staff
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.staff.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or email"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Staff Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffMembers as $member)
                        <tr>
                            <td>#{{ $member->id }}</td>
                            <td>
                                <strong>{{ $member->name }}</strong>
                            </td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->phone }}</td>
                            <td>{{ $member->city ?? 'N/A' }}</td>
                            <td>{{ optional($member->created_at)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                @if($member->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.staff.show', $member) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($member->status === 'active')
                                        <form method="POST" action="{{ route('admin.staff.suspend', $member) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.staff.activate', $member) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            data-confirm="Delete this staff member permanently?"
                                            data-confirm-title="Delete staff"
                                            data-confirm-button-text="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    No staff members available yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staffMembers instanceof \Illuminate\Contracts\Pagination\Paginator || $staffMembers instanceof \Illuminate\Contracts\Pagination\CursorPaginator)
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $staffMembers->count() }} of {{ $staffMembers->total() }} staff members</p>
            {{ $staffMembers->links() }}
        </div>
        @else
        <div class="mt-4">
            <p class="text-muted mb-0">Showing {{ $staffMembers->count() }} staff member(s)</p>
        </div>
        @endif
    </div>
</div>

@endsection
