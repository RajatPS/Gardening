@extends('admin.layouts.app')

@section('title', 'User Reports')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">User Reports</h1>
    <p class="text-muted">Overview of registered users and growth</p>
</div>

<div class="card">
    <div class="card-body">
        @if(!empty($data))
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="border rounded p-3"><strong>Total Users:</strong> {{ $data['total'] ?? 0 }}</div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3"><strong>Active:</strong> {{ $data['active'] ?? 0 }}</div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3"><strong>Suspended:</strong> {{ $data['suspended'] ?? 0 }}</div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3"><strong>Monthly Growth:</strong> {{ $data['monthly_growth'][array_key_first($data['monthly_growth'] ?? [])] ?? 0 }}</div>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-0">No user report data available yet.</div>
        @endif
    </div>
</div>
@endsection
