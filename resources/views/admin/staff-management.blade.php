@extends('admin.layouts.app')

@section('title', 'Staff Management')

@section('content')
@php
    $staffMembers = collect();
    if (isset($staffList) && is_iterable($staffList)) {
        $staffMembers = collect($staffList);
    } elseif (isset($staff) && is_iterable($staff)) {
        $staffMembers = collect($staff);
    } elseif (isset($staff) && is_object($staff)) {
        $staffMembers = collect([$staff]);
    }
@endphp
<div class="page-header mb-4">
    <h1 class="page-title">Staff Management</h1>
    <p class="text-muted">Manage your staff members</p>
</div>

@if(isset($createMode) || isset($editMode) || isset($showMode))
    <div id="admin-detail-content">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0" id="admin-detail-title">
                @if(isset($createMode)) Add Staff Member @endif
                @if(isset($editMode)) Edit Staff Member @endif
                @if(isset($showMode)) Staff Details @endif
            </h5>
        </div>
        <div class="card-body">
            @if(isset($createMode) || isset($editMode))
                <form method="POST" action="{{ isset($editMode) ? route('admin.staff.update', $staff) : route('admin.staff.store') }}">
                    @csrf
                    @if(isset($editMode))
                        @method('PUT')
                    @endif
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', optional($staff)->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', optional($staff)->email) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', optional($staff)->phone) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', optional($staff)->city) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Select branch</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', optional($staff)->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Staff ID</label>
                            <input type="text" name="staff_id" class="form-control" value="{{ old('staff_id', optional($staff)->staff_id) }}">
                            <div class="form-text">Unique ID such as STF-M001.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Capabilities</label>
                            <input type="text" name="capabilities" class="form-control" value="{{ old('capabilities', is_array(optional($staff)->capabilities) ? implode(', ', $staff->capabilities) : (optional($staff)->capabilities ?? '')) }}">
                            <div class="form-text">Comma-separated list of capabilities.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current Duty</label>
                            <input type="text" name="current_duty" class="form-control" value="{{ old('current_duty', optional($staff)->current_duty) }}">
                        </div>
                        @if(isset($createMode))
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $staff->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ old('status', $staff->status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ isset($editMode) ? 'Update Staff' : 'Create Staff' }}</button>
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary ms-2">Back to list</a>
                </form>
            @elseif(isset($showMode))
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Name</strong>
                        <div>{{ $staff->name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email</strong>
                        <div>{{ $staff->email }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone</strong>
                        <div>{{ $staff->phone }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Branch</strong>
                        <div>{{ $staff->branch?->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Staff ID</strong>
                        <div>{{ $staff->staff_id ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status</strong>
                        <div>{{ ucfirst($staff->status) }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Current Duty</strong>
                        <div>{{ $staff->current_duty ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Capabilities</strong>
                        <div>{{ is_array($staff->capabilities) ? implode(', ', $staff->capabilities) : ($staff->capabilities ?? 'N/A') }}</div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Assigned Appointments</h6>
                    @if($assignedTasks->isEmpty())
                        <div class="alert alert-info mb-0">No assigned appointments.</div>
                    @else
                        <ul class="list-group">
                            @foreach($assignedTasks as $task)
                                <li class="list-group-item">Appointment #{{ $task->id }} — {{ ucfirst($task->service_type) }} — {{ optional($task->booking_date)->format('M d, Y') }} {{ $task->time_slot }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-3">{{ $assignedTasks->links() }}</div>
                    @endif
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-warning">Edit Staff</a>
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Back to list</a>
                </div>
            @endif
        </div>
    </div>
    </div>
@endif

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
                        @if(! is_object($member))
                            @continue
                        @endif
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
