<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class BranchResolverService
{
    private LocationDistanceService $distanceService;
    private GeoapifyGeocodingService $geoapifyService;

    public function __construct(?LocationDistanceService $distanceService = null, ?GeoapifyGeocodingService $geoapifyService = null)
    {
        $this->distanceService = $distanceService ?? new LocationDistanceService();
        $this->geoapifyService = $geoapifyService ?? new GeoapifyGeocodingService();
    }

    public function resolveCustomerLocation(?string $address, ?string $city, ?string $pinCode): ?array
    {
        $parts = [];

        if (! empty($address)) {
            $parts[] = trim($address);
        }

        if (! empty($city)) {
            $parts[] = trim($city);
        }

        if (! empty($pinCode)) {
            $parts[] = trim($pinCode);
        }

        $parts[] = 'India';
        $locationQuery = implode(', ', array_unique($parts));

        $geoapifyLocation = $this->normalizeCoordinates($this->geoapifyService->geocode($parts) ?? []);
        if ($geoapifyLocation !== null) {
            return $geoapifyLocation;
        }

        if (trim($locationQuery) !== '') {
            $location = $this->normalizeCoordinates($this->distanceService->geocodeLocation($locationQuery) ?? []);
            if ($location !== null) {
                return $location;
            }
        }

        return $this->normalizeCoordinates($this->resolveFallbackCustomerLocation($address, $city, $pinCode) ?? []);
    }

    private function resolveFallbackCustomerLocation(?string $address, ?string $city, ?string $pinCode): ?array
    {
        $fallbackQueries = [];

        if (! empty($pinCode)) {
            $fallbackQueries[] = trim($pinCode) . ', India';
        }

        if (! empty($city)) {
            $fallbackQueries[] = trim($city) . ', India';
        }

        if (! empty($address)) {
            $fallbackQueries[] = trim($address) . ', India';

            $segments = array_filter(array_map('trim', explode(',', $address)));
            if (count($segments) > 1) {
                foreach ($segments as $segment) {
                    $fallbackQueries[] = $segment . ', India';
                }

                $fallbackQueries[] = implode(', ', array_reverse($segments)) . ', India';
            }
        }

        foreach (array_unique($fallbackQueries) as $query) {
            $location = $this->distanceService->geocodeLocation($query);
            if ($location !== null) {
                return $location;
            }
        }

        return null;
    }

    public function resolveNearestBranchToCoordinates(array $customerLocation): ?Branch
    {
        $normalizedCustomerLocation = $this->normalizeCoordinates($customerLocation);
        if ($normalizedCustomerLocation === null) {
            return null;
        }

        $branches = Branch::all();
        if ($branches->isEmpty()) {
            return null;
        }

        $nearestBranch = null;
        $shortestDistance = null;

        foreach ($branches as $branch) {
            $branchLocation = $this->normalizeCoordinates($this->getBranchLocation($branch) ?? []);

            if ($branchLocation === null) {
                continue;
            }

            $distance = $this->distanceKm($normalizedCustomerLocation, $branchLocation);

            if ($shortestDistance === null || $distance < $shortestDistance) {
                $nearestBranch = $branch;
                $shortestDistance = $distance;
            }
        }

        return $nearestBranch;
    }

    public function resolveNearestBranchWithDistance(array $location): ?array
    {
        $normalizedLocation = $this->normalizeCoordinates($location);
        if ($normalizedLocation === null) {
            return null;
        }

        $nearestBranch = null;
        $shortestDistance = null;

        foreach (Branch::query()->whereNotNull('latitude')->whereNotNull('longitude')->get() as $branch) {
            $distance = $this->distanceKm($normalizedLocation, [
                'lat' => (float) $branch->latitude,
                'lon' => (float) $branch->longitude,
            ]);

            if ($shortestDistance === null || $distance < $shortestDistance) {
                $nearestBranch = $branch;
                $shortestDistance = $distance;
            }
        }

        if ($nearestBranch === null) {
            return null;
        }

        return [
            'branch' => $nearestBranch,
            'distance_km' => $shortestDistance,
        ];
    }

    private function getBranchLocation(Branch $branch): ?array
    {
        if ($branch->latitude !== null && $branch->longitude !== null) {
            return [
                'lat' => (float) $branch->latitude,
                'lon' => (float) $branch->longitude,
            ];
        }

        try {
            return $this->distanceService->geocodeLocation($branch->name . ', India');
        } catch (\Throwable $exception) {
            Log::warning('Unable to geocode branch location', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function distanceKm(array $from, array $to): float
    {
        $normalizedFrom = $this->normalizeCoordinates($from);
        $normalizedTo = $this->normalizeCoordinates($to);

        if ($normalizedFrom === null || $normalizedTo === null) {
            return INF;
        }

        $earthRadiusKm = 6371;
        $latFrom = deg2rad((float) $normalizedFrom['lat']);
        $lonFrom = deg2rad((float) $normalizedFrom['lon']);
        $latTo = deg2rad((float) $normalizedTo['lat']);
        $lonTo = deg2rad((float) $normalizedTo['lon']);

        $deltaLat = $latTo - $latFrom;
        $deltaLon = $lonTo - $lonFrom;

        $a = sin($deltaLat / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    private function normalizeCoordinates(?array $coordinates): ?array
    {
        if (! is_array($coordinates)) {
            return null;
        }

        $lat = $coordinates['lat'] ?? $coordinates['latitude'] ?? null;
        $lon = $coordinates['lon'] ?? $coordinates['longitude'] ?? null;

        if ($lat === null || $lon === null) {
            return null;
        }

        return [
            'lat' => (float) $lat,
            'lon' => (float) $lon,
        ];
    }
}
