@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-2xl px-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-900">Change Password</h1>
            <p class="mt-2 text-sm text-slate-600">You are required to choose a new password before continuing.</p>
            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif
            <form method="POST" action="{{ route('customer.change-password.post') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">New Password</label>
                    <input type="password" name="password" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                </div>
                <button type="submit" class="w-full rounded-xl bg-emerald-900 px-4 py-3 text-sm font-semibold text-white">Update Password</button>
            </form>
        </div>
    </div>
</section>
@endsection
