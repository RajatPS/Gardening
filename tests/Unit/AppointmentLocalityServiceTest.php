<?php

namespace Tests\Unit;

use App\Services\AppointmentLocalityService;
use Tests\TestCase;

class AppointmentLocalityServiceTest extends TestCase
{
    public function test_matches_same_city_and_nearby_aliases(): void
    {
        $service = new AppointmentLocalityService();

        $this->assertTrue($service->isRelevant('Mumbai', 'Mumbai', 'Bandra, Mumbai'));
        $this->assertTrue($service->isRelevant('Mumbai', 'Navi Mumbai', 'Sector 15'));
        $this->assertTrue($service->isRelevant('Kolkata', 'Kolkata', 'Salt Lake'));
        $this->assertFalse($service->isRelevant('Mumbai', 'Kolkata', 'Park Street'));
    }

    public function test_extracts_city_from_address_when_booking_city_is_empty(): void
    {
        $service = new AppointmentLocalityService();

        $this->assertSame('Mumbai', $service->resolveBookingCity(null, 'Flat 12, Bandra West, Mumbai'));
        $this->assertSame('Kolkata', $service->resolveBookingCity(null, '7/1, Salt Lake, Kolkata'));
    }
}
