<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductDetailsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_details_page_displays_description_and_gallery_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/kiddo-1.jpg', 'image-one');
        Storage::disk('public')->put('products/kiddo-2.jpg', 'image-two');

        $product = Product::create([
            'name' => 'Kiddo Plant',
            'type' => 'plant',
            'category' => 'Accessories',
            'sku' => 'KID-TEST-1',
            'price' => 23,
            'stock' => 320,
            'description' => 'Full product description from DB',
            'is_active' => true,
            'is_pet_safe' => true,
            'specifications' => [
                'weight' => '1.2 kg',
                'details' => ['Care guide', 'Quick delivery'],
            ],
            'care_profile' => [
                'care' => 'Low maintenance',
            ],
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/kiddo-1.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/kiddo-2.jpg',
            'is_primary' => false,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('product.details', Str::slug($product->name)));

        $response->assertOk();
        $response->assertSee('Full product description from DB');
        $response->assertSee('type', false);
        $response->assertSee('Accessories');
        $response->assertSee('/storage/products/kiddo-1.jpg');
        $response->assertSee('/storage/products/kiddo-2.jpg');
    }
}
