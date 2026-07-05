@extends('admin.layouts.app')

@section('title', 'Revenue Report')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Revenue Report</h1>
    <p class="text-muted">Track revenue activity for your garden business</p>
</div>

<div class="card">
    <div class="card-body">
        @if(isset($data) && $data->count())
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                            <tr>
                                <td>{{ $row->date }}</td>
                                <td>${{ number_format($row->total ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mb-0">No revenue data available yet.</div>
        @endif
    </div>
</div>
@endsection
