<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\AuditLog;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'in_stock') {
                $query->where('stock', '>', 10);
            } elseif ($status === 'low_stock') {
                $query->whereBetween('stock', [1, 10]);
            } elseif ($status === 'out_of_stock') {
                $query->where('stock', 0);
            }
        }

        $allowedSorts = ['created_at', 'name', 'price', 'stock', 'category', 'is_active'];
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(15);

        return view('admin.product-management', compact('products'));
    }

    public function create()
    {
        return view('admin.product-create', ['categories' => $this->allowedCategories()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'category' => 'required|in:' . implode(',', $this->allowedCategories()),
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_pet_safe' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'confirm_details' => 'required|accepted',
        ]);

        $validated['sku'] = Product::generateUniqueSku($validated['name']);
        $validated['type'] = $validated['type'] ?? $validated['category'] ?? 'general';
        $validated['is_pet_safe'] = $request->boolean('is_pet_safe');
        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($validated);

        $order = $request->input('image_order', []);
        
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            
            if (empty($order)) {
                foreach ($files as $index => $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    $product->images()->create([
                        'path' => $path,
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            } else {
                foreach ($order as $index => $orderRef) {
                    if (str_starts_with($orderRef, 'new_')) {
                        $fileIndex = (int) str_replace('new_', '', $orderRef);
                        if (isset($files[$fileIndex])) {
                            $path = $files[$fileIndex]->store('products', 'public');
                            $product->images()->create([
                                'path' => $path,
                                'is_primary' => $index === 0,
                                'sort_order' => $index,
                            ]);
                        }
                    }
                }
            }
        }

        $this->logAudit('Product Added', 'products', $product->id, null, $product->toArray());

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);

        return view('admin.product-management', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        return view('admin.product-edit', [
            'product' => $product,
            'categories' => $this->allowedCategories(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'category' => 'required|in:' . implode(',', $this->allowedCategories()),
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id',
            'is_pet_safe' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['type'] = $validated['type'] ?? $validated['category'] ?? $product->type ?? 'general';
        $validated['is_pet_safe'] = $request->boolean('is_pet_safe');
        $validated['is_active'] = $request->boolean('is_active', true);

        if (empty($product->sku)) {
            $validated['sku'] = Product::generateUniqueSku($validated['name']);
        }

        $product->update($validated);

        if ($request->filled('delete_images')) {
            $imagesToDelete = $product->images()->whereIn('id', $request->input('delete_images'))->get();
            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $order = $request->input('image_order', []);
        $files = $request->file('images', []);

        if (empty($order)) {
            if ($request->hasFile('images')) {
                $maxSortOrder = $product->images()->max('sort_order') ?? -1;
                foreach ($request->file('images') as $index => $imageFile) {
                    $path = $imageFile->store('products', 'public');
                    $product->images()->create([
                        'path' => $path,
                        'is_primary' => false,
                        'sort_order' => $maxSortOrder + $index + 1,
                    ]);
                }
            }
        } else {
            $product->images()->update(['is_primary' => false]);
            
            foreach ($order as $index => $orderRef) {
                if (str_starts_with($orderRef, 'existing_')) {
                    $imageId = (int) str_replace('existing_', '', $orderRef);
                    $image = $product->images()->find($imageId);
                    if ($image) {
                        $image->update([
                            'is_primary' => $index === 0,
                            'sort_order' => $index,
                        ]);
                    }
                } elseif (str_starts_with($orderRef, 'new_')) {
                    $fileIndex = (int) str_replace('new_', '', $orderRef);
                    if (isset($files[$fileIndex])) {
                        $path = $files[$fileIndex]->store('products', 'public');
                        $product->images()->create([
                            'path' => $path,
                            'is_primary' => $index === 0,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        }

        if ($product->images()->count() > 0 && !$product->images()->where('is_primary', true)->exists()) {
            $firstImage = $product->images()->orderBy('sort_order')->orderBy('id')->first();
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
            }
        }

        $this->logAudit('Product Updated', 'products', $product->id, null, $validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $this->logAudit('Product Deleted', 'products', $product->id, $product->toArray(), null);

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }

    private function allowedCategories(): array
    {
        return ['Plants', 'Medicinal Plants', 'Accessories', 'Flowering Plants', 'Outdoor Plants', 'Bonsai'];
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
