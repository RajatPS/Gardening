<?php

namespace Tests\Unit;

use App\Services\LocationDistanceService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocationDistanceServiceTest extends TestCase
{
    public function test_it_calculates_distance_between_two_locations(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::sequence()
                ->push([
                    ['lat' => '22.5726', 'lon' => '88.3639'],
                ], 200)
                ->push([
                    ['lat' => '25.0983', 'lon' => '88.1386'],
                ], 200),
        ]);

        $service = new LocationDistanceService();
        $distance = $service->calculateDistanceKm('Kolkata', 'Malda');

        $this->assertNotNull($distance);
        $this->assertGreaterThan(0, $distance);
    }

    public function test_it_returns_null_when_geocoding_fails(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([], 200),
        ]);

        $service = new LocationDistanceService();

        $this->assertNull($service->calculateDistanceKm('UnknownPlace', 'AnotherUnknownPlace'));
    }
}
