<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\SavedProduct;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CustomerStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_guest_can_add_a_product_to_cart(): void
    {
        $product = Product::create([
            'name' => 'Areca Palm',
            'type' => 'plant',
            'category' => 'Air purifying indoor plant',
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
            'price' => 1199,
            'stock' => 5,
        ]);

        $user = User::factory()->create([
            'role' => 'customer',
            'phone' => '9876543210',
            'house_no' => '12A',
            'street' => 'Garden Road',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);
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
        Config::set('services.razorpay.gateway', 'local');
        Config::set('services.razorpay.key_id', null);
        Config::set('services.razorpay.key_secret', null);

        $user = User::factory()->create([
            'role' => 'customer',
            'phone' => '9876543210',
            'house_no' => '12A',
            'street' => 'Garden Road',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);
        $this->actingAs($user);

        $product = Product::create([
            'name' => 'Organic Growth Kit',
            'type' => 'kit',
            'category' => 'Plant care bundle',
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

        $response->assertOk();
        $response->assertViewIs('customer.checkout-review');

        $response = $this->post(route('customer.checkout.pay'), [
            'selected_items' => [$cartItem->id],
            'address_mode' => 'saved',
            'phone' => '9876543210',
            'payment_method' => 'upi',
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
