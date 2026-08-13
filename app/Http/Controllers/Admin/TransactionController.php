<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->join('orders', 'transactions.order_id', '=', 'orders.id')
            ->select('transactions.*', 'users.name as user_name', 'orders.order_number');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('transactions.transaction_id', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%")
                  ->orWhere('orders.order_number', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('transactions.status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $paymentMethod = $request->input('payment_method');
            $query->where(function ($q) use ($paymentMethod) {
                $q->where('transactions.payment_method', $paymentMethod)
                  ->orWhere('transactions.payment_gateway', $paymentMethod);
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transactions.created_at', [
                $request->input('date_from'),
                $request->input('date_to')
            ]);
        }

        if ($request->filled('amount_from') && $request->filled('amount_to')) {
            $query->whereBetween('transactions.amount', [
                $request->input('amount_from'),
                $request->input('amount_to')
            ]);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'transactions.created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $transactions = $query->paginate(15);

        return view('admin.transaction-management', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = DB::table('transactions')->where('id', $id)->first();
        if (!$transaction) abort(404);

        $user = DB::table('users')->where('id', $transaction->user_id)->first();
        $order = DB::table('orders')->where('id', $transaction->order_id)->first();

        // Provide transactions list for the management view
        $query = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->join('orders', 'transactions.order_id', '=', 'orders.id')
            ->select('transactions.*', 'users.name as user_name', 'orders.order_number')
            ->orderBy('transactions.created_at', 'desc');

        $transactions = $query->paginate(15);

        return view('admin.transaction-management', compact('transaction', 'user', 'order', 'transactions'))->with('showMode', true);
    }

    public function destroy($id)
    {
        $transaction = DB::table('transactions')->where('id', $id)->first();

        if (! $transaction) {
            return redirect()->route('admin.transactions.index')->with('error', 'Transaction not found.');
        }

        try {
            DB::table('transactions')->where('id', $id)->delete();

            return redirect()->route('admin.transactions.index')->with('success', 'Transaction deleted successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to delete this transaction.');
        }
    }
}
