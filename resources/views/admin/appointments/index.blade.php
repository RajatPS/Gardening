@extends('admin.layouts.app')

@section('title', 'Appointments')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Appointments</h1>
    <p class="text-muted">Manage service bookings and appointments</p>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Appointments List</h5>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.appointments.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by customer name"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <select class="form-select" name="service_type">
                        <option value="">All Services</option>
                        <option value="consultation" {{ request('service_type') === 'consultation' ? 'selected' : '' }}>Consultation</option>
                        <option value="maintenance" {{ request('service_type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="installation" {{ request('service_type') === 'installation' ? 'selected' : '' }}>Installation</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Appointments Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Service Type</th>
                        <th>Date & Time</th>
                        <th>Assigned Staff</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>#{{ $appointment->id }}</td>
                            <td>{{ $appointment->user->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($appointment->service_type) }}</td>
                            <td>{{ optional($appointment->booking_date)->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td>{{ $appointment->staff->name ?? 'Unassigned' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-info" onclick="assignStaff({{ $appointment->id }})">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No appointments found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $appointments->count() }} of {{ $appointments->total() }} appointments</p>
            {{ $appointments->links() }}
        </div>
    </div>
</div>

@endsection
