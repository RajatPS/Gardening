@extends('layouts.app')

@section('title', 'Checkout Payment')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 shadow-sm">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase text-emerald-800">Secure checkout</p>
                        <h1 class="mt-2 text-3xl font-semibold text-slate-950">Complete your purchase</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                            Choose any payment option below. This flow supports UPI, QR code, cards, net banking, wallets, and a secure local fallback when Razorpay is not configured.
                        </p>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-white px-5 py-4 text-right shadow-sm">
                        <p class="text-sm text-slate-500">Amount due</p>
                        <p class="mt-1 text-3xl font-semibold text-emerald-900">₹{{ number_format($amount, 2) }}</p>
                        <p class="text-sm text-slate-500">{{ $currency }}</p>
                    </div>
                </div>

                <form action="{{ route('customer.checkout.pay') }}" method="POST" class="mt-8 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    @csrf

                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-950">Select a payment method</h2>
                        <input type="hidden" name="payable_amount" value="{{ $summary['grand_total'] ?? $amount }}">
                        @foreach ($items as $item)
                            <input type="hidden" name="selected_items[]" value="{{ $item->id }}">
                        @endforeach
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ([['value' => 'upi', 'label' => 'UPI'], ['value' => 'qr', 'label' => 'QR Code'], ['value' => 'card', 'label' => 'Cards'], ['value' => 'netbanking', 'label' => 'Net Banking'], ['value' => 'wallet', 'label' => 'Wallets'], ['value' => 'cash', 'label' => 'Cash/Manual']] as $method)
                                <label class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-emerald-800 hover:text-emerald-900">
                                    <span>{{ $method['label'] }}</span>
                                    <input type="radio" name="payment_method" value="{{ $method['value'] }}" class="h-4 w-4 border-slate-300 text-emerald-900 focus:ring-emerald-800" {{ $loop->first ? 'checked' : '' }}>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-950">Order summary</h2>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span>Items</span>
                                <span class="font-semibold text-slate-950">{{ $items->count() }} {{ Str::plural('product', $items->count()) }}</span>
                            </div>
                            @foreach ($items as $item)
                                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                    <span class="truncate max-w-[160px]">{{ $item->product_name }} × {{ $item->quantity }}</span>
                                    <span class="font-semibold text-slate-950">₹{{ number_format($item->price * $item->quantity, 2) }}</span>
                                </div>
                            @endforeach
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span>Delivery</span>
                                <span class="font-semibold text-slate-950">₹{{ number_format($summary['delivery'] ?? 0, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                <span>GST (18%)</span>
                                <span class="font-semibold text-slate-950">₹{{ number_format($summary['gst'] ?? 0, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Total</span>
                                <span class="font-semibold text-slate-950">₹{{ number_format($amount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Gateway</span>
                                <span class="font-semibold text-slate-950">{{ ucfirst($paymentGateway) }}</span>
                            </div>
                        </div>

                        <button type="submit" class="mt-6 w-full rounded-md bg-emerald-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            Pay Now
                        </button>

                        <p class="mt-3 text-xs leading-6 text-slate-500">
                            Your order will be confirmed immediately after a successful payment. If Razorpay is not configured, the app will complete the payment using the built-in fallback flow.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    @if (!empty($order) && ($paymentGateway ?? '') === 'razorpay')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const options = {
                    key: '{{ env('RAZORPAY_KEY_ID') }}',
                    amount: '{{ $order['amount'] }}',
                    currency: '{{ $currency }}',
                    name: 'Gardening',
                    description: 'Cart checkout',
                    order_id: '{{ $order['id'] }}',
                    handler: function (response) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('customer.checkout.verify') }}';
                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';
                        form.appendChild(csrf);

                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = 'payment_method';
                        method.value = '{{ $paymentMethod ?? 'card' }}';
                        form.appendChild(method);

                        const paymentId = document.createElement('input');
                        paymentId.type = 'hidden';
                        paymentId.name = 'razorpay_payment_id';
                        paymentId.value = response.razorpay_payment_id;
                        form.appendChild(paymentId);

                        const orderId = document.createElement('input');
                        orderId.type = 'hidden';
                        orderId.name = 'razorpay_order_id';
                        orderId.value = response.razorpay_order_id;
                        form.appendChild(orderId);

                        const signature = document.createElement('input');
                        signature.type = 'hidden';
                        signature.name = 'razorpay_signature';
                        signature.value = response.razorpay_signature;
                        form.appendChild(signature);

                        document.body.appendChild(form);
                        form.submit();
                    },
                    modal: {
                        ondismiss: function () {
                            // User closed the Razorpay modal without paying — redirect back to cart
                            window.location.href = '{{ route('customer.cart') }}';
                        }
                    },
                    prefill: {
                        name: '{{ auth()->user()->name ?? '' }}',
                        email: '{{ auth()->user()->email ?? '' }}'
                    },
                    theme: {
                        color: '#065f46'
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();
            });
        </script>
    @endif
@endsection
