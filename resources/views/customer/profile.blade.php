@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading eyebrow="Profile" title="Your account details" description="Keep your contact details and delivery information updated." />

        <div class="mt-8 rounded-lg border border-slate-200 bg-stone-50 p-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Name</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $user?->name ?? 'Guest' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Email</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $user?->email ?? 'Not available' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Phone</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $user?->phone ?? 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Address</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $user?->address ?? 'Not provided' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
