@extends('layouts.app')

@section('title', 'Store')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Store" title="Plants, pots, tools and plant care essentials" description="A commerce foundation for the product catalog in the plan, ready for cart, checkout, reviews, specifications, and AI product questions." />

            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                @foreach ($products as $product)
                    <article class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="h-56 w-full object-cover">
                        <div class="p-5">
                            <p class="text-xs font-semibold uppercase text-emerald-800">{{ $product['category'] }}</p>
                            <div class="mt-2 flex items-start justify-between gap-4">
                                <h2 class="text-xl font-semibold text-slate-950">{{ $product['name'] }}</h2>
                                <p class="text-lg font-semibold text-slate-950">{{ $product['price'] }}</p>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">{{ $product['care'] }}</p>
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <form action="{{ route('customer.cart.add') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                                    <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                                    <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                                    <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                                    <button type="submit" class="w-full rounded-md bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Add to Cart</button>
                                </form>
                                <form action="{{ route('customer.saved-products.save') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                                    <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                                    <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                                    <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                                    <button type="submit" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-800">Save</button>
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
