@extends('admin.layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Sales Report</h1>
    <p class="text-muted">Summary of your sales by status</p>
</div>

<div class="card">
    <div class="card-body">
        @if(!empty($data) && count($data))
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                            <tr>
                                <td>{{ ucfirst($row->status ?? 'unknown') }}</td>
                                <td>{{ $row->count ?? 0 }}</td>
                                <td>₹{{ number_format($row->amount ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mb-0">No sales data available yet.</div>
        @endif
    </div>
</div>
@endsection
