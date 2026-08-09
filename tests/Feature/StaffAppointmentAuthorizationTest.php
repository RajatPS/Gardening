<?php

namespace Tests\Feature;

use App\Models\ServiceBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class StaffAppointmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }
    public function test_dashboard_includes_nearby_appointment_when_geocoding_succeeds(): void
    {
        Http::fake(function ($request) {
            return match ($request['q']) {
                'Mumbai' => Http::response([['lat' => '19.0760', 'lon' => '72.8777']]),
                'Navi Mumbai' => Http::response([['lat' => '19.0330', 'lon' => '73.0297']]),
                default => Http::response([], 404),
            };
        });

        $staff = $this->staff('Mumbai');
        $booking = $this->booking(['city' => 'Navi Mumbai']);

        $response = $this->actingAs($staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $this->assertTrue(
            $response->viewData('relevantAppointments')->contains('id', $booking->id),
            'A booking within the 150 km service radius should appear on the staff dashboard.'
        );
    }

    public function test_dashboard_uses_normalized_city_name_when_geocoding_fails(): void
    {
        Http::fake(['*' => Http::response([], 503)]);

        $staff = $this->staff('Mumbai');
        $booking = $this->booking(['city' => 'Mum-bai']);

        $response = $this->actingAs($staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $this->assertTrue(
            $response->viewData('relevantAppointments')->contains('id', $booking->id),
            'Equivalent city names should fall back to normalized comparison when geocoding is unavailable.'
        );
    }

    public function test_dashboard_excludes_appointments_outside_the_staff_service_area(): void
    {
        Http::fake(function ($request) {
            return match ($request['q']) {
                'Mumbai' => Http::response([['lat' => '19.0760', 'lon' => '72.8777']]),
                'Kolkata' => Http::response([['lat' => '22.5726', 'lon' => '88.3639']]),
                default => Http::response([], 404),
            };
        });

        $staff = $this->staff('Mumbai');
        $booking = $this->booking(['city' => null, 'address_line' => 'Park Street, Kolkata']);

        $response = $this->actingAs($staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $this->assertFalse($response->viewData('relevantAppointments')->contains('id', $booking->id));
    }

    public function test_staff_cannot_accept_appointment_already_assigned_to_another_staff_member(): void
    {
        $staff = $this->staff('Mumbai');
        $otherStaff = $this->staff('Mumbai');
        $booking = $this->booking(['assigned_staff_id' => $otherStaff->id, 'status' => 'assigned']);

        $response = $this->postAcceptAppointment($staff, $booking);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertSame($otherStaff->id, $booking->fresh()->assigned_staff_id);
    }

    public function test_staff_cannot_accept_an_appointment_outside_its_service_area(): void
    {
        Http::fake(['*' => Http::response([], 503)]);

        $staff = $this->staff('Mumbai');
        $booking = $this->booking(['city' => 'Kolkata', 'address_line' => 'Park Street, Kolkata']);

        $response = $this->postAcceptAppointment($staff, $booking);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertNull($booking->fresh()->assigned_staff_id);
        $this->assertSame('pending', $booking->fresh()->status);
    }

    /**
     * This models competing rapid requests. A true multi-process race test should be
     * added once the test environment has a database driver and a transactional DB.
     */
    public function test_only_one_staff_member_can_claim_an_appointment_when_requests_compete(): void
    {
        $firstStaff = $this->staff('Mumbai');
        $secondStaff = $this->staff('Mumbai');
        $booking = $this->booking();

        $this->postAcceptAppointment($firstStaff, $booking)->assertRedirect();
        $this->postAcceptAppointment($secondStaff, $booking)->assertSessionHasErrors();

        $booking->refresh();
        $this->assertSame($firstStaff->id, $booking->assigned_staff_id);
        $this->assertSame('assigned', $booking->status);
    }

    public function test_dashboard_counters_match_the_appointments_shown_to_the_staff_member(): void
    {
        $staff = $this->staff('Mumbai');
        $pending = $this->booking(['assigned_staff_id' => $staff->id, 'status' => 'pending']);
        $completed = $this->booking(['assigned_staff_id' => $staff->id, 'status' => 'completed']);
        $today = $this->booking(['assigned_staff_id' => $staff->id, 'booking_date' => now()]);

        $response = $this->actingAs($staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $assigned = $response->viewData('assignedAppointments');
        $this->assertSame($assigned->where('status', 'pending')->count(), $response->viewData('pendingAppointments'));
        $this->assertSame($assigned->where('status', 'completed')->count(), $response->viewData('completedAppointments'));
        $this->assertSame($assigned->filter(fn (ServiceBooking $item) => $item->booking_date?->isToday())->count(), $response->viewData('todayAppointments'));
        $this->assertTrue($assigned->contains('id', $pending->id));
        $this->assertTrue($assigned->contains('id', $completed->id));
        $this->assertTrue($assigned->contains('id', $today->id));
    }

    private function staff(string $city): User
    {
        return User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
            'user_type' => 'staff',
            'city' => $city,
            'must_change_password' => false,
        ]);
    }

    private function postAcceptAppointment(User $staff, ServiceBooking $booking)
    {
        $csrfToken = Str::random(40);

        return $this->actingAs($staff)
            ->withSession(['_token' => $csrfToken])
            ->post(route('staff.appointments.accept', $booking), ['_token' => $csrfToken]);
    }

    private function booking(array $attributes = []): ServiceBooking
    {
        return ServiceBooking::create(array_merge([
            'service_type' => 'plant-maintenance',
            'status' => 'pending',
            'city' => 'Mumbai',
            'address_line' => 'Bandra West, Mumbai',
            'booking_date' => now()->addDay(),
        ], $attributes));
    }
}
