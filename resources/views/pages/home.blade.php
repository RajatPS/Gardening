@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_0.86fr] lg:px-8 lg:py-16">
            <div class="flex flex-col justify-center">
                <p class="text-sm font-semibold uppercase text-emerald-800">Plant nursery and garden management platform</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-semibold tracking-normal text-slate-950 sm:text-5xl lg:text-6xl">
                    Sell plants, manage care plans, and support customers with AI-assisted gardening.
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                    A Laravel foundation for plant commerce, gardening services, subscriptions, reminders, expert communication, AI plant help, disease detection, and staff operations.
                </p>
                @guest
                    <div class="mt-8 rounded-lg border border-emerald-100 bg-emerald-50/80 p-4 shadow-sm sm:max-w-xl">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-emerald-900">New here?</p>
                                <p class="mt-1 text-sm text-emerald-800">Create an account to save plants, manage appointments, and keep your garden plans organized.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('customer.register') }}" class="rounded-md bg-emerald-900 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-emerald-800">Sign up</a>
                                <a href="{{ route('customer.login') }}" class="rounded-md border border-emerald-200 bg-white px-4 py-2 text-center text-sm font-semibold text-emerald-800 transition hover:border-emerald-800 hover:text-emerald-900">Log in</a>
                            </div>
                        </div>
                    </div>
                @endguest
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('store') }}" class="rounded-md bg-emerald-900 px-5 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Explore Store</a>
                    <a href="{{ route('ai-tools') }}" class="rounded-md border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-800 transition hover:border-emerald-800 hover:text-emerald-900">View AI Tools</a>
                </div>
                <dl class="mt-10 grid max-w-2xl grid-cols-3 gap-4">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <dt class="text-xs font-medium text-slate-500">Catalog</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-950">419+</dd>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <dt class="text-xs font-medium text-slate-500">Care Plans</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-950">3</dd>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <dt class="text-xs font-medium text-slate-500">AI Modules</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-950">5</dd>
                    </div>
                </dl>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-950 shadow-xl">
                <img
                    src="https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&w=1200&q=80"
                    alt="Organized plant nursery with potted plants"
                    class="h-72 w-full object-cover sm:h-96 lg:h-[34rem]"
                >
                <div class="grid gap-3 bg-slate-950 p-5 sm:grid-cols-3">
                    <div class="rounded-md bg-white/10 p-4 text-white">
                        <p class="text-xs text-emerald-200">Next visit</p>
                        <p class="mt-1 font-semibold">Balcony audit</p>
                    </div>
                    <div class="rounded-md bg-white/10 p-4 text-white">
                        <p class="text-xs text-emerald-200">Disease alert</p>
                        <p class="mt-1 font-semibold">2 reports pending</p>
                    </div>
                    <div class="rounded-md bg-white/10 p-4 text-white">
                        <p class="text-xs text-emerald-200">Inventory</p>
                        <p class="mt-1 font-semibold">Low: soil mix</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-stone-50 py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <x-section-heading eyebrow="Featured products" title="Fresh arrivals from the catalog" description="The latest active products from the store are now showing on the home page and can be explored in the full catalog." />
                <a href="{{ route('store') }}" class="text-sm font-semibold text-emerald-800 hover:text-emerald-900">View all products</a>
            </div>
            <div class="mt-10 grid gap-5 lg:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $isSaved = in_array($product['id'], $savedProductNames ?? []);
                        $previewText = trim($product['description'] ?? '');
                        $previewText = \Illuminate\Support\Str::limit($previewText, 90);
                    @endphp
                    <article class="group w-full max-w-[95%] justify-self-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                        <a href="{{ route('product.details', ['slug' => $product['slug']]) }}" class="block">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="h-52 w-full object-cover transition duration-200 group-hover:scale-[1.02]">
                            <div class="p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-800">{{ $product['category'] }}</p>
                                <div class="mt-2 flex items-start justify-between gap-4">
                                    <h3 class="text-lg font-semibold text-slate-950">{{ $product['name'] }}</h3>
                                    <p class="text-lg font-semibold text-slate-950">{{ $product['price'] }}</p>
                                </div>
                                <p class="mt-3 line-clamp-2 min-h-[2.75rem] text-sm leading-6 text-slate-600">{{ $previewText }}</p>
                                <p class="mt-3 text-sm text-slate-500">{{ $product['care'] }}</p>
                            </div>
                        </a>
                        <div class="border-t border-slate-100 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <button
                                    type="button"
                                    class="save-btn inline-flex h-10 w-10 items-center justify-center rounded-full border transition {{ $isSaved ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:text-emerald-700' }}"
                                    data-saved="{{ $isSaved ? 'true' : 'false' }}"
                                    data-product-id="{{ $product['id'] }}"
                                    data-product-name="{{ $product['name'] }}"
                                    data-product-category="{{ $product['category'] }}"
                                    data-price="{{ $product['price_value'] ?? 0 }}"
                                    data-image-url="{{ $product['image'] }}"
                                    data-toggle-url="{{ route('customer.saved-products.toggle') }}"
                                    data-csrf="{{ csrf_token() }}"
                                    title="{{ $isSaved ? 'Remove from saved' : 'Save to wishlist' }}"
                                    aria-label="{{ $isSaved ? 'Remove from saved' : 'Save to wishlist' }}"
                                >
                                    <svg class="h-4 w-4 save-icon-outline {{ $isSaved ? 'hidden' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20l-1.2-1.1C5.4 14.7 2 11.8 2 8.1 2 5.4 4.2 3.2 6.9 3.2c1.4 0 2.8.7 3.6 1.8.8-1.1 2.2-1.8 3.6-1.8 2.7 0 4.9 2.2 4.9 4.9 0 3.7-3.4 6.6-8.8 10.8L12 20z"/></svg>
                                    <svg class="h-4 w-4 save-icon-filled text-emerald-600 {{ $isSaved ? '' : 'hidden' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 20l-1.2-1.1C5.4 14.7 2 11.8 2 8.1 2 5.4 4.2 3.2 6.9 3.2c1.4 0 2.8.7 3.6 1.8.8-1.1 2.2-1.8 3.6-1.8 2.7 0 4.9 2.2 4.9 4.9 0 3.7-3.4 6.6-8.8 10.8L12 20z"/></svg>
                                </button>
                                <div class="flex flex-1 flex-wrap gap-2">
                                    <form action="{{ route('customer.cart.add') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                                        <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                                        <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                                        <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                                        <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                                        <button type="submit" class="w-full rounded-md bg-emerald-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-950">Add to Cart</button>
                                    </form>
                                    <form action="{{ route('customer.buy-now') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                                        <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                                        <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                                        <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                                        <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                                        <button type="submit" class="w-full rounded-md border border-emerald-900 px-3 py-2 text-sm font-semibold text-emerald-950 transition hover:bg-emerald-50">Buy Now</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            @if ($hasMoreProducts ?? false)
                <div class="mt-8 text-center">
                    <a href="{{ route('store') }}" class="inline-flex rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-800 hover:border-emerald-500 hover:text-emerald-700">Browse more products</a>
                </div>
            @endif
        </div>
    </section>

    <section class="bg-white py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Platform Modules" title="Built around the roadmap in your plan" description="The first Laravel build maps the product roadmap into clean web sections that can later connect to models, APIs, queues, payments, AI providers, and Flutter." />
            <div class="mt-10 grid gap-5 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Commerce', 'body' => 'Products, cart, checkout, payments, order history, and saved products.'],
                    ['title' => 'Services', 'body' => 'Garden setup, inspections, pest control, soil replacement, and emergency care.'],
                    ['title' => 'Subscriptions', 'body' => 'Basic, Standard, and Premium recurring maintenance with visit schedules.'],
                    ['title' => 'Operations', 'body' => 'Staff assignments, route planner, visit reports, inventory, and admin analytics.'],
                ] as $module)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-base font-semibold text-slate-950">{{ $module['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $module['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <script>
        (function () {
            function applySavedState(btn, saved) {
                btn.dataset.saved = saved ? 'true' : 'false';
                btn.title = saved ? 'Remove from saved' : 'Save to wishlist';
                btn.setAttribute('aria-label', saved ? 'Remove from saved' : 'Save to wishlist');

                if (saved) {
                    btn.classList.remove('border-slate-200', 'bg-white', 'text-slate-600', 'hover:border-emerald-200', 'hover:text-emerald-700');
                    btn.classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-700');
                } else {
                    btn.classList.remove('border-emerald-600', 'bg-emerald-50', 'text-emerald-700');
                    btn.classList.add('border-slate-200', 'bg-white', 'text-slate-600', 'hover:border-emerald-200', 'hover:text-emerald-700');
                }

                btn.querySelector('.save-icon-outline').classList.toggle('hidden', saved);
                btn.querySelector('.save-icon-filled').classList.toggle('hidden', !saved);
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.save-btn');
                if (!btn) return;

                e.preventDefault();
                e.stopPropagation();

                if (btn.disabled) return;
                btn.disabled = true;

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
                        // Keep state unchanged on failure.
                    })
                    .finally(function () {
                        btn.disabled = false;
                    });
            });
        })();
    </script>
@endsection
