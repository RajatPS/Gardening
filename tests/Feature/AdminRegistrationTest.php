<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_register_and_be_logged_in(): void
    {
        $response = $this->post(route('admin.register.post'), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9876543210',
            'address' => '123 Garden Street',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_with_default_password_redirects_to_change_password_page(): void
    {
        $user = User::create([
            'name' => 'Default Admin',
            'email' => 'default-admin@example.com',
            'password' => Hash::make('11223344'),
            'role' => 'admin',
            'status' => 'active',
            'user_type' => 'admin',
            'must_change_password' => true,
        ]);

        $response = $this->post(route('admin.login.post'), [
            'email' => $user->email,
            'password' => '11223344',
        ]);

        $response->assertRedirect(route('admin.change-password'));
        $this->assertTrue(Auth::user()->must_change_password);
    }
}
