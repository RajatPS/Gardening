<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Branch Orders - GardenHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Branch Orders</h1>
            <p class="text-muted mb-0">Orders assigned to {{ $staff->branch?->name ?? 'your branch' }}</p>
        </div>
        <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary">Dashboard</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Order</th><th>Customer</th><th>Branch</th><th>Total</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->user?->name ?? 'N/A' }}</td>
                        <td>{{ $order->branch?->name ?? 'N/A' }}</td>
                        <td>₹{{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('staff.orders.show', $order) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No orders for this branch.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $orders->links() }}</div>
    </div>
</main>
</body>
</html>