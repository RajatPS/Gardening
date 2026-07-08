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
