<?php

namespace App\Services;

class AppointmentLocalityService
{
    public function isRelevant(?string $staffCity, ?string $appointmentCity, ?string $address = null): bool
    {
        $staffCity = $this->normalizeCity($staffCity);
        $appointmentCity = $this->normalizeCity($appointmentCity ?? $this->extractCityFromAddress($address));

        if ($staffCity === '' || $appointmentCity === '') {
            return false;
        }

        if (strtolower($staffCity) === strtolower($appointmentCity)) {
            return true;
        }

        $distanceService = new LocationDistanceService();
        $distance = $distanceService->calculateDistanceKm($staffCity, $appointmentCity);

        return $distance !== null && $distance <= 150;
    }

    public function resolveBookingCity(?string $city, ?string $address = null): ?string
    {
        $city = $this->normalizeCity($city);

        if ($city !== '') {
            return $city;
        }

        $addressCity = $this->extractCityFromAddress($address);

        return $addressCity !== '' ? $addressCity : null;
    }

    public function normalizeCity(?string $city): string
    {
        if ($city === null) {
            return '';
        }

        $trimmed = trim($city);

        if ($trimmed === '') {
            return '';
        }

        $trimmed = preg_replace('/\s+/', ' ', $trimmed);

        return $trimmed !== null ? $trimmed : '';
    }

    private function extractCityFromAddress(?string $address): string
    {
        if ($address === null || trim($address) === '') {
            return '';
        }

        $parts = preg_split('/,|\s+/', strtolower($address));
        if ($parts === false) {
            return '';
        }

        $cityCandidates = ['mumbai', 'navi mumbai', 'thane', 'pune', 'kolkata', 'delhi', 'gurgaon', 'noida', 'bangalore', 'hyderabad', 'chennai', 'jaipur', 'ahmedabad', 'surat', 'lucknow', 'malda', 'siliguri'];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if (in_array($part, $cityCandidates, true)) {
                return ucfirst($part);
            }
        }

        return '';
    }

    private function nearbyCities(string $city): array
    {
        $normalized = strtolower($city);

        $map = [
            'mumbai' => ['mumbai', 'navi mumbai', 'thane', 'bandra', 'andheri', 'borivali'],
            'kolkata' => ['kolkata', 'salt lake', 'howrah', 'new town'],
            'delhi' => ['delhi', 'gurgaon', 'noida', 'ghaziabad'],
            'bangalore' => ['bangalore', 'whitefield', 'hebbal', 'marathahalli'],
            'pune' => ['pune', 'hinjewadi', 'baner', 'kharadi'],
        ];

        return $map[$normalized] ?? [];
    }
}
