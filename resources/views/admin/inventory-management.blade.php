@extends('admin.layouts.app')

@section('title', 'Inventory Management')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Inventory Management</h1>
    <p class="text-muted">Manage product stock and inventory levels</p>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Inventory List</h5>
    </div>

    <div class="card-body">
        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by product name or SKU"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="stock_status">
                        <option value="">All Stock Levels</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock (1-10)</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Inventory Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $product)
                        <tr>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->sku }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $product->stock }} units</span>
                            </td>
                            <td>
                                @if($product->stock > 10)
                                    <span class="badge bg-success">In Stock</span>
                                @elseif($product->stock > 0)
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="showAddStockModal({{ $product->id }})">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="showReduceStockModal({{ $product->id }})">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                        <form method="POST" action="{{ route('admin.inventory.destroy', $product) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-dark btn-sm" data-confirm="Delete this inventory item?" data-confirm-title="Delete inventory item" data-confirm-button-text="Delete">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <p class="text-muted">No inventory items found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted mb-0">Showing {{ $inventory->count() }} of {{ $inventory->total() }} items</p>
            {{ $inventory->links() }}
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStockForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Quantity to Add</label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showAddStockModal(productId) {
    var modal = new bootstrap.Modal(document.getElementById('addStockModal'));
    document.getElementById('addStockForm').action = '/admin/inventory/' + productId + '/add-stock';
    modal.show();
}

function showReduceStockModal(productId) {
    Swal.fire({
        title: 'Reduce stock',
        text: 'Enter the quantity to reduce for this product.',
        input: 'number',
        inputAttributes: {
            min: 1,
            step: 1,
        },
        inputLabel: 'Quantity',
        showCancelButton: true,
        confirmButtonText: 'Reduce stock',
        cancelButtonText: 'Cancel',
        preConfirm: (quantity) => {
            const value = Number(quantity);
            if (!value || value < 1) {
                Swal.showValidationMessage('Please enter a valid quantity greater than 0.');
            }
            return value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const quantity = result.value;
            fetch('/admin/inventory/' + productId + '/reduce-stock', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Reduced!', 'Stock quantity has been reduced.', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Unable to reduce stock.', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Unable to reduce stock.', 'error');
            });
        }
    });
}
</script>
@endpush

@endsection
