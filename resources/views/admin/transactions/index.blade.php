@extends('admin.layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Transactions</h1>
    <p class="text-muted">View all payment transactions</p>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Transaction Records</h5>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <select class="form-select" name="payment_method">
                        <option value="">All Methods</option>
                        <option value="razorpay" {{ request('payment_method') === 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                        <option value="stripe" {{ request('payment_method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                        <option value="paypal" {{ request('payment_method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Transactions Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Transaction ID</th>
                        <th>User</th>
                        <th>Order #</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td><strong>{{ $transaction->transaction_id }}</strong></td>
                            <td>{{ $transaction->user_name }}</td>
                            <td>#{{ $transaction->order_number }}</td>
                            <td>${{ number_format($transaction->amount, 2) }}</td>
                            <td>{{ ucfirst($transaction->payment_method) }}</td>
                            <td>
                                @if($transaction->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td>{{ optional($transaction->created_at)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No transactions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $transactions->count() }} of {{ $transactions->total() }} transactions</p>
            {{ $transactions->links() }}
        </div>
    </div>
</div>

@endsection
