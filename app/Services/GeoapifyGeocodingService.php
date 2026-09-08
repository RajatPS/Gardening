<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoapifyGeocodingService
{
    public function geocode(array $parts): ?array
    {
        $query = implode(', ', array_values(array_filter(array_map('trim', $parts))));
        $apiKey = config('services.geoapify.key');

        if ($query === '' || $apiKey === null || $apiKey === '') {
            return null;
        }

        return Cache::remember('geoapify:' . md5(strtolower($query)), now()->addDays(7), function () use ($query, $apiKey): ?array {
            try {
                $response = Http::timeout(10)->get('https://api.geoapify.com/v1/geocode/search', [
                    'text' => $query,
                    'format' => 'json',
                    'apiKey' => $apiKey,
                ]);

                if (! $response->successful()) {
                    return null;
                }

                $result = $response->json('results.0');
                $latitude = is_array($result) ? ($result['lat'] ?? null) : null;
                $longitude = is_array($result) ? ($result['lon'] ?? null) : null;

                if (! is_numeric($latitude) || ! is_numeric($longitude)) {
                    return null;
                }

                $latitude = (float) $latitude;
                $longitude = (float) $longitude;

                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    return null;
                }

                return [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            } catch (\Throwable) {
                return null;
            }
        });
    }
}
