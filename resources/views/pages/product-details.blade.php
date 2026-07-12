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
                            <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                            <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                            <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                            <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                            <button type="submit" class="rounded-md bg-emerald-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-950">Add to Cart</button>
                        </form>
                        <form action="{{ route('customer.saved-products.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                            <input type="hidden" name="product_category" value="{{ $product['category'] }}">
                            <input type="hidden" name="price" value="{{ $product['price_value'] ?? 0 }}">
                            <input type="hidden" name="image_url" value="{{ $product['image'] }}">
                            <button type="submit" class="rounded-md border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-800 transition hover:border-emerald-500 hover:text-emerald-700">Save</button>
                        </form>
                        <form action="{{ route('customer.buy-now') }}" method="POST">
                            @csrf
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
</script>
@endsection
