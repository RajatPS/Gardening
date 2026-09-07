<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerOtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config()->set('services.twilio.sid', 'test-sid');
        config()->set('services.twilio.token', 'test-token');
        config()->set('services.twilio.from', '+15555550100');
        Http::fake([
            'https://api.twilio.com/*' => Http::response(['sid' => 'message-id'], 201),
        ]);
    }

    public function test_valid_password_starts_login_otp_without_authenticating(): void
    {
        $user = $this->customer(['phone' => '+919876543210']);

        $response = $this->post(route('customer.login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertSame(302, $response->status());
        $this->assertSame(route('customer.login.otp'), $response->headers->get('Location'));
        $this->assertGuest();
        $this->assertTrue(session()->has('customer_login_pending'));
        Http::assertSent(fn ($request) => str_contains((string) $request->data()['Body'], 'login OTP'));
    }

    public function test_wrong_otp_does_not_authenticate_customer(): void
    {
        $user = $this->customer(['phone' => '+919876543210']);
        $this->startLogin($user);

        $response = $this->from(route('customer.login.otp'))->post(route('customer.login.otp.verify'), [
            'otp' => '000000',
        ]);

        $this->assertSame(302, $response->status());
        $this->assertSame(route('customer.login.otp'), $response->headers->get('Location'));
        $this->assertTrue($response->getSession()->has('errors'));
        $this->assertGuest();
    }

    public function test_expired_otp_does_not_authenticate_customer(): void
    {
        $user = $this->customer(['phone' => '+919876543210']);
        $this->startLogin($user);
        Cache::forget('customer_login_otp_' . $user->id);

        $response = $this->from(route('customer.login.otp'))->post(route('customer.login.otp.verify'), ['otp' => '123456']);

        $this->assertSame(302, $response->status());
        $this->assertSame(route('customer.login.otp'), $response->headers->get('Location'));
        $this->assertTrue($response->getSession()->has('errors'));
        $this->assertGuest();
    }

    public function test_correct_otp_authenticates_once_and_redirects_home(): void
    {
        $user = $this->customer(['phone' => '+919876543210']);
        $this->startLogin($user);
        $otp = (string) Cache::get('customer_login_otp_' . $user->id);

        $response = $this->post(route('customer.login.otp.verify'), ['otp' => $otp]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status', 'Login successful.');
        $this->assertAuthenticatedAs($user);
        $this->assertFalse(Cache::has('customer_login_otp_' . $user->id));
        $this->assertFalse(session()->has('customer_login_pending'));
    }

    public function test_login_otp_preserves_an_explicit_checkout_redirect(): void
    {
        $user = $this->customer(['phone' => '+919876543210']);

        $this->post(route('customer.login.post'), [
            'email' => $user->email,
            'password' => 'password',
            'redirect' => route('customer.checkout'),
        ])->assertRedirect(route('customer.login.otp'));

        $otp = (string) Cache::get('customer_login_otp_' . $user->id);
        $response = $this->post(route('customer.login.otp.verify'), ['otp' => $otp]);

        $response->assertRedirect(route('customer.checkout'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_requires_a_saved_phone_number(): void
    {
        $user = $this->customer(['phone' => null]);

        $response = $this->from(route('customer.login'))->post(route('customer.login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertSame(302, $response->status());
        $this->assertSame(route('customer.login'), $response->headers->get('Location'));
        $this->assertTrue($response->getSession()->has('errors'));
        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_signup_redirects_home_with_success_message(): void
    {
        $response = $this->post(route('customer.register.post'), [
            'name' => 'New Customer',
            'email' => 'new-customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status', 'Signed up successfully.');
        $this->assertAuthenticated();
    }

    public function test_forgot_password_otp_remains_separate_from_login_otp(): void
    {
        $user = $this->customer(['phone' => '+919876543210']);

        $response = $this->post(route('customer.forgot-password.post'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('password_reset_pending.email', $user->email);
        $this->assertGuest();
        $this->assertTrue(Cache::has('password_reset_otp_+919876543210'));
        $this->assertFalse(session()->has('customer_login_pending'));
    }

    public function test_forgot_password_otp_can_still_reset_the_password(): void
    {
        $user = $this->customer(['phone' => '+919876543210']);

        $this->post(route('customer.forgot-password.post'), [
            'email' => $user->email,
        ])->assertRedirect();

        $otp = (string) Cache::get('password_reset_otp_+919876543210');
        $response = $this->post(route('customer.reset-password.post'), [
            'email' => $user->email,
            'otp' => $otp,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('customer.login'));
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertGuest();
        $this->assertFalse(session()->has('customer_login_pending'));
    }

    private function customer(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer',
            'status' => 'active',
            'user_type' => 'customer',
            'password' => Hash::make('password'),
        ], $attributes));
    }

    private function startLogin(User $user): void
    {
        $this->post(route('customer.login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('customer.login.otp'));
    }
}
