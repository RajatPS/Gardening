<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_returns_only_active_products_with_pagination_metadata(): void
    {
        Product::create([
            'name' => 'Visible Fern',
            'type' => 'plant',
            'category' => 'Indoor',
            'price' => 499,
            'stock' => 3,
            'is_active' => true,
        ]);
        Product::create([
            'name' => 'Hidden Fern',
            'type' => 'plant',
            'category' => 'Indoor',
            'price' => 599,
            'stock' => 3,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/products?category=indoor&per_page=1');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Visible Fern')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonMissing(['name' => 'Hidden Fern']);
    }

    public function test_product_detail_supports_id_and_slug_and_returns_safe_not_found_json(): void
    {
        $product = Product::create([
            'name' => 'Peace Lily',
            'type' => 'plant',
            'category' => 'Indoor',
            'price' => 799,
            'stock' => 4,
        ]);

        $this->getJson('/api/v1/products/peace-lily')
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.slug', 'peace-lily')
            ->assertJsonMissingPath('data.password');

        $this->getJson('/api/v1/products/does-not-exist')
            ->assertNotFound()
            ->assertExactJson([
                'success' => false,
                'message' => 'Product not found.',
            ]);
    }

    public function test_product_index_returns_consistent_validation_errors(): void
    {
        $this->getJson('/api/v1/products?per_page=51')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonStructure(['errors' => ['per_page']]);
    }
}