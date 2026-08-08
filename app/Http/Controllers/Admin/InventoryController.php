<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::select('id', 'name', 'sku', 'stock');

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
                $query->whereBetween('stock', [1, 10]);
            } elseif ($status === 'out_of_stock') {
                $query->where('stock', 0);
            }
        }

        // Sorting with whitelist validation
        $allowedSorts = ['name', 'sku', 'stock'];
        $sortBy = $request->input('sort_by', 'name');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'name';
        }
        $sortOrder = strtolower($request->input('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
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
        $oldStock = $product->stock;
        $product->increment('stock', $validated['quantity']);

        $this->logAudit('Stock Increased', 'products', $id, $oldStock, $product->stock);

        return redirect()->back()->with('success', 'Stock added successfully');
    }

    public function reduceStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($id);
        if ($product->stock < $validated['quantity']) {
            return redirect()->back()->withErrors('Insufficient stock');
        }

        $oldStock = $product->stock;
        $product->decrement('stock', $validated['quantity']);

        $this->logAudit('Stock Reduced', 'products', $id, $oldStock, $product->stock);

        return redirect()->back()->with('success', 'Stock reduced successfully');
    }

    public function adjustInventory(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $product = Product::findOrFail($id);
        $oldStock = $product->stock;
        $product->update(['stock' => $validated['quantity']]);

        $this->logAudit('Inventory Adjusted', 'products', $id, $oldStock, $validated['quantity']);

        return redirect()->back()->with('success', 'Inventory adjusted successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        try {
            $product->delete();
            $this->logAudit('Inventory Deleted', 'products', $product->id, $product->toArray(), null);

            return redirect()->route('admin.inventory.index')->with('success', 'Inventory item deleted successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to delete this inventory item because it is still referenced by product records.');
        }
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
