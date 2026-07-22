@extends('admin.layouts.app')

@section('title', 'Create Product')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Create Product</h1>
    <p class="text-muted">Add a new product to the catalog</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" class="form-control" placeholder="plant, tool, fertilizer">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="0" min="0" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Add a short product description"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Product Images</label>
                    <input type="file" name="images[]" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                    <div class="form-text">Upload one or more product images. JPG, JPEG, PNG, and WEBP are supported. Max size: 2MB per image.</div>
                    <div id="image-preview" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_pet_safe" value="1" id="is_pet_safe">
                        <label class="form-check-label" for="is_pet_safe">Pet Safe</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 border-top pt-4">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="confirm_details" id="confirm_details" value="1" required>
                    <label class="form-check-label" for="confirm_details">Confirm product details are correct</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.querySelector('input[name="images[]"]');
        const preview = document.getElementById('image-preview');

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function () {
            preview.innerHTML = '';
            const files = Array.from(this.files || []);

            files.forEach(file => {
                if (!file.type.startsWith('image/')) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    const image = document.createElement('img');
                    image.src = event.target.result;
                    image.alt = file.name;
                    image.className = 'rounded border';
                    image.style.width = '96px';
                    image.style.height = '96px';
                    image.style.objectFit = 'cover';
                    preview.appendChild(image);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>
@endpush
