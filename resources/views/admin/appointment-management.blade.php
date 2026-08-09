@extends('admin.layouts.app')

@section('title', 'Appointments')

@section('content')
@php
    $appointmentList = isset($appointments) ? $appointments : collect();
    $displayCount = count($appointmentList);
    if (method_exists($appointmentList, 'total')) {
        $totalCount = $appointmentList->total();
        $showPagination = true;
    } else {
        $totalCount = $displayCount;
        $showPagination = false;
    }
@endphp

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
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
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
                        <th>Return Time</th>
                        <th>Assigned Staff</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointmentList as $appointment)
                        @php
                            $dateSource = $appointment->booking_date ?? $appointment->preferred_at;
                            $statusColors = [
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'assigned' => 'primary',
                                'scheduled' => 'secondary',
                                'in_progress' => 'dark',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                            $statusOptions = ['pending', 'confirmed', 'assigned', 'scheduled', 'in_progress', 'completed', 'cancelled'];
                        @endphp
                        <tr>
                            <td>#{{ $appointment->id }}</td>
                            <td>
                                {{ $appointment->user->name ?? 'N/A' }}
                                @if($appointment->user)
                                    <div class="text-muted small">ID: {{ $appointment->user->id }}</div>
                                @endif
                            </td>
                            <td>{{ ucfirst($appointment->service_type) }}</td>
                            <td>
                                {{ optional($dateSource)->format('M d, Y') ?? 'N/A' }}
                                @if($appointment->time_slot)
                                    <div class="text-muted small">{{ $appointment->time_slot }}</div>
                                @elseif($appointment->booking_date === null && $appointment->preferred_at)
                                    <div class="text-muted small">{{ optional($appointment->preferred_at)->format('h:i A') }}</div>
                                @endif
                            </td>
                            <td>
                                @if($appointment->staff)
                                    {{ $appointment->staff->name }}
                                    <div class="text-muted small">Staff ID: {{ $appointment->staff->id }}</div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-outline-primary" title="View appointment details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(! empty($appointment->user->phone))
                                        <a href="tel:{{ $appointment->user->phone }}" class="btn btn-outline-secondary" title="Call customer">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary disabled" title="Phone number unavailable">
                                            <i class="fas fa-phone"></i>
                                        </button>
                                    @endif
                                    @if($appointment->status === 'pending')
                                        <form method="POST" action="{{ route('admin.appointments.update-status', $appointment->id) }}" class="m-0">
                                            @csrf
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="btn btn-outline-success" title="Confirm appointment">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" class="btn btn-outline-info" title="Schedule visit" onclick="openScheduleModal({{ $appointment->id }}, '{{ optional($appointment->booking_date ?? $appointment->preferred_at)->format('Y-m-d') }}', '{{ $appointment->time_slot ?? optional($appointment->preferred_at)->format('H:i') }}')">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" title="Assign staff" onclick="openAssignModal({{ $appointment->id }}, '{{ $appointment->assigned_staff_id ?? '' }}', {!! json_encode($appointment->staff->name ?? '') !!})">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Change status">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @foreach($statusOptions as $statusOption)
                                                @if($statusOption !== $appointment->status)
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.appointments.update-status', $appointment->id) }}" class="m-0">
                                                            @csrf
                                                            <input type="hidden" name="status" value="{{ $statusOption }}">
                                                            <button type="submit" class="dropdown-item">{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</button>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                    <form method="POST" action="{{ route('admin.appointments.destroy', $appointment) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger admin-action-delete" data-confirm="Delete this appointment?" data-confirm-title="Delete appointment" data-confirm-button-text="Delete" title="Delete appointment">
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
                                    No appointments available yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($showPagination)
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $displayCount }} of {{ $totalCount }} appointments</p>
            {{ $appointmentList->links() }}
        </div>
        @else
        <div class="mt-4">
            <p class="text-muted mb-0">Showing {{ $displayCount }} appointment(s)</p>
        </div>
        @endif
    </div>
</div>

