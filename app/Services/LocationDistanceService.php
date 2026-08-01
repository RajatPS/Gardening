<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LocationDistanceService
{
    public function calculateDistanceKm(?string $from, ?string $to): ?float
    {
        if (empty($from) || empty($to)) {
            return null;
        }

        $fromCoords = $this->geocode($from);
        $toCoords = $this->geocode($to);

        if ($fromCoords === null || $toCoords === null) {
            return null;
        }

        $earthRadiusKm = 6371;
        $latFrom = deg2rad($fromCoords['lat']);
        $lonFrom = deg2rad($fromCoords['lon']);
        $latTo = deg2rad($toCoords['lat']);
        $lonTo = deg2rad($toCoords['lon']);

        $deltaLat = $latTo - $latFrom;
        $deltaLon = $lonTo - $lonFrom;

        $a = sin($deltaLat / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    public function isWithinRadius(?string $from, ?string $to, int $radiusKm = 150): bool
    {
        $distance = $this->calculateDistanceKm($from, $to);

        return $distance !== null && $distance <= $radiusKm;
    }

    private function geocode(string $location): ?array
    {
        $cacheKey = 'geocode:' . md5(strtolower(trim($location)));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($location) {
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'limit' => 1,
                'q' => $location,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (! is_array($data) || empty($data[0])) {
                return null;
            }

            return [
                'lat' => (float) $data[0]['lat'],
                'lon' => (float) $data[0]['lon'],
            ];
        });
    }
}
