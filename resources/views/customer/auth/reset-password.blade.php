@extends('layouts.app')

@section('title', ($loginOtp ?? false) ? 'Verify Login' : 'Reset Password')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-2xl px-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-900">{{ ($loginOtp ?? false) ? 'Verify your login' : 'Reset Password' }}</h1>
            @if ($loginOtp ?? false)
                <p class="mt-2 text-sm text-slate-600">Enter the verification code sent to your phone ending in {{ substr((string) $phone, -4) }}.</p>
            @endif
            <form method="POST" action="{{ ($loginOtp ?? false) ? route('customer.login.otp.verify') : route('customer.reset-password.post') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ ($loginOtp ?? false) ? 'Login verification code' : 'Verification code' }}</label>
                    <input type="text" name="otp" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                </div>
                @if (! ($loginOtp ?? false))
                    <input type="hidden" name="email" value="{{ $email }}">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">New Password</label>
                        <input type="password" name="password" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                    </div>
                @endif
                <button type="submit" class="w-full rounded-xl bg-emerald-900 px-4 py-3 text-sm font-semibold text-white">{{ ($loginOtp ?? false) ? 'Verify login' : 'Reset Password' }}</button>
            </form>
        </div>
    </div>
</section>
@endsection
