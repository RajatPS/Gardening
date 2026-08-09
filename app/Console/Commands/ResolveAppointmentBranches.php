<?php

namespace App\Console\Commands;

use App\Models\ServiceBooking;
use App\Services\BranchResolverService;
use Illuminate\Console\Command;

class ResolveAppointmentBranches extends Command
{
    protected $signature = 'appointments:resolve-branches';
    protected $description = 'Resolve and backfill branch_id for existing appointments using location coordinates and nearest branch.';

    public function handle(): int
    {
        $resolver = new BranchResolverService();
        $total = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        $this->info('Starting appointment branch resolution...');

        ServiceBooking::query()
            ->orderBy('id')
            ->chunkById(100, function ($appointments) use ($resolver, &$total, &$updated, &$skipped, &$failed) {
                foreach ($appointments as $appointment) {
                    $total++;
                    $currentBranchId = $appointment->branch_id;

                    $location = null;
                    if ($appointment->latitude !== null && $appointment->longitude !== null) {
                        $location = [
                            'lat' => (float) $appointment->latitude,
                            'lon' => (float) $appointment->longitude,
                        ];
                    } elseif (! empty($appointment->address_line) || ! empty($appointment->city) || ! empty($appointment->pin_code)) {
                        $location = $resolver->resolveCustomerLocation(
                            $appointment->address_line,
                            $appointment->city,
                            $appointment->pin_code
                        );

                        if ($location !== null) {
                            $appointment->latitude = $location['lat'];
                            $appointment->longitude = $location['lon'];
                        }
                    }

                    if ($location === null) {
                        $skipped++;
                        $this->warn("Appointment #{$appointment->id} skipped: missing location data.");
                        continue;
                    }

                    $nearestBranch = $resolver->resolveNearestBranchToCoordinates($location);
                    if ($nearestBranch === null) {
                        $failed++;
                        $this->warn("Appointment #{$appointment->id} skipped: could not resolve nearest branch.");
                        continue;
                    }

                    if ($currentBranchId === $nearestBranch->id
                        && $appointment->latitude !== null
                        && $appointment->longitude !== null) {
                        continue;
                    }

                    $appointment->branch_id = $nearestBranch->id;
                    $appointment->save();
                    $updated++;
                    $this->info("Appointment #{$appointment->id} updated to branch {$nearestBranch->name} ({$nearestBranch->id}).");
                }
            });

        $this->info("Finished. Processed: {$total}, Updated: {$updated}, Skipped: {$skipped}, Failed: {$failed}.");

        return 0;
    }
}
