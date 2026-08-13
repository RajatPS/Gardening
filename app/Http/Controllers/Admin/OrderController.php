<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;
use App\Models\Transaction;
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

        // Provide orders list so the management view has its table data
        $ordersQuery = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select('orders.*', 'users.name as customer_name')
            ->orderBy('orders.created_at', 'desc');

        $orders = $ordersQuery->paginate(15);

        return view('admin.order-management', compact('order', 'items', 'customer', 'orders'))->with('showMode', true);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,cancelled'
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

    public function recordCodPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'collected_amount' => 'required|numeric|min:0',
        ]);

        $order = DB::table('orders')->where('id', $id)->first();
        if (! $order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        if ($order->payment_method !== 'cod' && $order->payment_method !== 'cash') {
            return redirect()->back()->with('error', 'This order is not eligible for COD payment capture.');
        }

        if ($order->status !== 'delivered') {
            return redirect()->back()->with('error', 'COD payment can only be recorded after the order is delivered.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->back()->with('success', 'COD payment has already been recorded for this order.');
        }

        if (round((float) $validated['collected_amount'], 2) !== round((float) $order->total_amount, 2)) {
            return redirect()->back()->with('error', 'Collected amount does not match the order total.');
        }

        try {
            DB::transaction(function () use ($order, $validated) {
                $transactionId = 'cod-' . $order->order_number;

                $transaction = Transaction::firstOrNew([
                    'transaction_id' => $transactionId,
                ]);

                $transaction->fill([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'amount' => $validated['collected_amount'],
                    'payment_method' => 'cod',
                    'payment_gateway' => 'cash',
                    'status' => 'completed',
                    'payment_details' => [
                        'gateway' => 'cash_on_delivery',
                        'order_id' => $order->order_number,
                        'collected_amount' => $validated['collected_amount'],
                    ],
                    'gateway_response' => [
                        'method' => 'cash_on_delivery',
                        'collected_by' => auth()->id(),
                        'collected_at' => now()->toISOString(),
                    ],
                    'response' => 'Cash on Delivery payment collected and recorded.',
                ]);

                $transaction->save();

                DB::table('orders')->where('id', $order->id)->update(['payment_status' => 'paid']);
            });
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to record COD payment.');
        }

        return redirect()->back()->with('success', 'COD payment recorded successfully.');
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
