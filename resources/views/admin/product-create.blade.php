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
                    <input type="file" id="image-upload-input" name="images[]" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                    <div class="form-text">Upload one or more product images. JPG, JPEG, PNG, and WEBP are supported. Max size: 2MB per image.</div>
                    <div class="form-text text-primary"><strong>Tip:</strong> Drag and drop the selected images to arrange their display order. The first image in the list will automatically be used as the product thumbnail across the website.</div>
                    <div id="image-preview" class="mt-3 row g-2"></div>
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

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('image-upload-input');
        const preview = document.getElementById('image-preview');
        const form = input.closest('form');
        let selectedFiles = [];

        if (!input || !preview || !form) return;

        // Initialize SortableJS
        const sortable = new Sortable(preview, {
            animation: 150,
            ghostClass: 'bg-light',
            handle: '.drag-handle',
            onEnd: function () {
                updateBadges();
            }
        });

        input.addEventListener('change', function () {
            const files = Array.from(this.files || []);
            
            files.forEach(file => {
                if (!file.type.startsWith('image/')) return;
                
                // Assign a unique ID to the file object for tracking
                file._id = Math.random().toString(36).substr(2, 9);
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
                                <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-btn">
                                    <i class="fas fa-trash-can"></i>
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
            
            // Clear input so selecting same files triggers change event again
            input.value = '';
        });

        function updateBadges() {
            const cards = preview.querySelectorAll('.draggable-card');
            cards.forEach((card, index) => {
                const thumbBadge = card.querySelector('.thumbnail-badge');
                const posBadge = card.querySelector('.position-badge');
                
                posBadge.textContent = 'Image ' + (index + 1);
                
                if (index === 0) {
                    thumbBadge.style.display = 'block';
                    card.querySelector('.card').classList.add('border-primary', 'border-2');
                } else {
                    thumbBadge.style.display = 'none';
                    card.querySelector('.card').classList.remove('border-primary', 'border-2');
                }
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            
            // Create a new DataTransfer object
            const dt = new DataTransfer();
            
            // Get current order from DOM
            const cards = preview.querySelectorAll('.draggable-card');
            cards.forEach((card, index) => {
                const fileId = card.dataset.id;
                const file = selectedFiles.find(f => f._id === fileId);
                if (file) {
                    dt.items.add(file);
                    
                    // Create hidden input for ordering
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'image_order[]';
                    hidden.value = 'new_' + index;
                    form.appendChild(hidden);
                }
            });
            
            input.files = dt.files;
            
            // Actually submit the form
            form.submit();
        });
    });
</script>
@endpush
