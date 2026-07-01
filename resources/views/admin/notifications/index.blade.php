@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Notifications</h1>
    <p class="text-muted">View and manage admin notifications.</p>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">All Notifications</h5>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-2"></i> Create Notification
        </a>
    </div>
    <div class="card-body">
        <p class="text-muted">This admin notification system is currently a placeholder. Create a notification to see a success response.</p>
    </div>
</div>
@endsection
