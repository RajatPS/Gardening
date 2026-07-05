<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRegistrationTest extends TestCase
{
    use RefreshDatabase;

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
}
