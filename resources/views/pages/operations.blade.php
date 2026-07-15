@extends('layouts.app')

@section('title', 'Operations')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Staff and Admin" title="Operational control for visits, inventory and analytics" description="A management surface for staff assignments, routes, attendance, visit reports, customer notes, inventory, payments, subscriptions, AI management, and analytics." />

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-stone-50 p-6">
                    <h2 class="text-xl font-semibold text-slate-950">Staff visit report</h2>
                    <div class="mt-5 grid gap-3">
                        @foreach (['Before visit images', 'After visit images', 'Health status', 'Treatments applied', 'Products used', 'Customer signature'] as $field)
                            <div class="rounded-md bg-white p-4 text-sm font-medium text-slate-700">{{ $field }}</div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-950 p-6 text-white">
                    <h2 class="text-xl font-semibold">Admin dashboard scope</h2>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach (['Users', 'Staff', 'Products', 'Inventory', 'Appointments', 'Subscriptions', 'Payments', 'Analytics'] as $area)
                            <div class="rounded-md bg-white/10 p-4 text-sm font-medium">{{ $area }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
