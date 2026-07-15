@extends('admin.layouts.app')

@section('title', isset($product) ? 'Edit Product' : 'Create Product')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">{{ isset($product) ? 'Edit Product' : 'Create Product' }}</h1>
    <p class="text-muted">{{ isset($product) ? 'Update product information and images' : 'Add a new product to the catalog' }}</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($product))
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name', $product->name ?? '') }}" required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" class="form-control @error('type') is-invalid @enderror" 
                        placeholder="plant, tool, fertilizer" value="{{ old('type', $product->type ?? '') }}">
                    @error('type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" 
                        value="{{ old('category', $product->category ?? '') }}" required>
                    @error('category')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                        value="{{ old('quantity', $product->quantity ?? 0) }}" min="0" required>
                    @error('quantity')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" 
                        value="{{ old('price', $product->price ?? 0) }}" min="0" step="0.01" required>
                    @error('price')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" 
                        value="{{ old('stock', $product->stock ?? 0) }}" min="0">
                    @error('stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Product Images</label>
                    <input type="file" name="images[]" class="form-control @error('images.*') is-invalid @enderror" 
                        multiple accept="image/*">
                    <small class="text-muted d-block mt-2">You can upload multiple images. Supported formats: JPEG, PNG, GIF, WebP. Max size: 5MB per image.</small>
                    @error('images.*')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                </div>

                @if(isset($product) && $product->images->count() > 0)
                    <div class="col-12">
                        <label class="form-label">Current Images</label>
                        <div class="row g-2">
                            @foreach($product->images as $image)
                                <div class="col-md-2">
                                    <div class="card">
                                        <img src="{{ asset('storage/' . $image->path) }}" alt="Product image" 
                                            class="card-img-top" style="height: 150px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            <button type="button" class="btn btn-sm btn-danger btn-block w-100" 
                                                onclick="if(confirm('Delete this image?')) { document.getElementById('delete-image-{{ $image->id }}').click(); }">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                            <form id="form-delete-image-{{ $image->id }}" method="POST" 
                                                action="{{ route('admin.product-images.destroy', $image) }}" 
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_pet_safe" value="1" id="is_pet_safe"
                            {{ old('is_pet_safe', $product->is_pet_safe ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_pet_safe">Pet Safe</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ isset($product) ? 'Update Product' : 'Save Product' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
