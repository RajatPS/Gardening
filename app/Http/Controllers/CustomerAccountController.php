<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SavedProduct;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CustomerAccountController extends Controller
{
    public function cart()
    {
        $items = $this->cartItemsQuery()->get();
        $savedProductIds = $this->savedProductsQuery()->pluck('product_id')->filter()->all();
        $productIdsByName = Product::query()
            ->whereIn('name', $items->pluck('product_name')->filter()->all())
            ->pluck('id', 'name')
            ->all();

        $cartItemProductIds = [];
        foreach ($items as $item) {
            $cartItemProductIds[$item->id] = $productIdsByName[$item->product_name] ?? null;
        }

        return view('customer.cart', [
            'items' => $items,
            'savedProductIds' => $savedProductIds,
            'cartItemProductIds' => $cartItemProductIds,
            'total' => $items->sum(fn ($item) => $item->price * $item->quantity),
        ]);
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $this->upsertCartItem($product, (int) ($validated['quantity'] ?? 1));

        return redirect()->back()->with('success', 'Item added to cart.');
    }

    public function buyNow(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if (! auth()->check()) {
            return redirect()->route('customer.login', ['redirect' => route('customer.cart')]);
        }

        $product = Product::findOrFail($validated['product_id']);
        $this->upsertCartItem($product, (int) ($validated['quantity'] ?? 1));

        return redirect()->route('customer.cart')->with('success', 'Item prepared for checkout.');
    }

    public function updateCart(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->update([
            'quantity' => (int) $validated['quantity'],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated.',
                'item_id' => $cartItem->id,
                'item_total' => number_format($cartItem->price * $cartItem->quantity, 2),
            ]);
        }

        return redirect()->back()->with('success', 'Cart updated.');
    }

    public function removeCartItem(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);
        $cartItem->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    public function orders()
    {
        $query = Order::query()->latest();

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->whereRaw('1 = 0');
        }

        $orders = $query->paginate(8);

        return view('customer.orders', compact('orders'));
    }

    public function checkout(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('customer.login', ['redirect' => route('customer.cart')]);
        }

        $items = $this->selectedCartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Please select at least one product to continue.');
        }

        $summary = $this->buildCheckoutSummary($items);
        $currency = config('app.currency', env('APP_CURRENCY', 'INR'));
        session(['checkout_selected_ids' => $items->pluck('id')->all()]);

        $requestedAmount = $request->input('payable_amount');
        if ($requestedAmount !== null && abs((float) $requestedAmount - $summary['grand_total']) > 0.01) {
            return redirect()->route('customer.cart')->with('error', 'The payable amount could not be validated. Please try again.');
        }

        if (! $this->paymentGatewayEnabled()) {
            return $this->completeCartOrderDirectly($items, $summary, $request);
        }

        return view('customer.cart-payment', [
            'items'          => $items,
            'amount'         => $summary['grand_total'],
            'currency'       => $currency,
            'paymentGateway' => 'razorpay',
            'paymentMethod'  => null,
            'summary'        => $summary,
        ]);
    }

    public function checkoutPay(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('customer.login', ['redirect' => route('customer.cart')]);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:upi,qr,card,netbanking,wallet,cash'],
        ]);

        $items = $this->selectedCartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Please select at least one product to continue.');
        }

        $summary = $this->buildCheckoutSummary($items);
        $currency = config('app.currency', env('APP_CURRENCY', 'INR'));
        $paymentMethod = $validated['payment_method'];
        session(['checkout_selected_ids' => $items->pluck('id')->all()]);

        $requestedAmount = $request->input('payable_amount');
        if ($requestedAmount !== null && abs((float) $requestedAmount - $summary['grand_total']) > 0.01) {
            return redirect()->route('customer.cart')->with('error', 'The payable amount could not be validated. Please try again.');
        }

        if ($this->paymentGatewayEnabled()) {
            $order = $this->createRazorpayOrder($summary['amount_in_paise'], $currency, 'cart-checkout', $paymentMethod);

            if ($order['success']) {
                $transaction = Transaction::create([
                    'user_id'         => auth()->id(),
                    'subscription_id' => null,
                    'order_id'        => null,
                    'transaction_id'  => $order['data']['id'],
                    'amount'          => $summary['grand_total'],
                    'payment_method'  => $paymentMethod,
                    'payment_gateway' => 'razorpay',
                    'status'          => 'pending',
                    'payment_details' => [
                        'gateway'  => 'razorpay',
                        'order_id' => $order['data']['id'],
                        'type'     => 'cart_checkout',
                    ],
                    'gateway_response' => $order['data'],
                    'response'         => null,
                ]);

                return view('customer.cart-payment', [
                    'items'          => $items,
                    'amount'         => $summary['grand_total'],
                    'currency'       => $currency,
                    'paymentGateway' => 'razorpay',
                    'paymentMethod'  => $paymentMethod,
                    'order'          => $order['data'],
                    'transactionId'  => $transaction->id,
                    'summary'        => $summary,
                ]);
            }

            return redirect()->route('customer.cart')->with('error', 'Could not initiate payment. Please try again.');
        }

        // Fallback: mock/cash flow
        return $this->completeCartOrderDirectly($items, $summary, $request);
    }

    public function checkoutVerify(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('customer.login', ['redirect' => route('customer.cart')]);
        }

        $validated = $request->validate([
            'payment_method'      => ['required', 'string', 'in:upi,qr,card,netbanking,wallet,cash'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id'   => ['required', 'string'],
            'razorpay_signature'  => ['required', 'string'],
        ]);

        $transaction = Transaction::where('transaction_id', $validated['razorpay_order_id'])->latest()->first();

        if (! $transaction) {
            return redirect()->route('customer.cart')->with('error', 'We could not find the payment record. Please try again.');
        }

        $signature = $this->buildSignature($validated['razorpay_order_id'], $validated['razorpay_payment_id'], $validated['razorpay_signature']);

        if (! $signature['valid']) {
            $transaction->update([
                'status'          => 'failed',
                'response'        => 'Signature verification failed.',
                'payment_details' => [
                    'gateway'    => 'razorpay',
                    'payment_id' => $validated['razorpay_payment_id'],
                    'order_id'   => $validated['razorpay_order_id'],
                ],
            ]);

            return redirect()->route('customer.cart')->with('error', 'Payment could not be verified. Your cart is intact — please try again.');
        }

        // Payment verified — create order inside a DB transaction
        $items = $this->selectedCartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Please select at least one product to continue.');
        }

        $summary = $this->buildCheckoutSummary($items);

        DB::transaction(function () use ($items, $summary, $validated, $transaction, $request) {
            $order = Order::create([
                'user_id'          => auth()->id(),
                'order_number'     => 'ORD-' . strtoupper(uniqid()),
                'total_amount'     => $summary['grand_total'],
                'status'           => 'pending',
                'payment_status'   => 'paid',
                'shipping_address' => auth()->user()->address ?? $request->input('address', 'Address not provided'),
                'notes'            => 'Placed from cart — paid via Razorpay',
            ]);

            foreach ($items as $item) {
                $product = $item->product_id ? Product::find($item->product_id) : Product::where('name', $item->product_name)->first();

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product?->id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->price,
                    'subtotal'   => $item->price * $item->quantity,
                ]);
            }

            $transaction->update([
                'order_id'        => $order->id,
                'status'          => 'completed',
                'payment_method'  => $validated['payment_method'],
                'response'        => 'Payment verified successfully.',
                'payment_details' => [
                    'gateway'    => 'razorpay',
                    'payment_id' => $validated['razorpay_payment_id'],
                    'order_id'   => $validated['razorpay_order_id'],
                    'signature'  => $validated['razorpay_signature'],
                    'type'       => 'cart_checkout',
                ],
            ]);

            CartItem::whereIn('id', $items->pluck('id')->all())->delete();
        });

        return redirect()->route('customer.orders')->with('success', 'Order placed successfully. Payment completed.');
    }

    public function savedProducts()
    {
        $items = $this->savedProductsQuery()->get();

        return view('customer.saved-products', [
            'items' => $items,
        ]);
    }

    public function saveProduct(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $scope = $this->scopeAttributes();

        // Require authenticated user for saved product operations
        abort_if(empty($scope['user_id']), 403);

        $query = SavedProduct::query()
            ->where('product_id', $product->id)
            ->where('user_id', $scope['user_id']);

        $existing = $query->first();

        if ($existing) {
            $existing->delete();
            return redirect()->back()->with('success', 'Product removed from your saved list.');
        }

        SavedProduct::firstOrCreate(
            [
                'user_id'    => $scope['user_id'],
                'product_id' => $product->id,
            ],
            [
                'product_name'     => $product->name,
                'product_category' => $product->category,
                'price'            => (float) $product->price,
                'image_url'        => $this->resolveProductImageUrl($product),
                'metadata'         => ['saved_from' => 'store'],
            ]
        );

        return redirect()->back()->with('success', 'Product saved to your list.');
    }

    public function toggleSavedProduct(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $scope = $this->scopeAttributes();

        // Require authenticated user for saved product operations
        abort_if(empty($scope['user_id']), 403);

        $query = SavedProduct::query()
            ->where('product_id', $product->id)
            ->where('user_id', $scope['user_id']);

        $existing = $query->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'saved'        => false,
                'product_id'   => $product->id,
                'message'      => 'Product removed from your saved list.',
            ]);
        }

        SavedProduct::firstOrCreate(
            [
                'user_id'    => $scope['user_id'],
                'product_id' => $product->id,
            ],
            [
                'product_name'     => $product->name,
                'product_category' => $product->category,
                'price'            => (float) $product->price,
                'image_url'        => $this->resolveProductImageUrl($product),
                'metadata'         => ['saved_from' => 'store'],
            ]
        );

        return response()->json([
            'saved'        => true,
            'product_id'   => $product->id,
            'message'      => 'Product saved to your list.',
        ]);
    }

    public function syncSavedProducts(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.product_name' => 'required|string',
            'products.*.product_category' => 'nullable|string',
            'products.*.price' => 'nullable|numeric',
            'products.*.image_url' => 'nullable|string',
        ]);

        $scope = $this->scopeAttributes();
        $productIds = collect($validated['products'])->pluck('product_id')->filter()->unique()->values();
        $existingIds = SavedProduct::where('user_id', $scope['user_id'])
            ->whereIn('product_id', $productIds->all())
            ->pluck('product_id')
            ->all();

        $created = 0;
        foreach ($validated['products'] as $productData) {
            if (in_array($productData['product_id'], $existingIds, true)) {
                continue;
            }

            SavedProduct::firstOrCreate(
                [
                    'user_id'    => $scope['user_id'],
                    'product_id' => $productData['product_id'],
                ],
                [
                    'product_name'     => $productData['product_name'],
                    'product_category' => $productData['product_category'] ?? null,
                    'price'            => (float) ($productData['price'] ?? 0),
                    'image_url'        => $productData['image_url'] ?? null,
                    'metadata'         => ['saved_from' => 'guest_sync'],
                ]
            );

            $created++;
        }

        return response()->json([
            'synced' => true,
            'created' => $created,
        ]);
    }

    public function removeSavedProduct(SavedProduct $savedProduct)
    {
        $this->authorizeSavedProduct($savedProduct);

        $savedProduct->delete();

        return redirect()->back()->with('success', 'Saved product removed.');
    }

    public function profile()
    {
        return view('customer.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'house_no' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'country' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    private function cartItemsQuery()
    {
        $query = CartItem::query();
        $scope = $this->scopeAttributes();

        if ($scope['user_id'] ?? null) {
            $query->where(function ($query) use ($scope): void {
                $query->where('user_id', $scope['user_id']);
                $query->orWhere('session_id', $scope['session_id']);
            });
        } else {
            $query->where('session_id', $scope['session_id']);
        }

        return $query->where(function ($query): void {
            $query->whereNull('metadata->saved_for_later')->orWhere('metadata->saved_for_later', false);
        })->orderByDesc('created_at');
    }

    private function savedForLaterItemsQuery()
    {
        $query = CartItem::query();
        $scope = $this->scopeAttributes();

        if ($scope['user_id'] ?? null) {
            $query->where(function ($query) use ($scope): void {
                $query->where('user_id', $scope['user_id']);
                $query->orWhere('session_id', $scope['session_id']);
            });
        } else {
            $query->where('session_id', $scope['session_id']);
        }

        return $query->where('metadata->saved_for_later', true)->orderByDesc('created_at');
    }

    private function selectedCartItems(Request $request)
    {
        $selectedIds = $request->input('selected_items', session('checkout_selected_ids', []));
        $selectedIds = array_filter(array_map('intval', (array) $selectedIds));

        if (empty($selectedIds)) {
            return collect();
        }

        return $this->cartItemsQuery()->whereIn('id', $selectedIds)->get();
    }

    private function upsertCartItem(Product $product, int $quantity): void
    {
        $scope = $this->scopeAttributes();

        DB::transaction(function () use ($product, $quantity, $scope) {
            $existingItem = $this->cartItemsQuery()
                ->where(function ($query) use ($product) {
                    $query->where('product_id', $product->id)
                          ->orWhere('product_name', $product->name);
                })
                ->lockForUpdate()
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $quantity);

                if (! $existingItem->product_id) {
                    $existingItem->update(['product_id' => $product->id]);
                }

                return;
            }

            CartItem::create([
                'user_id' => $scope['user_id'] ?? null,
                'session_id' => $scope['session_id'] ?? null,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_category' => $product->category,
                'price' => (float) $product->price,
                'quantity' => $quantity,
                'image_url' => $this->resolveProductImageUrl($product),
                'metadata' => [
                    'added_from' => 'store',
                ],
            ]);
        });
    }

    private function savedProductsQuery()
    {
        $query = SavedProduct::query();
        $scope = $this->scopeAttributes();

        if ($scope['user_id'] ?? null) {
            $query->where('user_id', $scope['user_id']);
        } else {
            $query->where('session_id', $scope['session_id']);
        }

        return $query->orderByDesc('created_at');
    }

    private function scopeAttributes(): array
    {
        return [
            'user_id' => auth()->check() ? auth()->id() : null,
            'session_id' => session()->getId(),
        ];
    }

    private function authorizeCartItem(CartItem $cartItem): void
    {
        $scope = $this->scopeAttributes();

        if ($scope['user_id']) {
            abort_if($cartItem->user_id !== $scope['user_id'] && $cartItem->session_id !== $scope['session_id'], 403);
            return;
        }

        abort_if($cartItem->session_id !== $scope['session_id'], 403);
    }

    private function authorizeSavedProduct(SavedProduct $savedProduct): void
    {
        $scope = $this->scopeAttributes();

        if ($scope['user_id']) {
            abort_if($savedProduct->user_id !== $scope['user_id'] && $savedProduct->session_id !== $scope['session_id'], 403);
            return;
        }

        abort_if($savedProduct->session_id !== $scope['session_id'], 403);
    }

    private function resolveProductImageUrl(Product $product): ?string
    {
        return $product->image_url;
    }

    private function completeCartOrderDirectly($items, array $summary, Request $request)
    {
        $order = null;

        DB::transaction(function () use ($items, $summary, $request, &$order) {
            $order = Order::create([
                'user_id'          => auth()->id(),
                'order_number'     => 'ORD-' . strtoupper(uniqid()),
                'total_amount'     => $summary['grand_total'],
                'status'           => 'pending',
                'payment_status'   => 'paid',
                'shipping_address' => auth()->user()->address ?? $request->input('address', 'Address not provided'),
                'notes'            => $request->input('notes', 'Placed from cart'),
            ]);

            foreach ($items as $item) {
                $product = Product::where('name', $item->product_name)->first();

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product?->id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->price,
                    'subtotal'   => $item->price * $item->quantity,
                ]);
            }

            Transaction::create([
                'user_id'          => auth()->id(),
                'order_id'         => $order->id,
                'transaction_id'   => 'local-' . Str::uuid()->toString(),
                'amount'           => $summary['grand_total'],
                'payment_method'   => 'cash',
                'payment_gateway'  => 'mock',
                'status'           => 'completed',
                'payment_details'  => ['gateway' => 'mock', 'type' => 'cart_checkout'],
                'gateway_response' => ['gateway' => 'mock', 'message' => 'Payment completed using the local fallback flow.'],
                'response'         => 'Payment completed using the local fallback flow.',
            ]);

            CartItem::whereIn('id', $items->pluck('id')->all())->delete();
        });

        return redirect()->route('customer.orders')->with('success', 'Order placed successfully.');
    }

    private function paymentGatewayEnabled(): bool
    {
        return ! empty(env('RAZORPAY_KEY_ID')) && ! empty(env('RAZORPAY_KEY_SECRET')) && str_contains(strtolower((string) env('PAYMENT_GATEWAY', 'razorpay')), 'razorpay');
    }

    private function createRazorpayOrder(int $amountInPaise, string $currency, string $label, string $paymentMethod): array
    {
        $keyId = env('RAZORPAY_KEY_ID');
        $secret = env('RAZORPAY_KEY_SECRET');

        if (empty($keyId) || empty($secret)) {
            return ['success' => false, 'message' => 'Razorpay credentials are not configured.'];
        }

        $response = Http::withBasicAuth($keyId, $secret)
            ->asForm()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount'   => $amountInPaise,
                'currency' => $currency,
                'receipt'  => 'cart-' . Str::slug($label) . '-' . time(),
                'notes'    => [
                    'type'           => 'cart_checkout',
                    'payment_method' => $paymentMethod,
                    'user_id'        => auth()->id(),
                ],
            ]);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'message' => $response->body()];
    }

    private function buildCheckoutSummary($items): array
    {
        $subtotal = (float) collect($items)->sum(fn ($item) => ((float) ($item->price ?? 0)) * ((int) ($item->quantity ?? 1)));
        $deliveryCharges = 20.0;
        $gstAmount = round($subtotal * 0.18, 2);
        $grandTotal = round($subtotal + $deliveryCharges + $gstAmount, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'delivery' => round($deliveryCharges, 2),
            'gst' => $gstAmount,
            'grand_total' => $grandTotal,
            'amount_in_paise' => (int) round($grandTotal * 100),
        ];
    }

    private function buildSignature(string $orderId, string $paymentId, string $signature): array
    {
        $secret = env('RAZORPAY_KEY_SECRET');

        if (empty($secret)) {
            return ['valid' => false];
        }

        $body = $orderId . '|' . $paymentId;
        $expected = hash_hmac('sha256', $body, $secret);

        return ['valid' => hash_equals($expected, $signature)];
    }
}