<!-- Schedule Visit Modal -->
<div class="modal fade" id="scheduleVisitModal" tabindex="-1" aria-labelledby="scheduleVisitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleVisitModalLabel">Schedule Visit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="scheduleVisitForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="_method" value="POST">
                    <div class="mb-3">
                        <label for="scheduleBookingDate" class="form-label">Visit Date</label>
                        <input type="date" id="scheduleBookingDate" name="booking_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleTimeSlot" class="form-label">Visit Time</label>
                        <input type="time" id="scheduleTimeSlot" name="time_slot" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Staff Modal -->
<div class="modal fade" id="assignStaffModal" tabindex="-1" aria-labelledby="assignStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignStaffModalLabel">Assign Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignStaffForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="staffSearchQuery" class="form-label">Search staff</label>
                        <input type="search" id="staffSearchQuery" class="form-control" placeholder="Search staff by name, email, or phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Matching staff</label>
                        <div id="staffSearchResults" class="list-group"></div>
                    </div>
                    <input type="hidden" id="staffIdField" name="staff_id" value="">
                    <div id="selectedStaffInfo" class="small text-muted"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="assignStaffSubmitButton" disabled>Assign staff</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const scheduleVisitModal = new bootstrap.Modal(document.getElementById('scheduleVisitModal'));
    const assignStaffModal = new bootstrap.Modal(document.getElementById('assignStaffModal'));

    function openScheduleModal(appointmentId, bookingDate, timeSlot) {
        const form = document.getElementById('scheduleVisitForm');
        form.action = `/admin/appointments/${appointmentId}/reschedule`;
        document.getElementById('scheduleBookingDate').value = bookingDate || '';
        document.getElementById('scheduleTimeSlot').value = timeSlot || '';
        scheduleVisitModal.show();
    }

    function openAssignModal(appointmentId, staffId, staffName) {
        const form = document.getElementById('assignStaffForm');
        form.action = `/admin/appointments/${appointmentId}/assign-staff`;
        document.getElementById('staffIdField').value = staffId || '';
        document.getElementById('selectedStaffInfo').innerText = staffId ? `Current staff: ${staffName} (${staffId})` : 'No staff selected yet.';
        document.getElementById('staffSearchQuery').value = '';
        document.getElementById('staffSearchResults').innerHTML = '';
        document.getElementById('assignStaffSubmitButton').disabled = !staffId;
        assignStaffModal.show();
    }

    let staffSearchTimeout = null;
    document.getElementById('staffSearchQuery').addEventListener('input', function () {
        clearTimeout(staffSearchTimeout);
        const query = this.value.trim();
        staffSearchTimeout = setTimeout(() => searchStaff(query), 300);
    });

    function searchStaff(query) {
        const appointmentId = document.getElementById('assignStaffForm').action.match(/appointments\/(\d+)\/assign-staff$/)?.[1];
        if (!appointmentId) return;

        fetch(`/admin/appointments/search-staff?appointment_id=${appointmentId}&q=${encodeURIComponent(query)}`, {
            headers: {
                'Accept': 'application/json'
            }
        }).then(response => response.json()).then(data => {
            const results = document.getElementById('staffSearchResults');
            results.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                results.innerHTML = '<div class="list-group-item">No staff found.</div>';
                return;
            }

            data.forEach(staff => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `<strong>${staff.name}</strong><br><small>ID: ${staff.id} · ${staff.phone ?? 'No phone'} · ${staff.city ?? 'No city'}</small>`;
                item.addEventListener('click', () => {
                    document.getElementById('staffIdField').value = staff.id;
                    document.getElementById('selectedStaffInfo').innerText = `Selected: ${staff.name} (${staff.id})`;
                    document.getElementById('assignStaffSubmitButton').disabled = false;
                });
                results.appendChild(item);
            });
        }).catch(() => {
            document.getElementById('staffSearchResults').innerHTML = '<div class="list-group-item text-danger">Unable to load staff.</div>';
        });
    }
</script>
@endpush

@endsection
