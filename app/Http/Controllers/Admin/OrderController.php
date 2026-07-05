<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select('orders.*', 'users.name as customer_name');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('orders.order_number', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('orders.status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('orders.payment_status', $request->input('payment_status'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'orders.created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $orders = $query->paginate(15);

        return view('admin.order-management', compact('orders'));
    }

    public function show($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) abort(404);

        $items = DB::table('order_items')->where('order_id', $id)->get();
        $customer = DB::table('users')->where('id', $order->user_id)->first();

        return view('admin.order-management', compact('order', 'items', 'customer'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,out_for_delivery,delivered,cancelled,refunded'
        ]);

        DB::table('orders')->where('id', $id)->update(['status' => $validated['status']]);

        // Log audit
        $this->logAudit('Order Status Updated', 'orders', $id, null, $validated['status']);

        return redirect()->back()->with('success', 'Order status updated successfully');
    }

    public function generateInvoice($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) abort(404);

        // Generate PDF invoice
        // This is a placeholder - implement PDF generation
        return response()->download('invoices/order-' . $order->order_number . '.pdf');
    }

    public function cancel($id)
    {
        DB::table('orders')->where('id', $id)->update(['status' => 'cancelled']);
        
        $this->logAudit('Order Cancelled', 'orders', $id, null, 'cancelled');

        return redirect()->back()->with('success', 'Order cancelled successfully');
    }

    public function destroy($id)
    {
        DB::table('orders')->where('id', $id)->delete();
        
        $this->logAudit('Order Deleted', 'orders', $id, null, null);

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully');
    }

    private function logAudit($action, $module, $recordId, $oldValue, $newValue)
    {
        // Will implement audit logging later
    }
}
