@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<section class="bg-gradient-to-br from-emerald-50 via-white to-stone-100 py-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl lg:grid-cols-[1.1fr_0.9fr]">
            <div class="bg-emerald-900 p-8 text-white sm:p-10 lg:p-12">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-200">Join Gardening</p>
                <h1 class="mt-4 text-3xl font-semibold sm:text-4xl">Create your account in minutes</h1>
                <p class="mt-4 max-w-md text-sm leading-6 text-emerald-100/90">Sign up to place orders, save products, and receive helpful reminders for your plants.</p>
                <div class="mt-8 rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                    <p class="text-sm font-medium">What you get</p>
                    <ul class="mt-3 space-y-2 text-sm text-emerald-50/90">
                        <li>• Secure profile and address details</li>
                        <li>• Faster cart checkout</li>
                        <li>• Access to your order history</li>
                    </ul>
                </div>
            </div>

            <div class="p-8 sm:p-10 lg:p-12">
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-3">
                    <a href="{{ route('customer.google.redirect', ['redirect' => $redirect]) }}" class="flex items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition hover:border-emerald-500 hover:text-emerald-800">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#4285F4" d="M21.6 12.23c0-.78-.07-1.53-.2-2.25H12v4.26h5.38a4.6 4.6 0 0 1-2 3.02v2.5h3.24c1.9-1.75 2.98-4.33 2.98-7.53Z"/>
                            <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.62-2.43l-3.24-2.5c-.9.6-2.05.95-3.38.95-2.6 0-1.76-4.8-5.59-4.12H3.07v2.58A10 10 0 0 0 12 22Z"/>
                            <path fill="#FBBC05" d="M6.41 13.9a6.02 6.02 0 0 1 0-3.8V7.52H3.07a10 10 0 0 0 0 12.76l3.34-2.38Z"/>
                            <path fill="#EA4335" d="M12 6.05c1.47 0 2.79.5 3.83 1.49l2.87-2.87A9.96 9.96 0 0 0 12 2a10 10 0 0 0-8.93 5.52l3.34 2.58C7.2 7.81 9.4 6.05 12 6.05Z"/>
                        </svg>
                        <span>Continue with Google</span>
                    </a>
                </div>

                <div class="my-6 flex items-center gap-3">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">or</span>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <form method="POST" action="{{ route('customer.register.post') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Full name</label>
                        <input id="name" name="name" type="text" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:outline-none" value="{{ old('name') }}">
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email address</label>
                        <input id="email" name="email" type="email" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:outline-none" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                        <input id="password" name="password" type="password" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:outline-none">
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-emerald-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Create account</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
