<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\SavedProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_a_product_to_cart(): void
    {
        $product = Product::create([
            'name' => 'Areca Palm',
            'type' => 'plant',
            'category' => 'Air purifying indoor plant',
            'quantity' => 10,
            'price' => 799,
            'stock' => 10,
        ]);

        $response = $this->post(route('customer.cart.add'), [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Item added to cart.');
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'session_id' => session()->getId(),
        ]);
    }

    public function test_guest_can_save_a_product_to_wishlist(): void
    {
        $product = Product::create([
            'name' => 'Ceramic Self-Watering Pot',
            'type' => 'accessory',
            'category' => 'Decorative pot',
            'quantity' => 5,
            'price' => 1199,
            'stock' => 5,
        ]);

        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $response = $this->post(route('customer.saved-products.save'), [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product saved to your list.');
        $this->assertDatabaseHas('saved_products', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_checkout_from_cart(): void
    {
        putenv('PAYMENT_GATEWAY=local');
        putenv('RAZORPAY_KEY_ID=');
        putenv('RAZORPAY_KEY_SECRET=');
        $_ENV['PAYMENT_GATEWAY'] = 'local';
        $_ENV['RAZORPAY_KEY_ID'] = '';
        $_ENV['RAZORPAY_KEY_SECRET'] = '';
        $_SERVER['PAYMENT_GATEWAY'] = 'local';
        $_SERVER['RAZORPAY_KEY_ID'] = '';
        $_SERVER['RAZORPAY_KEY_SECRET'] = '';

        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $product = Product::create([
            'name' => 'Organic Growth Kit',
            'type' => 'kit',
            'category' => 'Plant care bundle',
            'quantity' => 3,
            'price' => 649,
            'stock' => 20,
        ]);

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'product_id' => $product->id,
            'product_name' => 'Organic Growth Kit',
            'price' => 649,
            'quantity' => 1,
        ]);

        $response = $this->post(route('customer.checkout'), [
            'selected_items' => [$cartItem->id],
        ]);

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
