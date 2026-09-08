<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StaffProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_unauthenticated_users_are_redirected_from_staff_profile(): void
    {
        $this->get(route('staff.profile'))->assertRedirect(route('login'));
    }

    public function test_staff_profile_is_populated_from_the_authenticated_user(): void
    {
        $branch = Branch::create(['name' => 'Profile Test Branch']);
        $staff = User::factory()->create([
            'role' => 'staff',
            'branch_id' => $branch->id,
            'phone' => '9876543210',
            'address' => 'Garden Road',
            'house_no' => '12A',
            'street' => 'Main Street',
            'city' => 'Kolkata',
            'state' => null,
            'pincode' => null,
            'country' => 'India',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.profile'));

        $response->assertOk()
            ->assertSee('value="' . $staff->name . '"', false)
            ->assertSee('value="' . $staff->email . '"', false)
            ->assertSee($branch->name)
            ->assertSee('Current Location: Not Set');
    }

    public function test_staff_can_update_their_own_address_without_a_user_id(): void
    {
        config(['services.geoapify.key' => 'test-key']);
        Http::fake([
            'https://api.geoapify.com/*' => Http::response([
                'results' => [['lat' => 26.5013, 'lon' => 89.3485]],
            ]),
        ]);
        $assignedBranch = Branch::create([
            'name' => 'Assigned Branch',
            'latitude' => 26.5013,
            'longitude' => 89.3485,
        ]);
        $staff = User::factory()->create(['role' => 'staff', 'branch_id' => $assignedBranch->id]);
        $otherStaff = User::factory()->create(['role' => 'staff', 'address' => 'Unchanged']);

        $response = $this->actingAs($staff)->put(route('staff.profile.update'), [
            'name' => $staff->name,
            'email' => $staff->email,
            'phone' => '1234567890',
            'address' => 'Updated Address',
            'house_no' => '7',
            'street' => 'Updated Street',
            'city' => 'Kolkata',
            'state' => 'West Bengal',
            'pincode' => '700001',
            'country' => 'India',
            'user_id' => $otherStaff->id,
            'branch_id' => $assignedBranch->id,
        ]);

        $response->assertRedirect(route('staff.profile'))->assertSessionHas('success');
        $this->assertSame('Updated Address', $staff->fresh()->address);
        $this->assertSame($assignedBranch->id, $staff->fresh()->branch_id);
        $this->assertEquals(26.5013, (float) $staff->fresh()->latitude);
        $this->assertSame('Unchanged', $otherStaff->fresh()->address);
    }

    public function test_address_save_rejects_a_branch_that_is_not_nearest(): void
    {
        config(['services.geoapify.key' => 'test-key']);
        Http::fake([
            'https://api.geoapify.com/*' => Http::response([
                'results' => [['lat' => 26.5013, 'lon' => 89.3485]],
            ]),
        ]);
        $nearestBranch = Branch::create(['name' => 'Near', 'latitude' => 26.5013, 'longitude' => 89.3485]);
        $farBranch = Branch::create(['name' => 'Far', 'latitude' => 22.5726, 'longitude' => 88.3639]);
        $staff = User::factory()->create(['role' => 'staff', 'branch_id' => $farBranch->id]);

        $response = $this->actingAs($staff)->put(route('staff.profile.update'), [
            'name' => $staff->name,
            'email' => $staff->email,
            'branch_id' => $farBranch->id,
            'address' => 'Madharihat',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);

        $response->assertRedirect()->assertSessionHasErrors('branch_id');
        $this->assertSame($farBranch->id, $staff->fresh()->branch_id);
        $this->assertNull($staff->fresh()->latitude);
        $this->assertNotSame($farBranch->id, $nearestBranch->id);
    }

    public function test_address_save_handles_geoapify_failure_without_updating_the_user(): void
    {
        config(['services.geoapify.key' => 'test-key']);
        Http::fake(['https://api.geoapify.com/*' => Http::response([], 503)]);
        $branch = Branch::create(['name' => 'Existing Branch', 'latitude' => 26.5013, 'longitude' => 89.3485]);
        $staff = User::factory()->create(['role' => 'staff', 'branch_id' => $branch->id]);

        $response = $this->actingAs($staff)->put(route('staff.profile.update'), [
            'name' => $staff->name,
            'email' => $staff->email,
            'branch_id' => $branch->id,
            'address' => 'Unknown location',
            'city' => 'Unknown',
            'country' => 'India',
        ]);

        $response->assertRedirect()->assertSessionHasErrors('address');
        $this->assertNull($staff->fresh()->address);
        $this->assertNull($staff->fresh()->latitude);
    }

    public function test_staff_can_save_current_location_for_the_authenticated_user(): void
    {
        $branch = Branch::create([
            'name' => 'Nearest Branch',
            'latitude' => 26.5013,
            'longitude' => 89.3485,
        ]);
        $staff = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->postJson(route('staff.profile.location'), [
            'branch_id' => $branch->id,
            'latitude' => 26.5013,
            'longitude' => 89.3485,
            'user_id' => $otherStaff->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('latitude', 26.5013)
            ->assertJsonPath('longitude', 89.3485);
        $this->assertEquals(26.5013, (float) $staff->fresh()->latitude);
        $this->assertEquals(89.3485, (float) $staff->fresh()->longitude);
        $this->assertSame($branch->id, $staff->fresh()->branch_id);
        $this->assertNull($otherStaff->fresh()->latitude);
        $this->assertNull($otherStaff->fresh()->longitude);
    }

    public function test_invalid_current_location_is_rejected(): void
    {
        $branch = Branch::create([
            'name' => 'Validation Branch',
            'latitude' => 26.5013,
            'longitude' => 89.3485,
        ]);
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->postJson(route('staff.profile.location'), [
            'branch_id' => $branch->id,
            'latitude' => 91,
            'longitude' => -181,
        ]);

        $this->assertSame(422, $response->status());
        $errors = $response->json('errors');
        $this->assertArrayHasKey('latitude', $errors);
        $this->assertArrayHasKey('longitude', $errors);
        $this->assertNull($staff->fresh()->latitude);
        $this->assertNull($staff->fresh()->longitude);
    }

    public function test_staff_cannot_save_a_branch_that_is_not_nearest_to_their_location(): void
    {
        $nearestBranch = Branch::create([
            'name' => 'Near Branch',
            'latitude' => 26.5013,
            'longitude' => 89.3485,
        ]);
        $farBranch = Branch::create([
            'name' => 'Far Branch',
            'latitude' => 22.5726,
            'longitude' => 88.3639,
        ]);
        $staff = User::factory()->create(['role' => 'staff', 'branch_id' => $nearestBranch->id]);

        $response = $this->actingAs($staff)->postJson(route('staff.profile.location'), [
            'branch_id' => $farBranch->id,
            'latitude' => 26.5013,
            'longitude' => 89.3485,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('nearest_branch', $nearestBranch->name)
            ->assertJsonPath('selected_branch', $farBranch->name);
        $this->assertSame($nearestBranch->id, $staff->fresh()->branch_id);
        $this->assertNull($staff->fresh()->latitude);
    }

    public function test_branches_without_coordinates_are_ignored_for_verification(): void
    {
        $validBranch = Branch::create([
            'name' => 'Coordinate Branch',
            'latitude' => 26.5013,
            'longitude' => 89.3485,
        ]);
        $missingCoordinates = Branch::create(['name' => 'Unlocated Branch']);
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->postJson(route('staff.profile.location'), [
            'branch_id' => $validBranch->id,
            'latitude' => 26.5013,
            'longitude' => 89.3485,
        ]);

        $response->assertOk();
        $this->assertNotSame($missingCoordinates->id, $staff->fresh()->branch_id);
    }

    public function test_non_staff_users_cannot_access_staff_profile_or_location(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get(route('staff.profile'))->assertForbidden();
        $this->actingAs($customer)->postJson(route('staff.profile.location'), [
            'latitude' => 26,
            'longitude' => 89,
        ])->assertForbidden();
    }
}