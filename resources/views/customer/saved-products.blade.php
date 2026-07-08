@extends('layouts.app')

@section('title', 'Saved Products')

@section('content')
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading eyebrow="Saved Products" title="Plants and products you want to revisit" description="Store your favorite items for quick access later." />

        @if (session('success'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-slate-300 bg-stone-50 p-8 text-center text-slate-600">
                You have not saved any products yet.
            </div>
        @else
            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($items as $item)
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        @if ($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="mb-4 h-40 w-full rounded-md object-cover">
                        @endif
                        <h3 class="font-semibold text-slate-950">{{ $item->product_name }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $item->product_category ?? 'Garden item' }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-950">₹{{ number_format($item->price, 2) }}</span>
                            <form action="{{ route('customer.saved-products.remove', $item) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded border border-slate-300 px-3 py-1 text-sm font-medium text-slate-700">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
