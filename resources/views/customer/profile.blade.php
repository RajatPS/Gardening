@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading eyebrow="Profile" title="Your account details" description="Keep your contact details and delivery information updated." />

        @if(session('success'))
            <div class="mt-4 rounded-md bg-green-50 p-4 border border-green-200">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('customer.profile.update') }}" method="POST" class="mt-8 rounded-lg border border-slate-200 bg-stone-50 p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user?->name) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Email</label>
                    <input type="email" name="email" id="email" value="{{ $user?->email }}" class="mt-2 block w-full rounded-md border-slate-200 bg-slate-100 text-slate-500 shadow-sm sm:text-sm cursor-not-allowed" readonly>
                    <p class="mt-1 text-xs text-slate-500">Email cannot be changed.</p>
                </div>

                <div>
                    <label for="phone" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user?->phone) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="house_no" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">House/Flat Number <span class="text-red-500">*</span></label>
                    <input type="text" name="house_no" id="house_no" value="{{ old('house_no', $user?->house_no) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    @error('house_no')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="street" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Street/Area <span class="text-red-500">*</span></label>
                    <input type="text" name="street" id="street" value="{{ old('street', $user?->street) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    @error('street')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">City <span class="text-red-500">*</span></label>
                    <input type="text" name="city" id="city" value="{{ old('city', $user?->city) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="state" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">State <span class="text-red-500">*</span></label>
                    <input type="text" name="state" id="state" value="{{ old('state', $user?->state) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    @error('state')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pincode" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">PIN Code <span class="text-red-500">*</span></label>
                    <input type="text" name="pincode" id="pincode" value="{{ old('pincode', $user?->pincode) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    @error('pincode')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="country" class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Country <span class="text-red-500">*</span></label>
                    <input type="text" name="country" id="country" value="{{ old('country', $user?->country) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    @error('country')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-200">
                <a href="{{ route('home') }}" class="text-sm font-semibold leading-6 text-slate-900 hover:text-slate-600">Skip for Now</a>
                <button type="submit" class="rounded-md bg-emerald-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800 onclick-disable" onclick="this.form.submit(); this.disabled=true; this.innerText='Saving...';">
                    Save Details
                </button>
            </div>
        </form>
    </div>
</section>
@endsection
