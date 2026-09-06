<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class CustomerCheckoutRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_guest_checkout_redirects_to_customer_login_page(): void
    {
        $response = $this->post(route('customer.checkout'));

        $response->assertRedirect(route('customer.login', ['redirect' => route('customer.checkout')]));
    }
}
