<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_product_creation_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.products.create'));

        $response->assertOk();
        $response->assertSee('Create Product');
        $response->assertSee('name="name"');
    }

    public function test_admin_can_create_product_without_manual_sku(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Rose Plant',
            'type' => 'plant',
            'category' => 'flower',
            'quantity' => 10,
            'price' => 150,
            'stock' => 10,
            'is_active' => '1',
            'is_pet_safe' => '1',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'Rose Plant']);

        $product = Product::where('name', 'Rose Plant')->first();
        $this->assertNotNull($product);
        $this->assertNotEmpty($product->sku);
    }
}
