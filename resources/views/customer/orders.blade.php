@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading eyebrow="Orders" title="Your order history" description="Track the status of every purchase made through the platform." />

        @if (session('success'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($orders->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-stone-50 p-8 text-center text-slate-600">
                You have not placed any orders yet.
            </div>
        @else
            <div class="mt-8 space-y-4">
                @foreach ($orders as $order)
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">{{ $order->order_number }}</p>
                                <p class="mt-1 text-sm text-slate-600">Placed on {{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="text-sm text-slate-600">
                                <span class="font-semibold text-slate-950">Status:</span> {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </div>
                            <div class="text-sm font-semibold text-slate-950">₹{{ number_format($order->total_amount, 2) }}</div>
                        </div>

                        <div class="mt-6 space-y-3">
                            @forelse ($order->items as $item)
                                <div class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center">
                                    <div class="flex h-20 w-full items-center justify-center overflow-hidden rounded-md bg-slate-100 sm:h-24 sm:w-24">
                                        @if (! empty($item->product?->image_url))
                                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name ?? 'Product image' }}" class="h-full w-full object-cover" />
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-xs text-slate-500">No Image</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-slate-950">{{ $item->product->name ?? 'Product unavailable' }}</p>
                                        <p class="mt-1 text-sm text-slate-600">Quantity: {{ $item->quantity }}</p>
                                        <p class="mt-1 text-sm text-slate-600">Price: ₹{{ number_format($item->price, 2) }}</p>
                                        <p class="mt-1 text-sm text-slate-500">Subtotal: ₹{{ number_format($item->subtotal, 2) }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                                    No products available for this order.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
