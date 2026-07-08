<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerCheckoutRedirectTest extends TestCase
{
    public function test_guest_checkout_redirects_to_customer_login_page(): void
    {
        $response = $this->post(route('customer.checkout'));

        $response->assertRedirect(route('customer.login', ['redirect' => route('customer.cart')]));
    }
}
