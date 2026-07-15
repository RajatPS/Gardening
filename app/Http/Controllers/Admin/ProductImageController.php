<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductImage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function destroy(ProductImage $productImage)
    {
        // Delete the file from storage
        if (Storage::disk('public')->exists($productImage->path)) {
            Storage::disk('public')->delete($productImage->path);
        }

        // Delete the database record
        $productImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully');
    }
}
