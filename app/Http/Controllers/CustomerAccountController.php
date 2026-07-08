<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\SavedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CustomerAccountController extends Controller
{
    public function cart()
    {
        $items = $this->cartItemsQuery()->get();

        return view('customer.cart', [
            'items' => $items,
            'total' => $items->sum(fn ($item) => $item->price * $item->quantity),
        ]);
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string',
            'product_category' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image_url' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $price = (float) ($validated['price'] ?? 0);
        $quantity = (int) ($validated['quantity'] ?? 1);

        $scope = $this->scopeAttributes();
        $existingItem = $this->cartItemsQuery()
            ->where('product_name', $validated['product_name'])
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'user_id' => $scope['user_id'] ?? null,
                'session_id' => $scope['session_id'] ?? null,
                'product_name' => $validated['product_name'],
                'product_category' => $validated['product_category'] ?? null,
                'price' => $price,
                'quantity' => $quantity,
                'image_url' => $validated['image_url'] ?? null,
                'metadata' => [
                    'added_from' => 'store',
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Item added to cart.');
    }

    public function updateCart(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->update([
            'quantity' => (int) $validated['quantity'],
        ]);

        return redirect()->back()->with('success', 'Cart updated.');
    }

    public function removeCartItem(CartItem $cartItem)
    {
        $cartItem->delete();

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

        $items = $this->cartItemsQuery()->get();

        if ($items->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Your cart is empty.');
        }

        $total = $items->sum(fn ($item) => $item->price * $item->quantity);
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'total_amount' => $total,
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipping_address' => auth()->user()->address ?? $request->input('address', 'Address not provided'),
            'notes' => $request->input('notes', 'Placed from cart'),
        ]);

        $this->cartItemsQuery()->delete();

        return redirect()->route('customer.orders')->with('success', 'Order placed successfully.');
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
            'product_name' => 'required|string',
            'product_category' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image_url' => 'nullable|string',
        ]);

        $scope = $this->scopeAttributes();
        SavedProduct::create([
            'user_id' => $scope['user_id'] ?? null,
            'session_id' => $scope['session_id'] ?? null,
            'product_name' => $validated['product_name'],
            'product_category' => $validated['product_category'] ?? null,
            'price' => (float) ($validated['price'] ?? 0),
            'image_url' => $validated['image_url'] ?? null,
            'metadata' => [
                'saved_from' => 'store',
            ],
        ]);

        return redirect()->back()->with('success', 'Product saved to your list.');
    }

    public function removeSavedProduct(SavedProduct $savedProduct)
    {
        $savedProduct->delete();

        return redirect()->back()->with('success', 'Saved product removed.');
    }

    public function profile()
    {
        return view('customer.profile', [
            'user' => auth()->user(),
        ]);
    }

    private function cartItemsQuery()
    {
        $query = CartItem::query();
        $scope = $this->scopeAttributes();

        if ($scope['user_id'] ?? null) {
            $query->where('user_id', $scope['user_id']);
        } else {
            $query->where('session_id', $scope['session_id']);
        }

        return $query->orderByDesc('created_at');
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
}
