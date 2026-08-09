@extends('admin.layouts.app')

@section('title', 'Order Management')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Order Management</h1>
    <p class="text-muted">Manage all customer orders</p>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Orders List</h5>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by order number or customer"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <select class="form-select" name="payment_status">
                        <option value="">All Payment</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Orders Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><strong>#{{ $order->order_number }}</strong></td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ optional($order->created_at)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>₹{{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'shipped' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                @if($order->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-outline-info">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <div class="btn-group" role="group">
                                        <button id="orderStatusDropdown{{ $order->id }}" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ ucfirst($order->status) }}
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="orderStatusDropdown{{ $order->id }}">
                                            <li>
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="pending">
                                                    <button type="submit" class="dropdown-item">Pending</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="processing">
                                                    <button type="submit" class="dropdown-item">Processing</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="shipped">
                                                    <button type="submit" class="dropdown-item">Shipped</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="out_for_delivery">
                                                    <button type="submit" class="dropdown-item">Out for Delivery</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="delivered">
                                                    <button type="submit" class="dropdown-item">Order Successful</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="dropdown-item">Order Cancelled</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                    <form method="POST" action="{{ route('admin.orders.destroy', $order->id) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger admin-action-delete" data-confirm="Delete this order?" data-confirm-title="Delete order" data-confirm-button-text="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No orders found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $orders->count() }} of {{ $orders->total() }} orders</p>
            {{ $orders->links() }}
        </div>
    </div>
</div>

@endsection
