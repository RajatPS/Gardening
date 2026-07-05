<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - GardenHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #ffffff;
            color: #111827;
            font-family: Inter, Arial, sans-serif;
        }
        .page-shell { padding: 24px; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            margin-bottom: 20px;
        }
        .card-soft {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }
        .stat-value { font-size: 1.5rem; font-weight: 700; }
        .btn-logout { border-radius: 10px; }
        .toggle-row { display: flex; gap: 10px; margin-bottom: 16px; }
        .toggle-btn {
            border: 1px solid #d1d5db;
            background: #f9fafb;
            color: #374151;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 600;
        }
        .toggle-btn.active {
            background: #16a34a;
            color: #fff;
            border-color: #16a34a;
        }
        .hidden-panel { display: none; }
    </style>
</head>
<body>
<div class="page-shell">
    <div class="topbar">
        <div>
            <h2 class="mb-1">Staff Dashboard</h2>
            <p class="text-muted mb-0">Welcome, {{ $staff->name }}</p>
        </div>
        <form method="POST" action="{{ route('staff.logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-logout">Logout</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card-soft p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted">Pending</div>
                        <div class="stat-value">{{ $pendingAppointments }}</div>
                    </div>
                    <i class="fas fa-clock text-warning fs-3"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-soft p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted">Completed</div>
                        <div class="stat-value">{{ $completedAppointments }}</div>
                    </div>
                    <i class="fas fa-check-circle text-success fs-3"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-soft p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted">Today</div>
                        <div class="stat-value">{{ $todayAppointments }}</div>
                    </div>
                    <i class="fas fa-calendar-day text-primary fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="toggle-row">
        <button type="button" class="toggle-btn active" data-target="nearbyPanel">Nearby Appointments</button>
        <button type="button" class="toggle-btn" data-target="assignedPanel">Assigned Appointments</button>
    </div>

    <div id="nearbyPanel" class="card-soft p-3 mb-4">
        <h5 class="mb-3">Nearby Appointments</h5>
        @if($relevantAppointments->isEmpty())
            <div class="alert alert-info mb-0">No nearby appointments are available for your city yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Service</th>
                        <th>City</th>
                        <th>Address</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($relevantAppointments as $appointment)
                        <tr>
                            <td>{{ $appointment->id }}</td>
                            <td>{{ ucfirst($appointment->service_type) }}</td>
                            <td>{{ $appointment->city ?? 'N/A' }}</td>
                            <td>{{ $appointment->address_line ?? 'N/A' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ ucfirst($appointment->status) }}</span>
                                    @if($appointment->assigned_staff_id === null)
                                        <form method="POST" action="{{ route('staff.appointments.accept', $appointment) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div id="assignedPanel" class="card-soft p-3 hidden-panel">
        <h5 class="mb-3">Assigned Appointments</h5>
        @if($assignedAppointments->isEmpty())
            <div class="alert alert-info mb-0">No appointments assigned yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($assignedAppointments as $appointment)
                        <tr>
                            <td>{{ $appointment->id }}</td>
                            <td>{{ ucfirst($appointment->service_type) }}</td>
                            <td>{{ ucfirst($appointment->status) }}</td>
                            <td>{{ optional($appointment->booking_date)->format('M d, Y h:i A') ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
<script>
    document.querySelectorAll('.toggle-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('.toggle-btn').forEach(function (btn) {
                btn.classList.remove('active');
            });
            this.classList.add('active');

            document.querySelectorAll('.card-soft[id$="Panel"]').forEach(function (panel) {
                panel.classList.add('hidden-panel');
            });

            document.getElementById(this.dataset.target).classList.remove('hidden-panel');
        });
    });
</script>
</body>
</html>
