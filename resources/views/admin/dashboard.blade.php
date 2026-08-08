@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Dashboard</h1>
    <p class="text-muted">Welcome to your admin dashboard</p>
</div>

<!-- Revenue Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary-light">
                <i class="fas fa-indian-rupee-sign text-primary"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Today's Revenue</p>
                <h3 class="stat-value">₹{{ number_format($dailyRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success-light">
                <i class="fas fa-chart-line text-success"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Weekly Revenue</p>
                <h3 class="stat-value">₹{{ number_format($weeklyRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning-light">
                <i class="fas fa-chart-bar text-warning"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Monthly Revenue</p>
                <h3 class="stat-value">₹{{ number_format($monthlyRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-info-light">
                <i class="fas fa-calendar text-info"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Yearly Revenue</p>
                <h3 class="stat-value">₹{{ number_format($yearlyRevenue, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Order & User Statistics -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Sales Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Total Orders</span>
                            <span class="stat-number">{{ $totalOrders }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Pending</span>
                            <span class="stat-number text-warning">{{ $pendingOrders }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Completed</span>
                            <span class="stat-number text-success">{{ $completedOrders }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Cancelled</span>
                            <span class="stat-number text-danger">{{ $cancelledOrders }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">User Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Total Users</span>
                            <span class="stat-number">{{ $totalUsers }}</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Active</span>
                            <span class="stat-number text-success">{{ $activeUsers }}</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Suspended</span>
                            <span class="stat-number text-danger">{{ $suspendedUsers }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product & Appointment Statistics -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Product Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Total Products</span>
                            <span class="stat-number">{{ $totalProducts }}</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Low Stock</span>
                            <span class="stat-number text-warning">{{ $lowStockProducts }}</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Out of Stock</span>
                            <span class="stat-number text-danger">{{ $outOfStockProducts }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Appointment Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Upcoming</span>
                            <span class="stat-number text-info">{{ $upcomingVisits }}</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Today</span>
                            <span class="stat-number text-primary">{{ $todaysVisits }}</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-small">
                            <span class="stat-label">Completed</span>
                            <span class="stat-number text-success">{{ $completedVisits }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subscription & Charts -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Subscription Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="stat-small">
                        <span class="stat-label">Active Plans</span>
                        <span class="stat-number text-success">{{ $activePlans }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="stat-small">
                        <span class="stat-label">Expiring Soon</span>
                        <span class="stat-number text-warning">{{ $expiringPlans }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Revenue Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Sales Chart</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">User Growth</h5>
            </div>
            <div class="card-body">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Revenue Chart
    var revenueCtx = document.getElementById('revenueChart').getContext('2d');
    var revenueData = @json($revenueChart);
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: Object.keys(revenueData),
            datasets: [{
                label: 'Revenue',
                data: Object.values(revenueData),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });

    // Sales Chart
    var salesCtx = document.getElementById('salesChart').getContext('2d');
    var salesData = @json($salesChart);
    new Chart(salesCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(salesData),
            datasets: [{
                data: Object.values(salesData),
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // User Growth Chart
    var userCtx = document.getElementById('userGrowthChart').getContext('2d');
    var userData = @json($userGrowthChart);
    new Chart(userCtx, {
        type: 'line',
        data: {
            labels: Object.keys(userData),
            datasets: [{
                label: 'Total Users',
                data: Object.values(userData),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush

@endsection
