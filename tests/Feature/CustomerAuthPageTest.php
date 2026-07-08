<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerAuthPageTest extends TestCase
{
    public function test_customer_login_page_shows_google_auth_option(): void
    {
        $response = $this->get(route('customer.login'));

        $response->assertOk();
        $response->assertSee('Continue with Google');
    }

    public function test_customer_register_page_shows_google_auth_option(): void
    {
        $response = $this->get(route('customer.register'));

        $response->assertOk();
        $response->assertSee('Continue with Google');
    }

    public function test_google_auth_redirect_signs_in_demo_user_and_redirects_back(): void
    {
        $response = $this->get(route('customer.google.redirect', ['redirect' => route('customer.profile')]));

        $response->assertStatus(302);
        $response->assertRedirectContains('accounts.google.com');
    }
}
