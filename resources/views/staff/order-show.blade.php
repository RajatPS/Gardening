<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $order->order_number }} - GardenHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-4">
    <a href="{{ route('staff.orders.index') }}" class="btn btn-link px-0">Back to branch orders</a>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3">Order {{ $order->order_number }}</h1>
            <p><strong>Customer:</strong> {{ $order->user?->name ?? 'N/A' }}</p>
            <p><strong>Branch:</strong> {{ $order->branch?->name ?? 'N/A' }}</p>
            <p><strong>Delivery:</strong><br>{{ $order->shipping_address }}</p>
            <p><strong>Contact:</strong> {{ $order->contact_phone }}</p>
            <p><strong>Total:</strong> ₹{{ number_format($order->total_amount, 2) }}</p>
            <form method="POST" action="{{ route('staff.orders.status', $order) }}" class="row g-2">
                @csrf
                <div class="col-auto">
                    <select name="status" class="form-select">
                        @foreach(['pending', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-primary">Update status</button></div>
            </form>
        </div>
    </div>
</main>
</body>
</html>