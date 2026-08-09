<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class BranchResolverService
{
    private LocationDistanceService $distanceService;

    public function __construct(?LocationDistanceService $distanceService = null)
    {
        $this->distanceService = $distanceService ?? new LocationDistanceService();
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

        if (trim($locationQuery) !== '') {
            $location = $this->distanceService->geocodeLocation($locationQuery);
            if ($location !== null) {
                return $location;
            }
        }

        return $this->resolveFallbackCustomerLocation($address, $city, $pinCode);
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
        $branches = Branch::all();
        if ($branches->isEmpty()) {
            return null;
        }

        $nearestBranch = null;
        $shortestDistance = null;

        foreach ($branches as $branch) {
            $branchLocation = $this->getBranchLocation($branch);

            if ($branchLocation === null) {
                continue;
            }

            $distance = $this->distanceKm($customerLocation, $branchLocation);

            if ($shortestDistance === null || $distance < $shortestDistance) {
                $nearestBranch = $branch;
                $shortestDistance = $distance;
            }
        }

        return $nearestBranch;
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
        $earthRadiusKm = 6371;
        $latFrom = deg2rad((float) $from['lat']);
        $lonFrom = deg2rad((float) $from['lon']);
        $latTo = deg2rad((float) $to['lat']);
        $lonTo = deg2rad((float) $to['lon']);

        $deltaLat = $latTo - $latFrom;
        $deltaLon = $lonTo - $lonFrom;

        $a = sin($deltaLat / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
