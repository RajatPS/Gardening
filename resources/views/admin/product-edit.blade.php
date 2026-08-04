@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="page-header mb-4">
    <h1 class="page-title">Edit Product</h1>
    <p class="text-muted">Update product details and stock availability</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ old('category', $product->category) === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" min="0" step="0.01" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}" min="0" required>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_pet_safe" value="1" id="is_pet_safe" {{ old('is_pet_safe', $product->is_pet_safe) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_pet_safe">Pet Safe</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <label class="form-label">Product Images</label>
                    <input type="file" id="image-upload-input" name="images[]" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                    <div class="form-text">Upload one or more product images. JPG, JPEG, PNG, and WEBP are supported. Max size: 2MB per image.</div>
                    <div class="form-text text-primary"><strong>Tip:</strong> Drag and drop the selected images to arrange their display order. The first image in the list will automatically be used as the product thumbnail across the website.</div>
                    <div id="image-preview" class="mt-3 row g-2">
                        @foreach($product->images as $index => $image)
                            <div class="col-auto position-relative draggable-card" data-id="existing_{{ $image->id }}">
                                <div class="card shadow-sm border {{ $image->is_primary ? 'border-primary border-2' : '' }}" style="width: 120px; cursor: grab;">
                                    <div class="card-img-top bg-light position-relative drag-handle" style="height: 120px;">
                                        <img src="{{ Storage::url($image->path) }}" class="w-100 h-100 object-fit-cover rounded-top" alt="Preview">
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-1 thumbnail-badge" style="{{ $image->is_primary ? 'display: block;' : 'display: none;' }}">Thumbnail</span>
                                        <span class="badge bg-dark position-absolute bottom-0 start-0 m-1 position-badge">Image {{ $index + 1 }}</span>
                                    </div>
                                    <div class="card-body p-2 text-center drag-handle">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-btn" data-type="existing" data-id="{{ $image->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('image-upload-input');
        const preview = document.getElementById('image-preview');
        const form = input.closest('form');
        let selectedFiles = [];

        if (!input || !preview || !form) return;

        const sortable = new Sortable(preview, {
            animation: 150,
            ghostClass: 'bg-light',
            handle: '.drag-handle',
            onEnd: function () {
                updateBadges();
            }
        });
        
        preview.querySelectorAll('.remove-btn[data-type="existing"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const imageId = this.dataset.id;
                
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'delete_images[]';
                hidden.value = imageId;
                form.appendChild(hidden);
                
                this.closest('.draggable-card').remove();
                updateBadges();
            });
        });

        input.addEventListener('change', function () {
            const files = Array.from(this.files || []);
            
            files.forEach(file => {
                if (!file.type.startsWith('image/')) return;
                
                file._id = 'new_' + Math.random().toString(36).substr(2, 9);
                selectedFiles.push(file);

                const reader = new FileReader();
                reader.onload = function (event) {
                    const col = document.createElement('div');
                    col.className = 'col-auto position-relative draggable-card';
                    col.dataset.id = file._id;
                    
                    col.innerHTML = `
                        <div class="card shadow-sm border" style="width: 120px; cursor: grab;">
                            <div class="card-img-top bg-light position-relative drag-handle" style="height: 120px;">
                                <img src="${event.target.result}" class="w-100 h-100 object-fit-cover rounded-top" alt="Preview">
                                <span class="badge bg-primary position-absolute top-0 start-0 m-1 thumbnail-badge" style="display: none;">Thumbnail</span>
                                <span class="badge bg-dark position-absolute bottom-0 start-0 m-1 position-badge"></span>
                            </div>
                            <div class="card-body p-2 text-center drag-handle">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-btn" data-type="new">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;

                    col.querySelector('.remove-btn').addEventListener('click', function(e) {
                        e.stopPropagation();
                        selectedFiles = selectedFiles.filter(f => f._id !== file._id);
                        col.remove();
                        updateBadges();
                    });

                    preview.appendChild(col);
                    updateBadges();
                };
                reader.readAsDataURL(file);
            });
            
            input.value = '';
        });

        function updateBadges() {
            const cards = preview.querySelectorAll('.draggable-card');
            cards.forEach((card, index) => {
                const thumbBadge = card.querySelector('.thumbnail-badge');
                const posBadge = card.querySelector('.position-badge');
                
                if (posBadge) posBadge.textContent = 'Image ' + (index + 1);
                
                if (index === 0) {
                    if (thumbBadge) thumbBadge.style.display = 'block';
                    card.querySelector('.card').classList.add('border-primary', 'border-2');
                } else {
                    if (thumbBadge) thumbBadge.style.display = 'none';
                    card.querySelector('.card').classList.remove('border-primary', 'border-2');
                }
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            
            const dt = new DataTransfer();
            let newFileIndex = 0;
            
            const cards = preview.querySelectorAll('.draggable-card');
            cards.forEach((card) => {
                const dataId = card.dataset.id;
                
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'image_order[]';
                
                if (dataId.startsWith('existing_')) {
                    hidden.value = dataId;
                } else {
                    const file = selectedFiles.find(f => f._id === dataId);
                    if (file) {
                        dt.items.add(file);
                        hidden.value = 'new_' + newFileIndex;
                        newFileIndex++;
                    }
                }
                
                form.appendChild(hidden);
            });
            
            input.files = dt.files;
            form.submit();
        });
    });
</script>
@endpush
