<?php

namespace App\Http\Controllers\Staff;

use App\Models\ServiceBooking;
use App\Models\User;
use App\Services\AppointmentLocalityService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $staff = Auth::user();

        $localityService = new AppointmentLocalityService();
        $staffCity = $staff->city;
        $staffBranch = $staff->branch;
        $staffBranchLocation = null;

        if ($staffBranch && $staffBranch->latitude !== null && $staffBranch->longitude !== null) {
            $staffBranchLocation = [
                'lat' => (float) $staffBranch->latitude,
                'lon' => (float) $staffBranch->longitude,
            ];
        }

        // Retrieve candidate bookings: unassigned bookings or those already assigned to this staff.
        // We'll then filter them by branch, location, and service area relevance.
        $nearbyAppointments = ServiceBooking::with('branch')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($query) use ($staff) {
                $query->whereNull('assigned_staff_id')
                    ->orWhere('assigned_staff_id', $staff->id)
                    ->orWhereHas('assignedStaff', function ($query) use ($staff) {
                        $query->where('users.id', $staff->id);
                    });
            })
            ->latest('booking_date')
            ->take(100)
            ->get();

        $relevantAppointments = collect();
        foreach ($nearbyAppointments as $appointment) {
            $appointmentCity = $appointment->city;
            $appointmentBranch = $appointment->branch;
            $appointmentLocation = null;

            if ($appointment->latitude !== null && $appointment->longitude !== null) {
                $appointmentLocation = [
                    'lat' => (float) $appointment->latitude,
                    'lon' => (float) $appointment->longitude,
                ];
            }

            if ($staffBranch && $appointmentBranch && $staffBranch->id === $appointmentBranch->id) {
                $relevantAppointments->push($appointment);
                continue;
            }

            if ($staffBranchLocation !== null && $appointmentLocation !== null) {
                if ($localityService->areLocationsWithinRadius($staffBranchLocation, $appointmentLocation, 120)) {
                    $relevantAppointments->push($appointment);
                    continue;
                }
            }

            if ($localityService->isRelevant($staffCity, $appointmentCity, $appointment->address_line)) {
                $relevantAppointments->push($appointment);
            }
        }

        $assignedQuery = $this->assignedQueryForStaff($staff->id);

        $assignedAppointments = (clone $assignedQuery)->latest('booking_date')->take(10)->get();

        $pendingAppointments = (clone $assignedQuery)->where('status', 'pending')->count();

        $completedAppointments = (clone $assignedQuery)->where('status', 'completed')->count();

        $todayAppointments = (clone $assignedQuery)->whereDate('booking_date', Carbon::today())->count();

        return view('staff.dashboard', compact(
            'staff',
            'assignedAppointments',
            'pendingAppointments',
            'completedAppointments',
            'todayAppointments',
            'relevantAppointments'
        ));
    }

    public function acceptAppointment(ServiceBooking $booking)
    {
        $staff = Auth::user();

        if (! $staff || $staff->role !== 'staff') {
            return redirect()->route('staff.login');
        }

        // Prevent accepting bookings that are already assigned to another staff
        if ($booking->assigned_staff_id && $booking->assigned_staff_id !== $staff->id) {
            return redirect()->back()->withErrors('This appointment is already assigned to another staff member.');
        }

        if ($booking->assignedStaff()->where('users.id', '!=', $staff->id)->exists()) {
            return redirect()->back()->withErrors('This appointment is already assigned to another staff member.');
        }

        // Only allow acceptance for bookings that are in an actionable status
        $disallowedStatuses = ['completed', 'cancelled'];
        if (in_array($booking->status, $disallowedStatuses, true)) {
            return redirect()->back()->withErrors('This appointment cannot be accepted.');
        }

        // Verify geographic/service area relevance
        $localityService = new AppointmentLocalityService();
        $bookingCity = $localityService->resolveBookingCity($booking->city, $booking->address_line);

        $allowedByBranch = false;
        $staffBranch = $staff->branch;
        if ($staffBranch && $staffBranch->latitude !== null && $staffBranch->longitude !== null && $booking->latitude !== null && $booking->longitude !== null) {
            $allowedByBranch = $localityService->areLocationsWithinRadius([
                'lat' => (float) $staffBranch->latitude,
                'lon' => (float) $staffBranch->longitude,
            ], [
                'lat' => (float) $booking->latitude,
                'lon' => (float) $booking->longitude,
            ], 120);
        }

        if ($booking->assigned_staff_id === null && ! $allowedByBranch && ! $localityService->isRelevant($staff->city, $bookingCity, $booking->address_line)) {
            return redirect()->back()->withErrors('You are not authorised to accept this appointment (outside your service area).');
        }

        // Final assignment: use a DB transaction to avoid races where two staff accept simultaneously
        \DB::transaction(function () use ($booking, $staff) {
            $fresh = ServiceBooking::lockForUpdate()->find($booking->id);

            if ($fresh->assigned_staff_id && $fresh->assigned_staff_id !== $staff->id) {
                throw new \RuntimeException('Appointment already assigned');
            }

            if ($fresh->assignedStaff()->where('users.id', '!=', $staff->id)->exists()) {
                throw new \RuntimeException('Appointment already assigned');
            }

            $fresh->assignedStaff()->syncWithoutDetaching([$staff->id]);
            $fresh->update([
                'assigned_staff_id' => $staff->id,
                'status' => 'assigned',
            ]);
        });

        return redirect()->back()->with('success', 'Appointment accepted successfully.');
    }

    private function assignedQueryForStaff(int $staffId)
    {
        return ServiceBooking::query()
            ->where(function ($query) use ($staffId) {
                $query->where('assigned_staff_id', $staffId)
                    ->orWhereHas('assignedStaff', function ($query) use ($staffId) {
                        $query->where('users.id', $staffId);
                    });
            });
    }
}
