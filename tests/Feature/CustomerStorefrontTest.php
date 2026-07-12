<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\SavedProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_a_product_to_cart(): void
    {
        $response = $this->post(route('customer.cart.add'), [
            'product_name' => 'Areca Palm',
            'product_category' => 'Air purifying indoor plant',
            'price' => 799,
            'image_url' => 'https://example.com/plant.jpg',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Item added to cart.');
        $this->assertDatabaseHas('cart_items', [
            'product_name' => 'Areca Palm',
            'session_id' => session()->getId(),
        ]);
    }

    public function test_guest_can_save_a_product_to_wishlist(): void
    {
        $response = $this->post(route('customer.saved-products.save'), [
            'product_name' => 'Ceramic Self-Watering Pot',
            'product_category' => 'Decorative pot',
            'price' => 1199,
            'image_url' => 'https://example.com/pot.jpg',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product saved to your list.');
        $this->assertDatabaseHas('saved_products', [
            'product_name' => 'Ceramic Self-Watering Pot',
            'session_id' => session()->getId(),
        ]);
    }

    public function test_authenticated_user_can_checkout_from_cart(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        CartItem::create([
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'product_name' => 'Organic Growth Kit',
            'price' => 649,
            'quantity' => 1,
        ]);

        $response = $this->post(route('customer.checkout'));

        $response->assertRedirect(route('customer.orders'));
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
        ]);
    }
}
