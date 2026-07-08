@extends('layouts.app')

@section('title', 'Cart')

@section('content')
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading eyebrow="Cart" title="Your selected plants and garden essentials" description="Review your items, update quantities or remove anything before checkout." />

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

        @if ($items->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-stone-50 p-8 text-center text-slate-600">
                Your cart is empty right now.
            </div>
        @else
            <div class="mt-8 grid gap-6 lg:grid-cols-[2fr_1fr]">
                <div class="space-y-4">
                    @foreach ($items as $item)
                        <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="h-16 w-16 rounded-md object-cover">
                                @endif
                                <div>
                                    <h3 class="font-semibold text-slate-950">{{ $item->product_name }}</h3>
                                    <p class="text-sm text-slate-600">{{ $item->product_category ?? 'Garden item' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <form action="{{ route('customer.cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <label class="text-sm text-slate-600" for="quantity-{{ $item->id }}">Qty</label>
                                    <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" value="{{ $item->quantity }}" class="w-16 rounded border border-slate-300 px-2 py-1 text-sm">
                                    <button type="submit" class="rounded border border-slate-300 px-3 py-1 text-sm font-medium text-slate-700">Update</button>
                                </form>
                                <form action="{{ route('customer.cart.remove', $item) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-rose-300 px-3 py-1 text-sm font-medium text-rose-700">Remove</button>
                                </form>
                            </div>
                            <div class="text-sm font-semibold text-slate-950">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="rounded-lg border border-slate-200 bg-stone-50 p-6">
                    <h3 class="text-lg font-semibold text-slate-950">Order summary</h3>
                    <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
                        <span>Subtotal</span>
                        <span>₹{{ number_format($total, 2) }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                        <span>Delivery</span>
                        <span>Free</span>
                    </div>
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between text-base font-semibold text-slate-950">
                            <span>Total</span>
                            <span>₹{{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                    <form action="{{ route('customer.checkout') }}" method="POST" class="mt-6">
                        @csrf
                        <button type="submit" class="w-full rounded-md bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Checkout</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
