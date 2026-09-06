<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'in:created_at,name,price,stock,category'],
            'sort_order' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Product::query()
            ->with(['primaryImage', 'images'])
            ->where('is_active', true)
            ->whereNotNull('name')
            ->where('name', '!=', '');

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($validated['category'])) {
            $category = $validated['category'];
            $query->where(function ($query) use ($category): void {
                $query->where('category', 'like', "%{$category}%")
                    ->orWhere('name', 'like', "%{$category}%");
            });
        }

        $products = $query
            ->orderBy($validated['sort_by'] ?? 'created_at', $validated['sort_order'] ?? 'desc')
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => ProductResource::collection($products->getCollection())->resolve(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $product): JsonResponse
    {
        $item = Product::query()
            ->with(['primaryImage', 'images'])
            ->where('is_active', true)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->where(function ($query) use ($product): void {
                if (ctype_digit($product)) {
                    $query->whereKey((int) $product);
                } else {
                    $query->whereRaw('LOWER(name) = ?', [str_replace('-', ' ', Str::lower($product))]);
                }
            })
            ->first();

        if ($item === null) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data' => (new ProductResource($item))->resolve(),
        ]);
    }
}