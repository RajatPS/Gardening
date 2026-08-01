<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

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

        // Sorting with whitelist validation
        $allowedSorts = ['orders.created_at', 'order_number', 'status', 'payment_status', 'total_amount', 'user_id'];
        $sortBy = $request->input('sort_by', 'orders.created_at');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'orders.created_at';
        }
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
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
        $items = DB::table('order_items')->where('order_id', $id)->get();
        $customer = DB::table('users')->where('id', $order->user_id)->first();

        // Compute summary matching storefront calculations
        $subtotal = (float) collect($items)->sum(fn ($item) => ((float) ($item->price ?? 0)) * ((int) ($item->quantity ?? 1)));
        $delivery = 20.0;
        $gst = round($subtotal * 0.18, 2);
        $grandTotal = round($subtotal + $delivery + $gst, 2);

        $summary = [
            'subtotal' => round($subtotal, 2),
            'delivery' => round($delivery, 2),
            'gst' => $gst,
            'grand_total' => $grandTotal,
        ];

        $data = [
            'order' => $order,
            'items' => $items,
            'customer' => $customer,
            'billing_address' => $order->shipping_address,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method ?? 'N/A',
            'order_date' => optional($order->created_at)->toDateString() ?? date('Y-m-d'),
            'invoice_number' => 'INV-' . $order->order_number,
            'summary' => $summary,
            'notes' => $order->notes,
        ];

        $pdf = PDF::loadView('admin.invoices.invoice', $data)->setPaper('a4', 'portrait');

        $filename = 'invoice-' . $order->order_number . '.pdf';

        return $pdf->stream($filename);
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
        AuditLog::create([
            'user_id' => auth()->id(),
            'action_type' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : (string) ($oldValue ?? ''),
            'new_value' => is_array($newValue) ? json_encode($newValue) : (string) ($newValue ?? ''),
            'ip_address' => request()->ip(),
            'device_info' => request()->header('User-Agent'),
        ]);
    }
}
