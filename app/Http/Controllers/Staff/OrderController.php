<?php

namespace App\Http\Controllers\Staff;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    public function index()
    {
        $staff = auth()->user();
        $orders = Order::with('user', 'branch')
            ->where('branch_id', $staff->branch_id)
            ->latest()
            ->paginate(15);

        return view('staff.orders', compact('orders', 'staff'));
    }

    public function show(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load('user', 'branch', 'items.product');

        return view('staff.order-show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,out_for_delivery,delivered,cancelled'],
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless(
            auth()->user()?->role === 'staff'
            && auth()->user()->branch_id !== null
            && (int) $order->branch_id === (int) auth()->user()->branch_id,
            403
        );
    }
}