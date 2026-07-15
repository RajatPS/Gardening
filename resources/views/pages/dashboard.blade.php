@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Customer Dashboard" title="Account, plant care and purchase workspace" description="A customer dashboard shell for orders, subscriptions, appointments, saved products, plant reports, AI history, payments, addresses, and profile management." />

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $dashboardLinks = [
                        ['label' => 'Orders', 'href' => route('customer.orders'), 'description' => 'Review your placed orders and status updates.'],
                        ['label' => 'Cart', 'href' => route('customer.cart'), 'description' => 'Manage items before checkout.'],
                        ['label' => 'Saved Products', 'href' => route('customer.saved-products'), 'description' => 'Keep products you want to revisit.'],
                        ['label' => 'Profile', 'href' => route('customer.profile'), 'description' => 'Update your profile and delivery information.'],
                    ];
                @endphp

                @foreach ($dashboardLinks as $link)
                    <a href="{{ $link['href'] }}" class="rounded-lg border border-slate-200 bg-stone-50 p-5 transition hover:border-emerald-800 hover:bg-white">
                        <h2 class="font-semibold text-slate-950">{{ $link['label'] }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $link['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
