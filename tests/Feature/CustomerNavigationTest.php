<?php

namespace Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Tests\TestCase;

class CustomerNavigationTest extends TestCase
{
    public function test_home_page_provides_numeric_product_prices_for_cart_actions(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        $products = $response->viewData('products');

        $this->assertNotEmpty($products);
        $this->assertTrue(
            collect($products)->contains(fn ($product) => is_numeric($product['price_value'] ?? null)),
            'Expected at least one product to expose a numeric price value for cart and saved-product actions.'
        );
    }

    public function test_home_page_shows_login_and_sign_up_links_for_guests(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Sign up');
        $response->assertSee('Log in');
    }

    public function test_home_page_hides_login_and_sign_up_links_for_authenticated_users(): void
    {
        $user = new class implements Authenticatable {
            public function getAuthIdentifierName()
            {
                return 'id';
            }

            public function getAuthIdentifier()
            {
                return 1;
            }

            public function getAuthPassword()
            {
                return 'password';
            }

            public function getAuthPasswordName()
            {
                return 'password';
            }

            public function getRememberToken()
            {
                return null;
            }

            public function setRememberToken($value)
            {
            }

            public function getRememberTokenName()
            {
                return 'remember_token';
            }
        };

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Sign up');
        $response->assertDontSee('Log in');
    }

    public function test_subscriptions_page_shows_plan_checkout_form(): void
    {
        $response = $this->get(route('subscriptions'));

        $response->assertOk();
        $response->assertSee('name="plan"', false);
    }
}
