<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerDeliveryCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Config::set('services.razorpay.gateway', 'local');
        Config::set('services.razorpay.key_id', null);
        Config::set('services.razorpay.key_secret', null);
        Branch::create([
            'name' => 'Test Branch',
            'address' => 'Kolkata, India',
            'latitude' => 22.5726,
            'longitude' => 88.3639,
        ]);
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([['lat' => '22.5726', 'lon' => '88.3639']], 200),
        ]);
    }

    public function test_checkout_shows_shared_review_page_for_user_without_address(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);
        $cartItem = $this->createCartItem($user);

        $response = $this->post(route('customer.checkout'), ['selected_items' => [$cartItem->id]]);

        $response->assertOk()->assertViewIs('customer.checkout-review');
        $response->assertSee('Contact phone number');
    }

    public function test_payment_is_blocked_without_delivery_address(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'phone' => '9876543210']);
        $this->actingAs($user);
        $cartItem = $this->createCartItem($user);
        session(['checkout_selected_ids' => [$cartItem->id]]);

        $response = $this->post(route('customer.checkout.pay'), [
            'selected_items' => [$cartItem->id],
            'address_mode' => 'new',
            'phone' => '9876543210',
            'payment_method' => 'upi',
        ]);

        $response->assertRedirect(route('customer.checkout'));
        $response->assertSessionHasErrors(['name', 'house_no', 'street', 'city', 'state', 'pincode', 'country']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_payment_is_blocked_without_contact_phone(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'house_no' => '12A',
            'street' => 'Garden Road',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);
        $this->actingAs($user);
        $cartItem = $this->createCartItem($user);
        session(['checkout_selected_ids' => [$cartItem->id]]);

        $response = $this->post(route('customer.checkout.pay'), [
            'selected_items' => [$cartItem->id],
            'address_mode' => 'saved',
            'payment_method' => 'upi',
        ]);

        $response->assertRedirect(route('customer.checkout'));
        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_valid_new_delivery_details_create_order_after_review(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);
        $cartItem = $this->createCartItem($user);

        $response = $this->post(route('customer.checkout'), ['selected_items' => [$cartItem->id]]);
        $response->assertOk();

        $response = $this->post(route('customer.checkout.pay'), [
            'selected_items' => [$cartItem->id],
            'address_mode' => 'new',
            'name' => 'Delivery Customer',
            'house_no' => '22B',
            'street' => 'Nursery Lane',
            'city' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700001',
            'country' => 'India',
            'phone' => '+919876543210',
            'payment_method' => 'upi',
        ]);

        $response->assertRedirect(route('customer.orders'));
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'branch_id' => Branch::query()->value('id'),
            'contact_phone' => '+919876543210',
            'shipping_address' => "Delivery Customer\n22B, Nursery Lane\nKolkata, West Bengal - 700001\nIndia",
        ]);
    }

    public function test_valid_checkout_details_are_persisted_and_prefilled_on_future_checkout(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);
        $cartItem = $this->createCartItem($user);

        $this->post(route('customer.checkout.pay'), [
            'selected_items' => [$cartItem->id],
            'address_mode' => 'new',
            'name' => 'Saved Customer',
            'house_no' => '18A',
            'street' => 'Plant Lane',
            'city' => 'Siliguri',
            'state' => 'West Bengal',
            'pincode' => '734001',
            'country' => 'India',
            'phone' => '9876543210',
            'payment_method' => 'cash',
        ])->assertRedirect(route('customer.orders'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Saved Customer',
            'phone' => '9876543210',
            'address' => "Saved Customer\n18A, Plant Lane\nSiliguri, West Bengal - 734001\nIndia",
            'house_no' => '18A',
            'street' => 'Plant Lane',
            'city' => 'Siliguri',
            'state' => 'West Bengal',
            'pincode' => '734001',
            'country' => 'India',
        ]);

        $nextCartItem = $this->createCartItem($user);
        $review = $this->post(route('customer.checkout'), ['selected_items' => [$nextCartItem->id]]);

        $review->assertOk()
            ->assertViewHas('hasSavedAddress', true)
            ->assertSee('9876543210')
            ->assertSee('18A, Plant Lane');
    }

    public function test_customer_can_update_saved_address_and_phone_from_checkout(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'phone' => '9876543210',
            'house_no' => '1A',
            'street' => 'Old Street',
            'city' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700001',
            'country' => 'India',
        ]);
        $this->actingAs($user);
        $cartItem = $this->createCartItem($user);

        $this->post(route('customer.checkout.pay'), [
            'selected_items' => [$cartItem->id],
            'address_mode' => 'new',
            'name' => 'Updated Customer',
            'house_no' => '9C',
            'street' => 'New Street',
            'city' => 'Durgapur',
            'state' => 'West Bengal',
            'pincode' => '713201',
            'country' => 'India',
            'phone' => '+919876543210',
            'payment_method' => 'cash',
        ])->assertRedirect(route('customer.orders'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Customer',
            'phone' => '+919876543210',
            'house_no' => '9C',
            'street' => 'New Street',
            'city' => 'Durgapur',
            'pincode' => '713201',
        ]);
    }

    public function test_invalid_checkout_details_do_not_update_customer_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'phone' => '9876543210',
            'house_no' => '1A',
            'street' => 'Original Street',
            'city' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700001',
            'country' => 'India',
        ]);
        $this->actingAs($user);
        $cartItem = $this->createCartItem($user);

        $response = $this->post(route('customer.checkout.pay'), [
            'selected_items' => [$cartItem->id],
            'address_mode' => 'new',
            'name' => 'Changed Customer',
            'house_no' => 'Changed House',
            'street' => 'Changed Street',
            'city' => 'Changed City',
            'state' => 'West Bengal',
            'pincode' => '700002',
            'country' => 'India',
            'phone' => 'invalid-phone',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('customer.checkout'));
        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '9876543210',
            'house_no' => '1A',
            'street' => 'Original Street',
            'city' => 'Kolkata',
            'pincode' => '700001',
        ]);
    }

    public function test_buy_now_redirects_to_the_same_review_page(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);
        $product = Product::create([
            'name' => 'Buy Now Plant',
            'type' => 'plant',
            'category' => 'Indoor',
            'price' => 399,
            'stock' => 5,
        ]);

        $response = $this->post(route('customer.buy-now'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('customer.checkout'));
        $this->get(route('customer.checkout'))->assertOk()->assertViewIs('customer.checkout-review');
    }

    public function test_direct_payment_route_without_review_is_blocked(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);
        $cartItem = $this->createCartItem($user);

        $response = $this->post(route('customer.checkout.pay'), [
            'selected_items' => [$cartItem->id],
            'payment_method' => 'upi',
        ]);

        $response->assertRedirect(route('customer.checkout'));
        $response->assertSessionHasErrors(['name', 'house_no', 'street', 'city', 'state', 'pincode', 'country']);
        $this->assertDatabaseCount('orders', 0);
    }

    private function createCartItem(User $user): CartItem
    {
        $product = Product::create([
            'name' => 'Test Checkout Plant ' . uniqid(),
            'type' => 'plant',
            'category' => 'Indoor',
            'price' => 500,
            'stock' => 5,
        ]);

        return CartItem::create([
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
        ]);
    }
}