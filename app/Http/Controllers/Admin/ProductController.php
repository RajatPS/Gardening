<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'in_stock') {
                $query->where('quantity', '>', 10);
            } elseif ($status === 'low_stock') {
                $query->whereBetween('quantity', [1, 10]);
            } elseif ($status === 'out_of_stock') {
                $query->where('quantity', 0);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $products = $query->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products',
            'category' => 'required|string',
            'subcategory' => 'nullable|string',
            'brand' => 'nullable|string',
            'sku' => 'required|string|unique:products',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'weight' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
            'description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'care_instructions' => 'nullable|string',
            'watering_info' => 'nullable|string',
            'sunlight_info' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
            'status' => 'required|in:active,draft,hidden',
        ]);

        $product = Product::create($validated);

        // Handle image upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        $this->logAudit('Product Added', 'products', $product->id, null, $product->toArray());

        return redirect()->route('admin.products.show', $product)->with('success', 'Product created successfully');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug,' . $id,
            'category' => 'required|string',
            'subcategory' => 'nullable|string',
            'brand' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku,' . $id,
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'weight' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
            'description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'care_instructions' => 'nullable|string',
            'watering_info' => 'nullable|string',
            'sunlight_info' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
            'status' => 'required|in:active,draft,hidden',
        ]);

        $product->update($validated);

        // Handle new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }

        $this->logAudit('Product Updated', 'products', $product->id, null, $validated);

        return redirect()->route('admin.products.show', $product)->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        $this->logAudit('Product Deleted', 'products', $product->id, $product->toArray(), null);

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }

    private function logAudit($action, $module, $recordId, $oldValue, $newValue)
    {
        // Will implement audit logging later
    }
}
