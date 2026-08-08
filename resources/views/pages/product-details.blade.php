@extends('layouts.app')

@section('title', $product['name'])

@section('content')
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-2xl border border-slate-200 bg-stone-50 p-4 shadow-sm">
                <img id="productImage" src="{{ $product['gallery'][0] ?? $product['image'] }}" alt="{{ $product['name'] }}" class="h-[420px] w-full rounded-xl object-cover">
                <div class="mt-4 flex gap-3">
                    @foreach (($product['gallery'] ?? []) as $index => $image)
                        <button type="button" class="h-20 w-20 overflow-hidden rounded-lg border border-slate-200 bg-white p-0" data-image="{{ $image }}" aria-label="View image {{ $index + 1 }}">
                            <img src="{{ $image }}" alt="{{ $product['name'] }} thumbnail" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-800">{{ $product['category'] }}</p>
                    <h1 class="mt-3 text-3xl font-semibold text-slate-950">{{ $product['name'] }}</h1>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium text-slate-600">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1">Type: {{ $product['type'] ?? 'general' }}</span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1">Stock: {{ $product['stock'] ?? 0 }}</span>
                        @if (!empty($product['weight']))
                            <span class="rounded-full bg-slate-100 px-2.5 py-1">Weight: {{ $product['weight'] }}</span>
                        @endif
                    </div>
                    <p class="mt-4 text-base leading-7 text-slate-600">{{ $product['description'] }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Price</p>
                            <p class="text-2xl font-semibold text-slate-950">{{ $product['price'] }}</p>
                        </div>
                        <div class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-800">In stock</div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <form action="{{ route('customer.cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                            <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                            <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                            <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                            <button type="submit" class="rounded-md bg-emerald-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-950">Add to Cart</button>
                        </form>
                        @php
                            $isSaved = in_array($product['id'], $savedProductNames ?? []);
                        @endphp
                        <button
                            type="button"
                            class="save-btn inline-flex items-center gap-2 rounded-md border px-5 py-2.5 text-sm font-semibold transition {{ $isSaved ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-slate-300 text-slate-800' }}"
                            data-saved="{{ $isSaved ? 'true' : 'false' }}"
                            data-product-id="{{ $product['id'] }}"
                            data-product-name="{{ $product['name'] }}"
                            data-product-category="{{ $product['category'] }}"
                            data-price="{{ $product['price_value'] ?? 0 }}"
                            data-image-url="{{ $product['image'] }}"
                            data-toggle-url="{{ route('customer.saved-products.toggle') }}"
                            data-csrf="{{ csrf_token() }}"
                            title="{{ $isSaved ? 'Remove from saved' : 'Save to wishlist' }}"
                        >
                            <svg class="h-4 w-4 save-icon-outline {{ $isSaved ? 'hidden' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20l-1.2-1.1C5.4 14.7 2 11.8 2 8.1 2 5.4 4.2 3.2 6.9 3.2c1.4 0 2.8.7 3.6 1.8.8-1.1 2.2-1.8 3.6-1.8 2.7 0 4.9 2.2 4.9 4.9 0 3.7-3.4 6.6-8.8 10.8L12 20z"/></svg>
                            <svg class="h-4 w-4 save-icon-filled text-emerald-600 {{ $isSaved ? '' : 'hidden' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 20l-1.2-1.1C5.4 14.7 2 11.8 2 8.1 2 5.4 4.2 3.2 6.9 3.2c1.4 0 2.8.7 3.6 1.8.8-1.1 2.2-1.8 3.6-1.8 2.7 0 4.9 2.2 4.9 4.9 0 3.7-3.4 6.6-8.8 10.8L12 20z"/></svg>
                            <span class="save-label">{{ $isSaved ? 'Saved' : 'Save' }}</span>
                        </button>
                        <form action="{{ route('customer.buy-now') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                            <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                            <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                            <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                            <button type="submit" class="rounded-md border border-emerald-900 px-5 py-2.5 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50">Buy Now</button>
                        </form>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-stone-50 p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-950">Why customers love it</h2>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        @foreach ($product['details'] as $detail)
                            <li class="flex gap-2"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-700"></span><span>{{ $detail }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-image]').forEach((button) => {
            button.addEventListener('click', () => {
                const image = button.getAttribute('data-image');
                const target = document.getElementById('productImage');
                if (target && image) {
                    target.src = image;
                }
            });
        });
    });

    (function () {
        function applySavedState(btn, saved) {
            btn.dataset.saved = saved ? 'true' : 'false';
            btn.title = saved ? 'Remove from saved' : 'Save to wishlist';

            if (saved) {
                btn.classList.remove('border-slate-300', 'text-slate-800', 'hover:border-emerald-500', 'hover:text-emerald-700');
                btn.classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-700');
            } else {
                btn.classList.remove('border-emerald-600', 'bg-emerald-50', 'text-emerald-700');
                btn.classList.add('border-slate-300', 'text-slate-800');
            }

            const outline = btn.querySelector('.save-icon-outline');
            const filled = btn.querySelector('.save-icon-filled');
            if (outline) outline.classList.toggle('hidden', saved);
            if (filled) filled.classList.toggle('hidden', !saved);

            const label = btn.querySelector('.save-label');
            if (label) {
                label.textContent = saved ? 'Saved' : 'Save';
            }
        }

        document.querySelectorAll('.save-btn').forEach(function (btn) {
            if (!window.gardeningAuthenticated) {
                const savedState = window.gardeningGuestSavedProducts.has(btn.dataset.productId);
                applySavedState(btn, savedState);
            }
        });

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.save-btn');
            if (!btn) return;

            if (btn.disabled) return;
            btn.disabled = true;

            if (!window.gardeningAuthenticated) {
                window.gardeningHandleGuestSaveClick(btn);
                btn.disabled = false;
                return;
            }

            const isSaved = btn.dataset.saved === 'true';
            const formData = new FormData();
            formData.append('_token', btn.dataset.csrf);
            formData.append('product_id', btn.dataset.productId);
            formData.append('product_name', btn.dataset.productName);
            formData.append('product_category', btn.dataset.productCategory || '');
            formData.append('price', btn.dataset.price || '0');
            formData.append('image_url', btn.dataset.imageUrl || '');

            fetch(btn.dataset.toggleUrl, { method: 'POST', body: formData })
                .then(function (res) {
                    if (res.ok) return res.json();
                    throw new Error('Request failed');
                })
                .then(function (data) {
                    applySavedState(btn, data.saved);
                })
                .catch(function () {
                    // On error leave button in original state
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    })();

    // Prevent duplicate add-to-cart submissions on product details page
    (function () {
        document.querySelectorAll('form[action="{{ route('customer.cart.add') }}"]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const btn = form.querySelector('button[type="submit"]');
                if (!btn) return;
                if (btn.dataset.pending === 'true') {
                    e.preventDefault();
                    return;
                }
                btn.dataset.pending = 'true';
                btn.disabled = true;
            });
        });
    })();
</script>
@endsection
