<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::select('id', 'name', 'sku', 'quantity');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'low_stock') {
                $query->whereBetween('quantity', [1, 10]);
            } elseif ($status === 'out_of_stock') {
                $query->where('quantity', 0);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $inventory = $query->paginate(15);

        return view('admin.inventory-management', compact('inventory'));
    }

    public function addStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($id);
        $oldQuantity = $product->quantity;
        $product->increment('quantity', $validated['quantity']);

        $this->logAudit('Stock Increased', 'products', $id, $oldQuantity, $product->quantity);

        return redirect()->back()->with('success', 'Stock added successfully');
    }

    public function reduceStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($id);
        if ($product->quantity < $validated['quantity']) {
            return redirect()->back()->withErrors('Insufficient stock');
        }

        $oldQuantity = $product->quantity;
        $product->decrement('quantity', $validated['quantity']);

        $this->logAudit('Stock Reduced', 'products', $id, $oldQuantity, $product->quantity);

        return redirect()->back()->with('success', 'Stock reduced successfully');
    }

    public function adjustInventory(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $product = Product::findOrFail($id);
        $oldQuantity = $product->quantity;
        $product->update(['quantity' => $validated['quantity']]);

        $this->logAudit('Inventory Adjusted', 'products', $id, $oldQuantity, $validated['quantity']);

        return redirect()->back()->with('success', 'Inventory adjusted successfully');
    }

    private function logAudit($action, $module, $recordId, $oldValue, $newValue)
    {
        // Will implement audit logging later
    }
}
