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
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="tel:{{ $it->phone }}" class="btn btn-primary">Call</a>
                                            <form method="POST" action="{{ route('admin.garden-setups.destroy', $it->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" data-confirm="Delete this garden setup request?" data-confirm-title="Delete request" data-confirm-button-text="Delete">
                                                    <i class="fas fa-trash-can"></i>
                                                </button>
                                            </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $items->links() }}</div>
        </div>
    </div>
@endsection
