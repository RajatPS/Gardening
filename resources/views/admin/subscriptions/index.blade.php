@extends('admin.layouts.app')

@section('title', 'Subscriptions')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Subscriptions</h1>
    <p class="text-muted">Manage user subscriptions and plans</p>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Subscriptions List</h5>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by user"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Subscriptions Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->user->name ?? 'N/A' }}</td>
                            <td>{{ $subscription->plan_name ?? 'Premium Plan' }}</td>
                            <td>{{ optional($subscription->start_date)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>{{ optional($subscription->end_date)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>${{ number_format($subscription->amount, 2) }}</td>
                            <td>
                                @if($subscription->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($subscription->status === 'expired')
                                    <span class="badge bg-danger">Expired</span>
                                @else
                                    <span class="badge bg-secondary">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($subscription->status === 'active')
                                        <form method="POST" action="{{ route('admin.subscriptions.renew', $subscription) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-sync"></i> Renew
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">No subscriptions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $subscriptions->count() }} of {{ $subscriptions->total() }} subscriptions</p>
            {{ $subscriptions->links() }}
        </div>
    </div>
</div>

@endsection
