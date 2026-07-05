@extends('admin.layouts.app')

@section('title','Garden Setups')

@section('content')
    <div class="page-header mb-4">
        <h1 class="page-title">Garden Setup Requests</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Budget</th>
                            <th>Received</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $it)
                            <tr>
                                <td>{{ $it->id }}</td>
                                <td>{{ $it->name }}</td>
                                <td>{{ $it->phone }}</td>
                                <td>{{ Str::limit($it->address,60) }}</td>
                                <td>{{ $it->budget }}</td>
                                <td>{{ $it->created_at->diffForHumans() }}</td>
                                <td><a href="tel:{{ $it->phone }}" class="btn btn-sm btn-primary">Call</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $items->links() }}</div>
        </div>
    </div>
@endsection
