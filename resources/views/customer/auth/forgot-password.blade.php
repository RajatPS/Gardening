@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-2xl px-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-900">Forgot Password</h1>
            <p class="mt-2 text-sm text-slate-600">Enter your email and phone number to receive a verification code.</p>
            @if(session('status'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('customer.forgot-password.post') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email address</label>
                    <input type="email" name="email" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Phone number</label>
                    <input type="text" name="phone" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm" placeholder="e.g. 9876543210">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Country code</label>
                    <input type="text" name="country_code" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm" placeholder="e.g. 91">
                </div>
                <button type="submit" class="w-full rounded-xl bg-emerald-900 px-4 py-3 text-sm font-semibold text-white">Send Verification Code</button>
            </form>
        </div>
    </div>
</section>
@endsection
