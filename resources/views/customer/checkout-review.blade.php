@extends('layouts.app')

@section('title', 'Checkout Review')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Checkout" title="Review your order" description="Confirm your delivery details before continuing to secure payment." />

            @if ($errors->any())
                <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Please check your delivery details.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('customer.checkout.pay') }}" method="POST" class="mt-8 grid gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                @csrf
                @foreach ($items as $item)
                    <input type="hidden" name="selected_items[]" value="{{ $item->id }}">
                @endforeach
                <input type="hidden" name="address_mode" id="address-mode" value="{{ old('address_mode', $hasSavedAddress ? 'saved' : 'new') }}">

                <div class="space-y-8">
                    <div class="rounded-lg border border-slate-200 bg-stone-50 p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Delivery details</p>
                                <h2 class="mt-1 text-xl font-semibold text-slate-950">Where should we deliver?</h2>
                            </div>
                            @if ($hasSavedAddress)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Saved address available</span>
                            @endif
                        </div>

                        @if ($hasSavedAddress)
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-start gap-3 rounded-md border border-emerald-300 bg-white p-4">
                                    <input type="radio" name="address_choice" value="saved" class="mt-1 address-choice" {{ old('address_mode', 'saved') === 'saved' ? 'checked' : '' }}>
                                    <span>
                                        <span class="block text-sm font-semibold text-slate-950">Use saved address</span>
                                        <span class="mt-1 block whitespace-pre-line text-sm leading-6 text-slate-600">{{ $user->name }}
{{ $user->house_no }}, {{ $user->street }}
{{ $user->city }}, {{ $user->state }} - {{ $user->pincode }}
{{ $user->country }}</span>
                                    </span>
                                </label>
                                <label class="flex cursor-pointer items-start gap-3 rounded-md border border-slate-200 bg-white p-4">
                                    <input type="radio" name="address_choice" value="new" class="mt-1 address-choice" {{ old('address_mode') === 'new' ? 'checked' : '' }}>
                                    <span>
                                        <span class="block text-sm font-semibold text-slate-950">Use a new address</span>
                                        <span class="mt-1 block text-sm leading-6 text-slate-600">Enter a different address for this order.</span>
                                    </span>
                                </label>
                            </div>
                        @endif

                        <div id="new-address-fields" class="mt-6 grid gap-5 {{ $hasSavedAddress && old('address_mode', 'saved') === 'saved' ? 'hidden' : '' }} sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="name" class="text-sm font-semibold text-slate-700">Full name <span class="text-red-500">*</span></label>
                                <input id="name" name="name" value="{{ old('name', $user->name) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="255">
                            </div>
                            <div>
                                <label for="house_no" class="text-sm font-semibold text-slate-700">House / flat number <span class="text-red-500">*</span></label>
                                <input id="house_no" name="house_no" value="{{ old('house_no', $user->house_no) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="255">
                            </div>
                            <div>
                                <label for="street" class="text-sm font-semibold text-slate-700">Street / area <span class="text-red-500">*</span></label>
                                <input id="street" name="street" value="{{ old('street', $user->street) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="255">
                            </div>
                            <div>
                                <label for="city" class="text-sm font-semibold text-slate-700">City <span class="text-red-500">*</span></label>
                                <input id="city" name="city" value="{{ old('city', $user->city) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="255">
                            </div>
                            <div>
                                <label for="state" class="text-sm font-semibold text-slate-700">State <span class="text-red-500">*</span></label>
                                <input id="state" name="state" value="{{ old('state', $user->state) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="255">
                            </div>
                            <div>
                                <label for="pincode" class="text-sm font-semibold text-slate-700">PIN / postal code <span class="text-red-500">*</span></label>
                                <input id="pincode" name="pincode" value="{{ old('pincode', $user->pincode) }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="20">
                            </div>
                            <div>
                                <label for="country" class="text-sm font-semibold text-slate-700">Country <span class="text-red-500">*</span></label>
                                <input id="country" name="country" value="{{ old('country', $user->country ?: 'India') }}" class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="255">
                            </div>
                        </div>

                        <div class="mt-6 border-t border-slate-200 pt-6">
                            <label for="phone" class="text-sm font-semibold text-slate-700">Contact phone number <span class="text-red-500">*</span></label>
                            <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" maxlength="30" placeholder="Phone number for delivery contact">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <aside class="h-fit rounded-lg border border-slate-200 bg-stone-50 p-6 lg:sticky lg:top-24">
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Order summary</p>
                    <div class="mt-5 space-y-4">
                        @foreach ($items as $item)
                            <div class="flex gap-3 border-b border-slate-200 pb-4">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="h-16 w-16 rounded-md object-cover">
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-950">{{ $item->product_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Qty {{ $item->quantity }} × ₹{{ number_format($item->price, 2) }}</p>
                                </div>
                                <p class="text-sm font-semibold text-slate-950">₹{{ number_format($item->price * $item->quantity, 2) }}</p>
                            </div>
                        @endforeach
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex justify-between"><span>Subtotal</span><span>₹{{ number_format($summary['subtotal'], 2) }}</span></div>
                            <div class="flex justify-between"><span>Delivery</span><span>₹{{ number_format($summary['delivery'], 2) }}</span></div>
                            <div class="flex justify-between"><span>GST (18%)</span><span>₹{{ number_format($summary['gst'], 2) }}</span></div>
                            <div class="flex justify-between border-t border-slate-200 pt-3 text-base font-semibold text-slate-950"><span>Total</span><span>₹{{ number_format($amount, 2) }}</span></div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="payment_method" class="text-sm font-semibold text-slate-700">Payment method</label>
                        <select id="payment_method" name="payment_method" class="mt-2 block w-full rounded-md border-slate-300 bg-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="upi">UPI</option>
                            <option value="qr">QR Code</option>
                            <option value="card">Card</option>
                            <option value="netbanking">Net Banking</option>
                            <option value="wallet">Wallet</option>
                            <option value="cash">Cash / Manual</option>
                        </select>
                    </div>
                    <button type="submit" class="mt-6 w-full rounded-md bg-emerald-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">Continue to payment</button>
                    <p class="mt-3 text-center text-xs leading-5 text-slate-500">Payment begins only after your delivery details pass server-side validation.</p>
                </aside>
            </form>
        </div>
    </section>

    @if ($hasSavedAddress)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mode = document.getElementById('address-mode');
                const fields = document.getElementById('new-address-fields');
                document.querySelectorAll('.address-choice').forEach(function (choice) {
                    choice.addEventListener('change', function () {
                        mode.value = this.value;
                        fields.classList.toggle('hidden', this.value !== 'new');
                    });
                });
            });
        </script>
    @endif
@endsection
