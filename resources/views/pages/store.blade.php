@extends('layouts.app')

@section('title', 'Store')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Store" title="Plants, pots, tools and plant care essentials" description="A commerce foundation for the product catalog in the plan, ready for cart, checkout, reviews, specifications, and AI product questions." />

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('store') }}" class="rounded-full border px-4 py-2 text-sm font-semibold {{ empty($currentCategory) ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-slate-300 text-slate-700 hover:border-emerald-500 hover:text-emerald-700' }}">All products</a>
                @foreach (['Plants' => 'Plants', 'Indoor Plants' => 'Indoor Plants', 'Outdoor Plants' => 'Outdoor Plants'] as $query => $label)
                    <a href="{{ route('store', ['category' => $query]) }}" class="rounded-full border px-4 py-2 text-sm font-semibold {{ ($currentCategory ?? '') === $query ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-slate-300 text-slate-700 hover:border-emerald-500 hover:text-emerald-700' }}">{{ $label }}</a>
                @endforeach
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                @foreach ($products as $product)
                    <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <a href="{{ route('product.details', ['slug' => $product['slug'] ?? \Illuminate\Support\Str::slug($product['name'])]) }}" class="block cursor-pointer">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="h-56 w-full object-cover transition duration-200 hover:scale-[1.02]">
                            <div class="p-5">
                                <p class="text-xs font-semibold uppercase text-emerald-800">{{ $product['category'] }}</p>
                                <div class="mt-2 flex items-start justify-between gap-4">
                                    <h2 class="text-xl font-semibold text-slate-950">{{ $product['name'] }}</h2>
                                    <p class="text-lg font-semibold text-slate-950">{{ $product['price'] }}</p>
                                </div>
                                <p class="mt-3 text-sm text-slate-600">{{ $product['care'] }}</p>
                            </div>
                        </a>
                        <div class="p-5 pt-0">
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <form action="{{ route('customer.cart.add') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                                    <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                                    <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                                    <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                                    <button type="submit" class="w-full cursor-pointer rounded-md bg-emerald-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-950">Add to Cart</button>
                                </form>
                                <form action="{{ route('customer.saved-products.save') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                                    <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                                    <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                                    <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                                    <button type="submit" class="w-full cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-800 transition hover:border-emerald-500 hover:text-emerald-700" title="Save to wishlist">
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20l-1.2-1.1C5.4 14.7 2 11.8 2 8.1 2 5.4 4.2 3.2 6.9 3.2c1.4 0 2.8.7 3.6 1.8.8-1.1 2.2-1.8 3.6-1.8 2.7 0 4.9 2.2 4.9 4.9 0 3.7-3.4 6.6-8.8 10.8L12 20z"/></svg>
                                        Save
                                    </span>
                                </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <div class="rounded-lg border border-slate-200 bg-stone-50 p-5">
                        <h3 class="font-semibold text-slate-950">{{ $category['name'] }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $category['count'] }} prepared for filters, inventory, product details, 3D view, and recommendations.</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
