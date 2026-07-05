@extends('admin.layouts.app')

@section('title', 'Staff Performance')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Staff Performance</h1>
    <p class="text-muted">Performance overview for staff members</p>
</div>

<div class="card">
    <div class="card-body">
        @if(!empty($data) && count($data))
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Appointments</th>
                            <th>Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->appointments ?? 0 }}</td>
                                <td>{{ $row->orders ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mb-0">No staff performance data available yet.</div>
        @endif
    </div>
</div>
@endsection
