@extends('admin.layouts.app')

@section('title', 'Product Management')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Product Management</h1>
    <p class="text-muted">Manage all products in your catalog</p>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Products List</h5>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i> Add Product
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.products.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, SKU, or category"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>Hidden</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <select class="form-select" name="stock_status">
                        <option value="">All Stock</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </div>
        </form>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Product ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>#{{ $product->id }}</td>
                            <td>
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                        class="rounded" width="40" height="40" style="object-fit: cover;">
                                @else
                                    <div class="bg-light rounded" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                <br>
                                <small class="text-muted">SKU: {{ $product->sku }}</small>
                            </td>
                            <td>{{ $product->category }}</td>
                            <td>
                                @if(($product->stock ?? 0) > 10)
                                    <span class="badge bg-success">{{ $product->stock ?? 0 }} in stock</span>
                                @elseif(($product->stock ?? 0) > 0)
                                    <span class="badge bg-warning">{{ $product->stock ?? 0 }} low stock</span>
                                @else
                                    <span class="badge bg-danger">Out of stock</span>
                                @endif
                            </td>
                            <td>${{ number_format($product->price, 2) }}</td>
                            <td>
                                @if($product->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($product->status === 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @else
                                    <span class="badge bg-danger">Hidden</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            data-confirm="Delete this product?"
                                            data-confirm-title="Delete product"
                                            data-confirm-button-text="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted">No products found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $products->count() }} of {{ $products->total() }} products</p>
            {{ $products->links() }}
        </div>
    </div>
</div>

@endsection
