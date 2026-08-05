@extends('layouts.app')

@section('title', 'Cart')

@section('content')
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading eyebrow="Cart" title="Your selected plants and garden essentials" description="Review your items, update quantities, or pick a subset for checkout." />

        @if (session('success'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mt-6 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @php
            $selectedIds = collect(old('selected_items', session('checkout_selected_ids', [])) ?? [])->map(fn ($id) => (int) $id)->values()->all();
            if (empty($selectedIds)) {
                $selectedIds = $items->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            }
        @endphp

        @if ($items->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-stone-50 p-8 text-center text-slate-600">
                Your cart is empty right now.
            </div>
        @else
            <div class="mt-8 grid gap-6 lg:grid-cols-[2fr_1fr]">
                <div class="space-y-4">
                    @foreach ($items as $item)
                        <div class="cart-item-row flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:gap-5">
                            <div class="flex flex-1 min-w-0 items-center gap-4">
                                <input type="checkbox" class="cart-item-selector mt-1 h-4 w-4 flex-shrink-0 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" value="{{ $item->id }}" data-price="{{ (float) $item->price }}" data-quantity="{{ (int) $item->quantity }}" @checked(in_array($item->id, $selectedIds, true))>
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="h-16 w-16 flex-shrink-0 rounded-md object-cover">
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="cart-product-name-wrap">
                                        <div class="cart-product-name-track">
                                            <span class="cart-product-name-text font-semibold text-slate-950">{{ $item->product_name }}</span>
                                        </div>
                                    </div>
                                    <div class="cart-product-name-wrap mt-1">
                                        <div class="cart-product-name-track">
                                            <span class="cart-product-name-text text-sm text-slate-600">{{ $item->product_category ?? 'Garden item' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-3 lg:ml-4 lg:flex-nowrap">
                                <div class="cart-item-total min-w-[84px] text-sm font-semibold text-slate-950 lg:text-right" data-item-id="{{ $item->id }}">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                                <form action="{{ route('customer.cart.update', $item) }}" method="POST" class="cart-quantity-form flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-2 py-2" data-item-id="{{ $item->id }}">
                                    @csrf
                                    <label class="text-sm text-slate-600" for="quantity-{{ $item->id }}">Qty</label>
                                    <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" value="{{ $item->quantity }}" class="w-16 rounded border border-slate-300 px-2 py-1 text-sm">
                                    <button type="submit" class="rounded border border-slate-300 px-3 py-1 text-sm font-medium text-slate-700 transition hover:border-emerald-500 hover:text-emerald-700">Update</button>
                                </form>
                                <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-2 py-2">
                                    @php
                                        $itemProductId = $cartItemProductIds[$item->id] ?? null;
                                        $itemIsSaved = $itemProductId !== null && in_array($itemProductId, $savedProductIds ?? [], true);
                                    @endphp
                                    <button
                                        type="button"
                                        class="save-btn inline-flex h-10 w-10 items-center justify-center rounded-full border transition {{ $itemIsSaved ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:text-emerald-700' }}"
                                        data-saved="{{ $itemIsSaved ? 'true' : 'false' }}"
                                        data-product-id="{{ $item->product_id ?? $itemProductId ?? '' }}"
                                        data-product-name="{{ $item->product_name }}"
                                        data-product-category="{{ $item->product_category ?? '' }}"
                                        data-price="{{ (float) $item->price }}"
                                        data-image-url="{{ $item->image_url ?? '' }}"
                                        data-toggle-url="{{ route('customer.saved-products.toggle') }}"
                                        data-csrf="{{ csrf_token() }}"
                                        title="{{ $itemIsSaved ? 'Remove from saved' : 'Save to wishlist' }}"
                                        aria-label="{{ $itemIsSaved ? 'Remove from saved' : 'Save to wishlist' }}"
                                    >
                                        <svg class="h-4 w-4 save-icon-outline {{ $itemIsSaved ? 'hidden' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20l-1.2-1.1C5.4 14.7 2 11.8 2 8.1 2 5.4 4.2 3.2 6.9 3.2c1.4 0 2.8.7 3.6 1.8.8-1.1 2.2-1.8 3.6-1.8 2.7 0 4.9 2.2 4.9 4.9 0 3.7-3.4 6.6-8.8 10.8L12 20z"/></svg>
                                        <svg class="h-4 w-4 save-icon-filled text-emerald-600 {{ $itemIsSaved ? '' : 'hidden' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 20l-1.2-1.1C5.4 14.7 2 11.8 2 8.1 2 5.4 4.2 3.2 6.9 3.2c1.4 0 2.8.7 3.6 1.8.8-1.1 2.2-1.8 3.6-1.8 2.7 0 4.9 2.2 4.9 4.9 0 3.7-3.4 6.6-8.8 10.8L12 20z"/></svg>
                                    </button>
                                    <form action="{{ route('customer.cart.remove', $item) }}" method="POST" class="cart-remove-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-rose-200 bg-white text-rose-600 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700" aria-label="Remove item" title="Remove item">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V4h6v3m-7 0l1 12a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2l1-12M10 11v6m4-6v6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>

                <div class="rounded-lg border border-slate-200 bg-stone-50 p-6">
                    <h3 class="text-lg font-semibold text-slate-950">Order summary</h3>
                    <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
                        <span>Subtotal</span>
                        <span id="selected-subtotal">₹{{ number_format($total, 2) }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                        <span>Delivery Charges</span>
                        <span id="delivery-charges">₹20.00</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                        <span>GST (18%)</span>
                        <span id="gst-amount">₹0.00</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                        <span>Selected items</span>
                        <span id="selected-count">{{ count($selectedIds) }}</span>
                    </div>
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between text-base font-semibold text-slate-950">
                            <span>Grand Total</span>
                            <span id="selected-total">₹{{ number_format($total + 20, 2) }}</span>
                        </div>
                    </div>
                    <form action="{{ route('customer.checkout') }}" method="POST" class="mt-6" id="checkout-form">
                        @csrf
                        <div id="selected-items-inputs"></div>
                        <input type="hidden" name="payable_amount" id="payable-amount" value="{{ number_format($total + 20, 2, '.', '') }}">
                        <button type="submit" id="checkout-button" class="w-full rounded-md bg-emerald-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-950 disabled:cursor-not-allowed disabled:bg-slate-400">Checkout</button>
                    </form>
                    <p class="mt-3 text-sm text-slate-500">Select one or more products to enable checkout for that subset.</p>
                </div>
            </div>
        @endif
    </div>
</section>

<style>
    .cart-product-name-wrap {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        position: relative;
        min-width: 0;
    }

    .cart-product-name-track {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        white-space: nowrap;
        will-change: transform;
        animation: none;
        transform: translate3d(0, 0, 0);
    }

    .cart-product-name-track.is-scrolling {
        animation: cart-product-marquee var(--scroll-duration, 12s) linear infinite;
        animation-delay: var(--scroll-delay, 0.8s);
        animation-fill-mode: both;
        animation-play-state: running;
    }

    .cart-product-name-track.is-scrolling:hover {
        animation-play-state: paused;
    }

    .cart-product-name-text {
        display: inline-block;
        white-space: nowrap;
        padding-right: 0.25rem;
    }

    .cart-product-name-text--duplicate {
        margin-left: 1.25rem;
    }

    @keyframes cart-product-marquee {
        0% {
            transform: translate3d(0, 0, 0);
        }
        100% {
            transform: translate3d(var(--scroll-distance, -50%), 0, 0);
        }
    }
</style>

<script>
    (function () {
        const formatCurrency = (value) => new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 2,
        }).format(value);

        const ensureDuplicateText = (track) => {
            const original = track.querySelector('.cart-product-name-text');
            if (!original) {
                return;
            }

            const existing = track.querySelector('.cart-product-name-text--duplicate');
            if (existing) {
                return;
            }

            const duplicate = document.createElement('span');
            duplicate.className = 'cart-product-name-text cart-product-name-text--duplicate font-semibold text-slate-950';
            duplicate.textContent = original.textContent;
            track.appendChild(duplicate);
        };

        const removeDuplicateText = (track) => {
            const duplicate = track.querySelector('.cart-product-name-text--duplicate');
            if (duplicate) {
                duplicate.remove();
            }
        };

        const applyOverflowState = () => {
            document.querySelectorAll('.cart-product-name-track').forEach((track) => {
                const container = track.parentElement;
                if (!container) {
                    return;
                }

                const overflowWidth = track.scrollWidth - container.clientWidth;
                const shouldScroll = overflowWidth > 2;

                track.classList.toggle('is-scrolling', shouldScroll);

                if (shouldScroll) {
                    ensureDuplicateText(track);
                    const duration = Math.max(9, Math.min(16, overflowWidth / 24));
                    track.style.setProperty('--scroll-duration', `${duration}s`);
                    track.style.setProperty('--scroll-distance', '-50%');
                    track.style.setProperty('--scroll-delay', '0.8s');
                } else {
                    removeDuplicateText(track);
                    track.style.removeProperty('--scroll-duration');
                    track.style.removeProperty('--scroll-distance');
                    track.style.removeProperty('--scroll-delay');
                }
            });
        };

        const syncCheckoutSummary = () => {
            const checkboxes = Array.from(document.querySelectorAll('.cart-item-selector:checked'));
            const selectedIds = checkboxes.map((checkbox) => checkbox.value);
            const subtotal = checkboxes.reduce((sum, checkbox) => {
                const price = Number(checkbox.dataset.price || 0);
                const quantity = Number(checkbox.dataset.quantity || 1);
                return sum + (price * quantity);
            }, 0);
            const deliveryCharges = 20;
            const gst = subtotal * 0.18;
            const grandTotal = subtotal + deliveryCharges + gst;
            const hiddenContainer = document.getElementById('selected-items-inputs');
            const checkoutButton = document.getElementById('checkout-button');

            if (hiddenContainer) {
                hiddenContainer.innerHTML = '';
                selectedIds.forEach((id) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_items[]';
                    input.value = id;
                    hiddenContainer.appendChild(input);
                });
            }

            const selectedSubtotalElement = document.getElementById('selected-subtotal');
            const deliveryChargesElement = document.getElementById('delivery-charges');
            const gstAmountElement = document.getElementById('gst-amount');
            const selectedTotalElement = document.getElementById('selected-total');
            const selectedCountElement = document.getElementById('selected-count');
            const payableAmountInput = document.getElementById('payable-amount');

            if (selectedSubtotalElement) {
                selectedSubtotalElement.textContent = formatCurrency(subtotal);
            }
            if (deliveryChargesElement) {
                deliveryChargesElement.textContent = formatCurrency(deliveryCharges);
            }
            if (gstAmountElement) {
                gstAmountElement.textContent = formatCurrency(gst);
            }
            if (selectedTotalElement) {
                selectedTotalElement.textContent = formatCurrency(grandTotal);
            }
            if (selectedCountElement) {
                selectedCountElement.textContent = String(selectedIds.length);
            }
            if (payableAmountInput) {
                payableAmountInput.value = grandTotal.toFixed(2);
            }
            if (checkoutButton) {
                checkoutButton.disabled = selectedIds.length === 0;
            }

            document.querySelectorAll('.cart-item-row').forEach((row) => {
                const checkbox = row.querySelector('.cart-item-selector');
                const totalElement = row.querySelector('.cart-item-total');
                if (!checkbox || !totalElement) {
                    return;
                }

                const price = Number(checkbox.dataset.price || 0);
                const quantity = Number(checkbox.dataset.quantity || 1);
                totalElement.textContent = formatCurrency(price * quantity);
            });
        };

        document.querySelectorAll('.cart-item-selector').forEach((checkbox) => {
            checkbox.addEventListener('change', syncCheckoutSummary);
        });

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!form.classList.contains('cart-quantity-form')) {
                return;
            }

            event.preventDefault();
            const quantityInput = form.querySelector('input[name="quantity"]');
            if (!quantityInput) {
                return;
            }

            const row = form.closest('.cart-item-row');
            const checkbox = row ? row.querySelector('.cart-item-selector') : null;
            const selectedState = checkbox ? checkbox.checked : false;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': formData.get('_token')
                }
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed');
                }
                return response.json();
            })
            .then(function () {
                if (checkbox) {
                    checkbox.dataset.quantity = quantityInput.value;
                    checkbox.checked = selectedState;
                }
                syncCheckoutSummary();
            })
            .catch(function () {
                if (checkbox) {
                    checkbox.checked = selectedState;
                }
                syncCheckoutSummary();
            });
        });

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!form.classList.contains('cart-remove-form')) {
                return;
            }

            event.preventDefault();
            const formData = new FormData(form);
            fetch(form.action, {
                method: 'DELETE',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': formData.get('_token')
                }
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed');
                }
                return response.json();
            })
            .then(function () {
                const row = form.closest('.cart-item-row');
                if (row) {
                    row.remove();
                }
                syncCheckoutSummary();
            })
            .catch(function () {
                syncCheckoutSummary();
            });
        });

        function applySavedState(btn, saved) {
            btn.dataset.saved = saved ? 'true' : 'false';
            btn.title = saved ? 'Remove from saved' : 'Save to wishlist';
            btn.setAttribute('aria-label', saved ? 'Remove from saved' : 'Save to wishlist');

            const outline = btn.querySelector('.save-icon-outline');
            const filled = btn.querySelector('.save-icon-filled');
            if (outline) outline.classList.toggle('hidden', saved);
            if (filled) filled.classList.toggle('hidden', !saved);
        }

        if (!window.gardeningAuthenticated) {
            document.querySelectorAll('.save-btn').forEach(function (btn) {
                const savedState = window.gardeningGuestSavedProducts.has(btn.dataset.productId);
                applySavedState(btn, savedState);
            });
        }

        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.save-btn');
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
                    btn.dataset.saved = isSaved ? 'true' : 'false';
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });

        window.addEventListener('load', () => {
            applyOverflowState();
            syncCheckoutSummary();
        });
        window.addEventListener('resize', applyOverflowState);
        applyOverflowState();
        syncCheckoutSummary();
    })();
</script>
@endsection
